<?php

namespace Tests\Feature;

use App\Actions\AssignMachineAction;
use App\Actions\CreateJobAction;
use App\Actions\TransitionJobStatusAction;
use App\Enums\JobStatus;
use App\Enums\MachineStatus;
use App\Exceptions\InvalidStatusTransitionException;
use App\Models\Customer;
use App\Models\Job;
use App\Models\Machine;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class LaundryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $cashier;
    protected User $operator;
    protected Customer $customer;
    protected Service $shirtService;
    protected Service $suitService;
    protected Machine $washer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['email' => 'admin@taison.co.ke'],
            [
                'name' => 'Lewis Sila',
                'password' => bcrypt('Lewis123'),
                'role' => 'admin',
                'phone' => '0700000001',
            ]
        );

        $this->cashier = User::firstOrCreate(
            ['email' => 'cashier@taison.co.ke'],
            [
                'name' => 'Jane Cashier',
                'password' => bcrypt('Cashier123'),
                'role' => 'cashier',
                'phone' => '0700000002',
            ]
        );

        $this->operator = User::firstOrCreate(
            ['email' => 'operator@taison.co.ke'],
            [
                'name' => 'John Operator',
                'password' => bcrypt('Operator123'),
                'role' => 'operator',
                'phone' => '0700000003',
            ]
        );

        $this->customer = Customer::firstOrCreate(
            ['phone' => '0712345678'],
            [
                'name' => 'Austin Bailey',
                'email' => 'austin@example.com',
                'address' => 'Nairobi West',
            ]
        );

        $this->shirtService = Service::firstOrCreate(
            ['name' => 'Shirt Wash & Fold'],
            [
                'price_in_cents' => 35000, // KSh 350 in cents
                'duration_minutes' => 45,
                'description' => 'Shirt Wash & Fold',
            ]
        );

        $this->suitService = Service::firstOrCreate(
            ['name' => 'Executive Suit Dry Clean'],
            [
                'price_in_cents' => 80000, // KSh 800 in cents
                'duration_minutes' => 60,
                'description' => 'Executive Suit Dry Clean',
            ]
        );

        $this->washer = Machine::firstOrCreate(
            ['name' => 'Washer 1 (15kg Heavy Duty)'],
            [
                'is_available' => true,
                'is_active' => true,
            ]
        );
    }

    /**
     * Test 1: Order intake creates job with exact derived financials (zero drift)
     */
    public function test_order_intake_creates_job_with_exact_derived_financials(): void
    {
        $createJobAction = app(CreateJobAction::class);

        $items = [
            ['service_id' => $this->shirtService->id, 'quantity' => 2],
            ['service_id' => $this->suitService->id, 'quantity' => 1],
        ];

        $job = $createJobAction->execute(
            customer: $this->customer,
            items: $items,
            notes: 'Handle suit with extra care',
            paymentMethod: 'mpesa',
            paidAmount: 50000
        );

        $this->assertNotNull($job->id);
        $this->assertEquals($this->customer->id, $job->customer_id);
        $this->assertEquals(JobStatus::RECEIVED, $job->status);

        // VAT-Inclusive Financial Assertions (Retail: 2 * 350 + 1 * 800 = 1500.00)
        // Subtotal (Taxable Base Net): 1500 / 1.16 = 1293.10
        // Tax (16% VAT): 1500 - 1293.10 = 206.90
        // Total Price: 1500.00
        // Paid: 500.00 (from 50000 cents deposit)
        // Balance: 1000.00
        $this->assertEquals(1500.00, $job->total_price);
        $this->assertEquals(1293.10, $job->subtotal);
        $this->assertEquals(206.90, $job->tax);
        $this->assertEquals(500.00, $job->paid_amount);
        $this->assertEquals(1000.00, $job->balance_due);
        $this->assertEquals('partial', $job->payment_status);
        $this->assertCount(2, $job->items);
    }

    /**
     * Test 2: Assign Machine locks machine and prevents double booking
     */
    public function test_assign_machine_locks_machine_and_prevents_double_booking(): void
    {
        $createJobAction = app(CreateJobAction::class);
        $job1 = $createJobAction->execute($this->customer, [
            ['service_id' => $this->shirtService->id, 'quantity' => 1],
        ]);
        $job2 = $createJobAction->execute($this->customer, [
            ['service_id' => $this->shirtService->id, 'quantity' => 1],
        ]);

        $assignMachineAction = app(AssignMachineAction::class);

        // Assign machine to job1 using machine ID
        $assignMachineAction->execute($job1, $this->washer->id, $this->operator->id);

        $this->assertEquals(MachineStatus::IN_USE, $this->washer->fresh()->status);
        $this->assertEquals($this->washer->id, $job1->fresh()->machine_id);

        // Attempting to assign the same machine to job2 must throw RuntimeException
        $this->expectException(RuntimeException::class);
        $assignMachineAction->execute($job2, $this->washer->id, $this->operator->id);
    }

    /**
     * Test 3: State Machine validates transitions and frees machine when ready
     */
    public function test_state_machine_validates_transitions_and_frees_machine_when_ready(): void
    {
        $createJobAction = app(CreateJobAction::class);
        $job = $createJobAction->execute($this->customer, [
            ['service_id' => $this->shirtService->id, 'quantity' => 1],
        ]);

        $assignMachineAction = app(AssignMachineAction::class);
        $assignMachineAction->execute($job, $this->washer->id, $this->operator->id);

        $transitionAction = app(TransitionJobStatusAction::class);

        // Valid transition: RECEIVED -> IN_PROGRESS
        $transitionAction->execute($job, JobStatus::IN_PROGRESS);
        $this->assertEquals(JobStatus::IN_PROGRESS, $job->fresh()->status);

        // Invalid transition: Attempting illegal jump directly to PICKED_UP
        try {
            $transitionAction->execute($job->fresh(), JobStatus::PICKED_UP);
            $this->fail('Expected InvalidStatusTransitionException was not thrown');
        } catch (InvalidStatusTransitionException $e) {
            $this->assertTrue(true);
        }

        // Valid transition: IN_PROGRESS -> READY (should vacate machine)
        $transitionAction->execute($job->fresh(), JobStatus::READY);
        $this->assertEquals(JobStatus::READY, $job->fresh()->status);
        $this->assertEquals(MachineStatus::AVAILABLE, $this->washer->fresh()->status);
    }

    /**
     * Test 4: Operator cannot access staff management or financial dashboards
     */
    public function test_operator_cannot_access_staff_management_or_financial_dashboards(): void
    {
        // Operator accessing /staff -> 403 Forbidden or Redirect
        $response = $this->actingAs($this->operator)->get('/staff');
        $this->assertTrue($response->status() === 302 || $response->status() === 403);

        // Admin accessing /staff -> Success
        $adminResponse = $this->actingAs($this->admin)->get('/staff');
        $adminResponse->assertStatus(200);
        $adminResponse->assertSee('Staff & Operations Activity Tracker', false);
    }

    /**
     * Test 5: Public Customer Tracking Kiosk (No Auth Required)
     */
    public function test_public_customer_can_track_order_without_login(): void
    {
        $createJobAction = app(CreateJobAction::class);
        $job = $createJobAction->execute($this->customer, [
            ['service_id' => $this->shirtService->id, 'quantity' => 2],
        ]);

        // Guest visitor querying /track?ticket=...
        $response = $this->get('/track?ticket=' . $job->job_number);

        $response->assertStatus(200);
        $response->assertSee($job->job_number);
        $response->assertSee('Received');
        $response->assertSee('Austin Bailey');
    }

    /**
     * Test 6: Admin can add, update, and remove catalog services
     */
    public function test_admin_can_manage_services_crud(): void
    {
        // 1. Admin views services list
        $response = $this->actingAs($this->admin)->get('/services');
        $response->assertStatus(200);
        $response->assertSee('Services & Pricing Catalog', false);

        // 2. Admin adds a new service
        $storeResponse = $this->actingAs($this->admin)->post('/services', [
            'name'             => 'Heavy Winter Duvet Care',
            'price'            => 850,
            'duration_minutes' => 75,
            'description'      => 'Anti-dustmite antibacterial wash and fluff dry',
        ]);
        $storeResponse->assertRedirect('/services');

        $duvetService = Service::where('name', 'Heavy Winter Duvet Care')->first();
        $this->assertNotNull($duvetService);
        $this->assertEquals(85000, $duvetService->price_in_cents);
        $this->assertEquals(850.00, $duvetService->price);

        // 3. Admin updates service price
        $updateResponse = $this->actingAs($this->admin)->put('/services/' . $duvetService->id, [
            'name'             => 'Heavy Winter Duvet Care',
            'price'            => 950,
            'duration_minutes' => 80,
            'description'      => 'Updated deep sanitization',
        ]);
        $updateResponse->assertRedirect('/services');
        $this->assertEquals(95000, $duvetService->fresh()->price_in_cents);

        // 4. Admin removes unlinked service
        $deleteResponse = $this->actingAs($this->admin)->delete('/services/' . $duvetService->id);
        $deleteResponse->assertRedirect('/services');
        $this->assertNull(Service::find($duvetService->id));
    }
}
