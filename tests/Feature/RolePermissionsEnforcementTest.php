<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Job;
use App\Models\Machine;
use App\Models\Role;
use App\Models\Service;
use App\Models\ShopSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionsEnforcementTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $managerUser;
    protected User $cashierUser;
    protected User $operatorUser;
    protected Machine $machine;
    protected Customer $customer;
    protected Job $job;

    protected function setUp(): void
    {
        parent::setUp();

        ShopSetting::current();

        Role::create(['name' => 'admin', 'display_name' => 'Admin / Owner', 'access_level' => 'Full Access', 'primary_actions' => ['user:manage', 'invoice:void', 'refund:issue', 'audit-log:view'], 'is_system' => true]);
        Role::create(['name' => 'manager', 'display_name' => 'Store Manager', 'access_level' => 'Operational Authority', 'primary_actions' => ['price:override', 'rewash:approve', 'route:assign', 'report:export'], 'is_system' => true]);
        Role::create(['name' => 'cashier', 'display_name' => 'Front Desk Cashier', 'access_level' => 'Customer Facing', 'primary_actions' => ['order:create', 'order:edit', 'payment:collect', 'tag:generate'], 'is_system' => true]);
        Role::create(['name' => 'operator', 'display_name' => 'Laundry Operator', 'access_level' => 'Back-end Processing', 'primary_actions' => ['status:update', 'weight:log', 'machine:allocate'], 'is_system' => true]);
        Role::create(['name' => 'rider', 'display_name' => 'Delivery Rider', 'access_level' => 'Logistics Only', 'primary_actions' => ['delivery:confirm', 'bag:audit'], 'is_system' => true]);

        $this->adminUser = User::factory()->create(['role' => 'admin', 'is_admin' => true]);
        $this->managerUser = User::factory()->create(['role' => 'manager', 'is_admin' => false]);
        $this->cashierUser = User::factory()->create(['role' => 'cashier', 'is_admin' => false]);
        $this->operatorUser = User::factory()->create(['role' => 'operator', 'is_admin' => false]);

        $this->machine = Machine::create([
            'name'         => 'Washer Titan 01',
            'type'         => 'washer',
            'capacity_kg'  => 18,
            'status'       => 'available',
            'is_available' => true,
        ]);

        $this->customer = Customer::create([
            'name'  => 'Faith Wambui',
            'phone' => '0711223344',
            'email' => 'faith@example.com',
        ]);

        $service = Service::create([
            'name'             => 'Duvet Washing',
            'price_in_cents'   => 50000,
            'category'         => 'bedding',
            'duration_minutes' => 60,
            'is_active'        => true,
        ]);

        $this->job = Job::create([
            'job_number'  => 'TST-99001',
            'customer_id' => $this->customer->id,
            'user_id'     => $this->cashierUser->id,
            'status'      => 'received',
        ]);

        $this->job->items()->create([
            'service_id'     => $service->id,
            'quantity'       => 1,
            'price_in_cents' => 50000,
        ]);
    }

    public function test_admin_has_full_access_to_all_modules()
    {
        $this->actingAs($this->adminUser)->get(route('settings.edit'))->assertOk();
        $this->actingAs($this->adminUser)->get(route('settings.staff'))->assertOk();
        $this->actingAs($this->adminUser)->get(route('settings.roles'))->assertOk();
        $this->actingAs($this->adminUser)->get(route('settings.permissions'))->assertOk();
        $this->actingAs($this->adminUser)->get(route('reports.index'))->assertOk();
        $this->actingAs($this->adminUser)->get(route('reports.export-pnl'))->assertOk();
        $this->actingAs($this->adminUser)->get(route('customers.index'))->assertOk();
        $this->actingAs($this->adminUser)->get(route('services.index'))->assertOk();
        $this->actingAs($this->adminUser)->get(route('machines.index'))->assertOk();
        $this->actingAs($this->adminUser)->get(route('expenses.index'))->assertOk();
        $this->actingAs($this->adminUser)->get(route('jobs.show', $this->job))->assertOk();
    }

    public function test_store_manager_can_supervise_operations_and_reports()
    {
        // Manager can view reports and export P&L
        $this->actingAs($this->managerUser)->get(route('reports.index'))->assertOk();
        $this->actingAs($this->managerUser)->get(route('reports.export-pnl'))->assertOk();

        // Manager can operate equipment, services, intake orders, customers, and expenses
        $this->actingAs($this->managerUser)->get(route('customers.index'))->assertOk();
        $this->actingAs($this->managerUser)->get(route('services.index'))->assertOk();
        $this->actingAs($this->managerUser)->get(route('machines.index'))->assertOk();
        $this->actingAs($this->managerUser)->get(route('expenses.index'))->assertOk();
        $this->actingAs($this->managerUser)->get(route('jobs.create'))->assertOk();
        $this->actingAs($this->managerUser)->get(route('jobs.show', $this->job))->assertOk();
    }

    public function test_store_manager_is_strictly_forbidden_from_settings_staff_and_roles()
    {
        // Settings forbidden
        $this->actingAs($this->managerUser)->get(route('settings.edit'))->assertStatus(403);
        $this->actingAs($this->managerUser)->put(route('settings.update'), ['shop_name' => 'Hack', 'tagline' => 'Tag', 'location' => 'Loc', 'phone' => '0700000000', 'tax_percent' => 16])->assertStatus(403);

        // Staff management forbidden
        $this->actingAs($this->managerUser)->get(route('staff.index'))->assertStatus(403);
        $this->actingAs($this->managerUser)->get(route('settings.staff'))->assertStatus(403);
        $this->actingAs($this->managerUser)->get(route('settings.permissions'))->assertStatus(403);
        $this->actingAs($this->managerUser)->post(route('staff.store'), ['name' => 'New Guy', 'email' => 'new@guy.com', 'password' => 'secret123', 'role' => 'cashier'])->assertStatus(403);

        // Role management forbidden
        $this->actingAs($this->managerUser)->get(route('roles.index'))->assertStatus(403);
        $this->actingAs($this->managerUser)->get(route('settings.roles'))->assertStatus(403);
        $this->actingAs($this->managerUser)->post(route('roles.store'), ['name' => 'custom', 'display_name' => 'Custom', 'access_level' => 'Test', 'primary_actions' => 'test'])->assertStatus(403);
    }

    public function test_cashier_can_handle_intake_orders_and_payments()
    {
        // Cashier can access customer directory & register customers
        $this->actingAs($this->cashierUser)->get(route('customers.index'))->assertOk();
        $this->actingAs($this->cashierUser)->get(route('customers.create'))->assertOk();

        // Cashier can create intake orders and view receipts
        $this->actingAs($this->cashierUser)->get(route('jobs.create'))->assertOk();
        $this->actingAs($this->cashierUser)->get(route('jobs.show', $this->job))->assertOk();
        $this->actingAs($this->cashierUser)->get(route('jobs.receipt', $this->job))->assertOk();

        // Cashier can record payments
        $response = $this->actingAs($this->cashierUser)->post(route('jobs.collect-payment', $this->job), [
            'amount' => 500,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('payments', ['job_id' => $this->job->id]);
    }

    public function test_cashier_cannot_alter_machine_statuses_or_access_admin_features()
    {
        // Machine status alterations blocked
        $this->actingAs($this->cashierUser)
            ->post(route('machines.assign', $this->machine), ['job_id' => $this->job->id])
            ->assertStatus(403);

        $this->actingAs($this->cashierUser)
            ->post(route('machines.release', $this->machine))
            ->assertStatus(403);

        $this->actingAs($this->cashierUser)
            ->post(route('jobs.assign-machine', $this->job), ['machine_id' => $this->machine->id])
            ->assertStatus(403);

        // Management & Admin features blocked
        $this->actingAs($this->cashierUser)->get(route('settings.edit'))->assertStatus(403);
        $this->actingAs($this->cashierUser)->get(route('staff.index'))->assertStatus(403);
        $this->actingAs($this->cashierUser)->get(route('roles.index'))->assertStatus(403);
        $this->actingAs($this->cashierUser)->get(route('reports.index'))->assertStatus(403);
        $this->actingAs($this->cashierUser)->get(route('expenses.index'))->assertStatus(403);
    }

    public function test_laundry_operator_can_allocate_machines_and_update_status()
    {
        // Operator can view order queue and order details
        $this->actingAs($this->operatorUser)->get(route('jobs.index'))->assertOk();
        $this->actingAs($this->operatorUser)->get(route('jobs.show', $this->job))->assertOk();

        // Operator can allocate machinery to order
        $assignResponse = $this->actingAs($this->operatorUser)
            ->post(route('jobs.assign-machine', $this->job), [
                'machine_id' => $this->machine->id,
            ]);
        $assignResponse->assertRedirect();
        $this->job->refresh();
        $this->assertEquals('in_progress', $this->job->status->value ?? $this->job->status);

        // Operator can mark ready with rack location
        $readyResponse = $this->actingAs($this->operatorUser)
            ->post(route('jobs.mark-ready', $this->job), [
                'rack_location' => 'Rack B-12',
            ]);
        $readyResponse->assertRedirect();
        $this->job->refresh();
        $this->assertEquals('ready', $this->job->status->value ?? $this->job->status);
        $this->assertEquals('Rack B-12', $this->job->rack_location);
    }

    public function test_laundry_operator_cannot_view_customers_pricing_receipts_or_reports()
    {
        // Blocked from customer profiles
        $this->actingAs($this->operatorUser)->get(route('customers.index'))->assertStatus(403);
        $this->actingAs($this->operatorUser)->get(route('customers.create'))->assertStatus(403);
        $this->actingAs($this->operatorUser)->get(route('customers.show', $this->customer))->assertStatus(403);
        $this->actingAs($this->operatorUser)->get(route('customers.edit', $this->customer))->assertStatus(403);

        // Blocked from pricing catalog
        $this->actingAs($this->operatorUser)->get(route('services.index'))->assertStatus(403);

        // Blocked from customer financial receipts
        $this->actingAs($this->operatorUser)->get(route('jobs.receipt', $this->job))->assertStatus(403);

        // Blocked from payment collection
        $this->actingAs($this->operatorUser)->post(route('jobs.collect-payment', $this->job), ['amount' => 100])->assertStatus(403);

        // Blocked from order export
        $this->actingAs($this->operatorUser)->get(route('jobs.export'))->assertStatus(403);

        // Blocked from reports & expenses
        $this->actingAs($this->operatorUser)->get(route('reports.index'))->assertStatus(403);
        $this->actingAs($this->operatorUser)->get(route('reports.export-pnl'))->assertStatus(403);
        $this->actingAs($this->operatorUser)->get(route('expenses.index'))->assertStatus(403);

        // Blocked from settings & staff
        $this->actingAs($this->operatorUser)->get(route('settings.edit'))->assertStatus(403);
        $this->actingAs($this->operatorUser)->get(route('staff.index'))->assertStatus(403);
    }
}
