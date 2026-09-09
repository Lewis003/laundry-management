<?php

namespace Tests\Feature;

use App\Enums\JobStatus;
use App\Enums\MachineStatus;
use App\Models\Customer;
use App\Models\Job;
use App\Models\Machine;
use App\Models\User;
use App\Services\MachineService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class MachineTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;
    protected User $cashier;
    protected MachineService $machineService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->machineService = app(MachineService::class);

        $this->admin = User::factory()->create([
            'role'     => 'admin',
            'is_admin' => true,
        ]);

        $this->operator = User::factory()->create([
            'role'     => 'operator',
            'is_admin' => false,
        ]);

        $this->cashier = User::factory()->create([
            'role'     => 'cashier',
            'is_admin' => false,
        ]);
    }

    public function test_can_filter_machines_by_type_and_status(): void
    {
        $washer = Machine::create([
            'name'        => 'Washer 01',
            'type'        => 'washer',
            'status'      => MachineStatus::AVAILABLE,
            'capacity_kg' => 15.00,
        ]);

        $dryer = Machine::create([
            'name'        => 'Dryer 01',
            'type'        => 'dryer',
            'status'      => MachineStatus::MAINTENANCE,
            'capacity_kg' => 12.00,
        ]);

        $response = $this->actingAs($this->admin)->get(route('machines.index', ['type' => 'washer']));
        $response->assertOk();
        $response->assertSee('Washer 01');
        $response->assertDontSee('Dryer 01');
    }

    public function test_admin_can_create_machine(): void
    {
        $response = $this->actingAs($this->admin)->post(route('machines.store'), [
            'name'        => 'Washer Heavy 05',
            'type'        => 'washer',
            'capacity_kg' => 20.00,
            'status'      => 'available',
        ]);

        $response->assertRedirect(route('machines.index'));
        $this->assertDatabaseHas('machines', [
            'name' => 'Washer Heavy 05',
            'type' => 'washer',
        ]);
    }

    public function test_unauthorized_user_cannot_create_machine(): void
    {
        $response = $this->actingAs($this->cashier)->post(route('machines.store'), [
            'name'        => 'Unauthorized Washer',
            'type'        => 'washer',
            'capacity_kg' => 15.00,
            'status'      => 'available',
        ]);

        $response->assertForbidden();
    }

    public function test_assign_machine_atomically_transitions_machine_and_job(): void
    {
        $machine = Machine::create([
            'name'        => 'Speed Washer 01',
            'type'        => 'washer',
            'status'      => MachineStatus::AVAILABLE,
            'capacity_kg' => 15.00,
        ]);

        $customer = Customer::create([
            'name'  => 'Test Customer',
            'phone' => '0711223344',
        ]);

        $job = Job::create([
            'job_number'  => 'JOB-TEST-001',
            'customer_id' => $customer->id,
            'status'      => JobStatus::RECEIVED,
        ]);

        $updatedJob = $this->machineService->assignMachineToJob($machine, $job, $this->operator->id);

        $this->assertEquals(JobStatus::IN_PROGRESS, $updatedJob->status);
        $this->assertEquals($machine->id, $updatedJob->machine_id);

        $machine->refresh();
        $this->assertEquals(MachineStatus::IN_USE, $machine->status);
        $this->assertFalse($machine->is_available);
    }

    public function test_cannot_assign_machine_already_in_use(): void
    {
        $machine = Machine::create([
            'name'        => 'Speed Washer 02',
            'type'        => 'washer',
            'status'      => MachineStatus::IN_USE,
            'is_available'=> false,
            'capacity_kg' => 15.00,
        ]);

        $customer = Customer::create([
            'name'  => 'Customer 1',
            'phone' => '0711223355',
        ]);

        // Existing job running in this machine
        Job::create([
            'job_number'  => 'JOB-EXISTING',
            'customer_id' => $customer->id,
            'machine_id'  => $machine->id,
            'status'      => JobStatus::IN_PROGRESS,
        ]);

        $newJob = Job::create([
            'job_number'  => 'JOB-NEW',
            'customer_id' => $customer->id,
            'status'      => JobStatus::RECEIVED,
        ]);

        $this->expectException(RuntimeException::class);
        $this->machineService->assignMachineToJob($machine, $newJob, $this->operator->id);
    }

    public function test_machine_status_transition_validation(): void
    {
        $machine = Machine::create([
            'name'        => 'Dryer Unit 03',
            'type'        => 'dryer',
            'status'      => MachineStatus::IN_USE,
            'capacity_kg' => 10.00,
        ]);

        // Attempt invalid direct transition from IN_USE to RETIRED (must be released or set to maintenance first)
        $this->expectException(DomainException::class);
        $this->machineService->updateMachine($machine, [
            'status' => 'retired',
        ]);
    }
}

