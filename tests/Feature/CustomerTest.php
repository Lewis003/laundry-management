<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_cannot_register_customer_with_duplicate_phone_number()
    {
        Customer::create([
            'name'  => 'Alice Wambui',
            'phone' => '0711000111',
            'email' => 'alice@example.com',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('customers.store'), [
                'name'  => 'Alice Clone',
                'phone' => '0711000111', // Duplicate phone
            ]);

        $response->assertSessionHasErrors(['phone']);
        $this->assertDatabaseCount('customers', 1);
    }

    public function test_customer_index_filters_by_query_string_using_when_pattern()
    {
        Customer::create(['name' => 'John Kamau', 'phone' => '0722111222']);
        Customer::create(['name' => 'Mary Otieno', 'phone' => '0733444555']);

        // Filter by phone search
        $response = $this->actingAs($this->user)
            ->get(route('customers.index', ['search' => '0722111222']));

        $response->assertOk();
        $response->assertSee('John Kamau');
        $response->assertDontSee('Mary Otieno');
    }

    public function test_cannot_delete_customer_with_active_order_history()
    {
        $customer = Customer::create(['name' => 'Peter Njoroge', 'phone' => '0799888777']);

        // Simulating existing order
        \DB::table('jobs')->insert([
            'job_number'  => 'TEST-ORD-001',
            'customer_id' => $customer->id,
            'status'      => 'received',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('customers.destroy', $customer));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }
}
