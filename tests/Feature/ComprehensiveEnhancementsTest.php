<?php

namespace Tests\Feature;

use App\Enums\JobStatus;
use App\Enums\MachineStatus;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Job;
use App\Models\Machine;
use App\Models\Service;
use App\Models\ShopSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComprehensiveEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $cashier;
    protected User $operator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email'    => 'admin@taison.co.ke',
            'role'     => 'admin',
            'is_admin' => true,
        ]);

        $this->cashier = User::factory()->create([
            'email'    => 'cashier@taison.co.ke',
            'role'     => 'cashier',
            'is_admin' => false,
        ]);

        $this->operator = User::factory()->create([
            'email'    => 'operator@taison.co.ke',
            'role'     => 'operator',
            'is_admin' => false,
        ]);
    }

    public function test_expense_controller_accepts_water_and_electricity_and_salaries(): void
    {
        $this->actingAs($this->admin);

        // 1. Water expense
        $responseWater = $this->post(route('expenses.store'), [
            'title'          => 'Clean Borehole Water Tanker',
            'category'       => 'water',
            'amount'         => 8500,
            'expense_date'   => Carbon::today()->toDateString(),
            'payment_method' => 'mpesa',
            'reference_code' => 'REF-WATER-001',
        ]);
        $responseWater->assertRedirect(route('expenses.index'));
        $this->assertDatabaseHas('expenses', [
            'title'           => 'Clean Borehole Water Tanker',
            'category'        => 'water',
            'amount_in_cents' => 850000,
        ]);

        // 2. Electricity expense
        $responseElec = $this->post(route('expenses.store'), [
            'title'          => 'KPLC Prepaid Commercial Tokens',
            'category'       => 'electricity',
            'amount'         => 14200,
            'expense_date'   => Carbon::today()->toDateString(),
            'payment_method' => 'mpesa',
            'reference_code' => 'REF-ELEC-002',
        ]);
        $responseElec->assertRedirect(route('expenses.index'));
        $this->assertDatabaseHas('expenses', [
            'title'           => 'KPLC Prepaid Commercial Tokens',
            'category'        => 'electricity',
            'amount_in_cents' => 1420000,
        ]);

        // 3. Salaries expense
        $responseSal = $this->post(route('expenses.store'), [
            'title'          => 'Staff Wages Week 36',
            'category'       => 'salaries',
            'amount'         => 18000,
            'expense_date'   => Carbon::today()->toDateString(),
            'payment_method' => 'bank_transfer',
            'reference_code' => 'REF-SAL-003',
        ]);
        $responseSal->assertRedirect(route('expenses.index'));
        $this->assertDatabaseHas('expenses', [
            'title'           => 'Staff Wages Week 36',
            'category'        => 'salaries',
            'amount_in_cents' => 1800000,
        ]);

        // 4. Invalid category rejected
        $responseInvalid = $this->post(route('expenses.store'), [
            'title'          => 'Invalid Category Expense',
            'category'       => 'unsupported_junk_category',
            'amount'         => 500,
            'expense_date'   => Carbon::today()->toDateString(),
            'payment_method' => 'cash',
        ]);
        $responseInvalid->assertSessionHasErrors('category');
    }

    public function test_reports_show_where_money_goes_with_water_and_electricity_breakdown(): void
    {
        $this->actingAs($this->admin);

        Expense::create([
            'title'           => 'Water Delivery Tanker',
            'category'        => 'water',
            'amount_in_cents' => 600000, // KSh 6,000
            'expense_date'    => Carbon::today()->toDateString(),
            'payment_method'  => 'mpesa',
            'reference_code'  => 'WTR-99',
            'created_by'      => $this->admin->id,
        ]);

        Expense::create([
            'title'           => 'KPLC Power Supply',
            'category'        => 'electricity',
            'amount_in_cents' => 1200000, // KSh 12,000
            'expense_date'    => Carbon::today()->toDateString(),
            'payment_method'  => 'mpesa',
            'reference_code'  => 'PWR-99',
            'created_by'      => $this->admin->id,
        ]);

        $response = $this->get(route('reports.index', ['period' => 'today']));
        $response->assertOk();
        $response->assertSee('Where Does the Company Money Go?');
        $response->assertSee('Water &amp; Borehole Delivery', false);
        $response->assertSee('Electricity &amp; Power (KPLC)', false);
        $response->assertSee('6,000.00');
        $response->assertSee('12,000.00');
    }

    public function test_shop_settings_can_be_customized_by_admin_and_denied_to_cashier(): void
    {
        // Cashier denied
        $this->actingAs($this->cashier);
        $this->get(route('settings.edit'))->assertForbidden();
        $this->put(route('settings.update'), [
            'shop_name' => 'Hacked Name',
        ])->assertForbidden();

        // Admin allowed
        $this->actingAs($this->admin);
        $this->get(route('settings.edit'))->assertOk();

        $updateResponse = $this->put(route('settings.update'), [
            'shop_name'             => 'Savannah Commercial Laundry',
            'tagline'               => 'Nairobi High Speed Dry Cleaners',
            'location'              => 'Kilimani, Argwings Kodhek Rd',
            'phone'                 => '+254 711 999 888',
            'email'                 => 'hello@savannahwash.co.ke',
            'mpesa_till_or_paybill' => '778899',
            'tax_percent'           => 16.00,
            'receipt_footer'        => 'Quality care for every fabric.',
        ]);

        $updateResponse->assertRedirect(route('settings.edit'));
        $updateResponse->assertSessionHas('success');

        $this->assertDatabaseHas('shop_settings', [
            'shop_name'             => 'Savannah Commercial Laundry',
            'location'              => 'Kilimani, Argwings Kodhek Rd',
            'mpesa_till_or_paybill' => '778899',
        ]);
    }

    public function test_mark_ready_records_rack_location_and_releases_machine(): void
    {
        $this->actingAs($this->admin);

        $customer = Customer::create([
            'name'  => 'Sarah Jenkins',
            'phone' => '0788776655',
        ]);

        $washer = Machine::create([
            'name'         => 'Heavy Speed Washer 03',
            'type'         => 'washer',
            'status'       => MachineStatus::IN_USE,
            'is_available' => false,
            'is_active'    => true,
            'capacity_kg'  => 18.00,
        ]);

        $job = Job::create([
            'job_number'    => 'AUR-20260909-TEST',
            'customer_id'   => $customer->id,
            'machine_id'    => $washer->id,
            'status'        => JobStatus::IN_PROGRESS,
            'rack_location' => null,
        ]);

        $response = $this->post(route('jobs.mark-ready', $job), [
            'rack_location' => 'Rack D-12',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $job->refresh();
        $this->assertEquals(JobStatus::READY, $job->status);
        $this->assertEquals('Rack D-12', $job->rack_location);

        $washer->refresh();
        $this->assertTrue((bool) $washer->is_available);
    }

    public function test_customer_tracking_portal_shows_rack_location_and_dynamic_shop_name(): void
    {
        ShopSetting::current()->update([
            'shop_name' => 'Apex Platinum Laundry',
            'tagline'   => 'Eco-Friendly Dry Clean & Press',
            'location'  => 'Westlands, Nairobi',
            'phone'     => '+254 700 123 456',
        ]);

        $customer = Customer::create([
            'name'  => 'David Ndung\'u',
            'phone' => '0799112233',
        ]);

        $job = Job::create([
            'job_number'    => 'AUR-20260909-TRK1',
            'customer_id'   => $customer->id,
            'status'        => JobStatus::READY,
            'rack_location' => 'Rack Bay 07',
        ]);

        $response = $this->get(route('track', ['ticket' => 'AUR-20260909-TRK1']));
        $response->assertOk();
        $response->assertSee('APEX PLATINUM LAUNDRY');
        $response->assertSee('Eco-Friendly Dry Clean &amp; Press', false);
        $response->assertSee('Rack Bay 07');
        $response->assertSee('Westlands, Nairobi');
    }
}

