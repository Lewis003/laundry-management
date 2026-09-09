<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $cashierUser;
    protected User $operatorUser;
    protected User $riderUser;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin', 'display_name' => 'Admin / Owner', 'access_level' => 'Full Access', 'primary_actions' => ['user:manage', 'invoice:void', 'refund:issue', 'audit-log:view'], 'is_system' => true]);
        Role::create(['name' => 'manager', 'display_name' => 'Store Manager', 'access_level' => 'Operational Authority', 'primary_actions' => ['price:override', 'rewash:approve', 'route:assign', 'report:export'], 'is_system' => true]);
        Role::create(['name' => 'cashier', 'display_name' => 'Front Desk Cashier', 'access_level' => 'Customer Facing', 'primary_actions' => ['order:create', 'order:edit', 'payment:collect', 'tag:generate'], 'is_system' => true]);
        Role::create(['name' => 'operator', 'display_name' => 'Laundry Operator', 'access_level' => 'Back-end Processing', 'primary_actions' => ['status:update', 'weight:log', 'machine:allocate'], 'is_system' => true]);
        Role::create(['name' => 'rider', 'display_name' => 'Delivery Rider', 'access_level' => 'Logistics Only', 'primary_actions' => ['delivery:confirm', 'bag:audit'], 'is_system' => true]);

        $this->adminUser = User::factory()->create([
            'role'     => 'admin',
            'is_admin' => true,
        ]);

        $this->cashierUser = User::factory()->create([
            'role'     => 'cashier',
            'is_admin' => false,
        ]);

        $this->operatorUser = User::factory()->create([
            'role'     => 'operator',
            'is_admin' => false,
        ]);

        $this->riderUser = User::factory()->create([
            'role'     => 'rider',
            'is_admin' => false,
        ]);
    }

    public function test_customer_edit_view_renders_and_updates_successfully()
    {
        $customer = Customer::create([
            'name'  => 'Grace Njeri',
            'phone' => '0711998877',
            'email' => 'grace@example.com',
            'notes' => 'Handle delicate silk items with care',
        ]);

        // Verify edit view renders
        $response = $this->actingAs($this->cashierUser)
            ->get(route('customers.edit', $customer));

        $response->assertOk();
        $response->assertSee('Edit Customer Profile');
        $response->assertSee('Grace Njeri');
        $response->assertSee('0711998877');
        $response->assertSee('Handle delicate silk items with care');

        // Verify update action persists changes
        $updateResponse = $this->actingAs($this->cashierUser)
            ->put(route('customers.update', $customer), [
                'name'  => 'Grace Njeri Updated',
                'phone' => '0711998877',
                'email' => 'grace.updated@example.com',
                'notes' => 'No starch, hypoallergenic soap',
            ]);

        $updateResponse->assertRedirect(route('customers.show', $customer));
        $this->assertDatabaseHas('customers', [
            'id'    => $customer->id,
            'name'  => 'Grace Njeri Updated',
            'email' => 'grace.updated@example.com',
            'notes' => 'No starch, hypoallergenic soap',
        ]);
    }

    public function test_operator_and_rider_cannot_access_or_update_customer_edit_view()
    {
        $customer = Customer::create([
            'name'  => 'Peter Mwangi',
            'phone' => '0722334455',
        ]);

        // Operator blocked
        $this->actingAs($this->operatorUser)
            ->get(route('customers.edit', $customer))
            ->assertStatus(403);

        $this->actingAs($this->operatorUser)
            ->put(route('customers.update', $customer), [
                'name'  => 'Hacked',
                'phone' => '0722334455',
            ])
            ->assertStatus(403);

        // Rider blocked
        $this->actingAs($this->riderUser)
            ->get(route('customers.edit', $customer))
            ->assertStatus(403);

        $this->actingAs($this->riderUser)
            ->put(route('customers.update', $customer), [
                'name'  => 'Hacked Rider',
                'phone' => '0722334455',
            ])
            ->assertStatus(403);
    }

    public function test_staff_index_renders_role_matrix_table_with_default_roles()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('staff.index'));

        $response->assertOk();
        $response->assertSee('Role & Primary Permissions Matrix', false);
        $response->assertSee('Admin / Owner');
        $response->assertSee('Store Manager');
        $response->assertSee('Front Desk Cashier');
        $response->assertSee('Laundry Operator');
        $response->assertSee('Delivery Rider');
        $response->assertSee('Full Access');
        $response->assertSee('Operational Authority');
        $response->assertSee('Customer Facing');
        $response->assertSee('Back-end Processing');
        $response->assertSee('Logistics Only');
        $response->assertSee('user:manage');
        $response->assertSee('order:create');
        $response->assertSee('delivery:confirm');
    }

    public function test_admin_can_create_custom_role()
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('roles.store'), [
                'name'            => 'quality_auditor',
                'display_name'    => 'Quality Auditor',
                'access_level'    => 'Operational Authority',
                'primary_actions' => 'quality:check, garment:inspect, invoice:flag',
                'description'     => 'Inspects laundered garments for spot-checks before packing.',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('roles', [
            'name'         => 'quality_auditor',
            'display_name' => 'Quality Auditor',
            'access_level' => 'Operational Authority',
        ]);

        $role = Role::where('name', 'quality_auditor')->first();
        $this->assertEquals(['quality:check', 'garment:inspect', 'invoice:flag'], $role->primary_actions);
        $this->assertFalse($role->is_system);
    }

    public function test_admin_can_update_role_details_and_permissions()
    {
        $role = Role::where('name', 'cashier')->first();

        $response = $this->actingAs($this->adminUser)
            ->put(route('roles.update', $role), [
                'display_name'    => 'Lead Front Desk Cashier',
                'access_level'    => 'Customer Facing',
                'primary_actions' => 'order:create, order:edit, payment:collect, tag:generate, discount:apply',
                'description'     => 'Supervises front-of-house customer intake and payments.',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('roles', [
            'id'           => $role->id,
            'display_name' => 'Lead Front Desk Cashier',
        ]);

        $updatedRole = $role->fresh();
        $this->assertContains('discount:apply', $updatedRole->primary_actions);
    }

    public function test_cannot_delete_system_roles()
    {
        $adminRole = Role::where('name', 'admin')->first();

        $response = $this->actingAs($this->adminUser)
            ->delete(route('roles.destroy', $adminRole));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Core system roles cannot be deleted.');
        $this->assertDatabaseHas('roles', ['id' => $adminRole->id]);
    }

    public function test_can_delete_custom_role_with_no_assigned_staff()
    {
        $customRole = Role::create([
            'name'            => 'temp_trainee',
            'display_name'    => 'Temporary Trainee',
            'access_level'    => 'Limited Access',
            'primary_actions' => ['garment:sort'],
            'is_system'       => false,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('roles.destroy', $customRole));

        $response->assertRedirect();
        $this->assertDatabaseMissing('roles', ['id' => $customRole->id]);
    }

    public function test_non_admin_cannot_create_or_modify_roles()
    {
        $this->actingAs($this->cashierUser)
            ->post(route('roles.store'), [
                'name'            => 'hacker_role',
                'display_name'    => 'Hacker Role',
                'access_level'    => 'Full Access',
                'primary_actions' => 'system:all',
            ])
            ->assertStatus(403);

        $role = Role::first();
        $this->actingAs($this->cashierUser)
            ->put(route('roles.update', $role), [
                'display_name'    => 'Modified By Cashier',
                'access_level'    => 'Full Access',
                'primary_actions' => 'order:create',
            ])
            ->assertStatus(403);

        $this->actingAs($this->operatorUser)
            ->delete(route('roles.destroy', $role))
            ->assertStatus(403);
    }

    public function test_settings_role_management_route_renders_view()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('settings.roles'));

        $response->assertOk();
        $response->assertSee('Role Management');
        $response->assertSee('Role & Primary Permissions Matrix', false);
        $response->assertSee('Admin / Owner');
        $response->assertSee('Delivery Rider');
    }

    public function test_settings_user_roles_permissions_route_renders_view()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('settings.permissions'));

        $response->assertOk();
        $response->assertSee('User Roles & Granular Permissions', false);
        $response->assertSee('User Permission Toggles');
        $response->assertSee('View Revenue');
    }

    public function test_settings_staff_management_route_renders_view()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('settings.staff'));

        $response->assertOk();
        $response->assertSee('Staff & Team Management', false);
        $response->assertSee('Active Staff Accounts');
    }
}
