<?php

namespace Database\Seeders;

use App\Enums\JobStatus;
use App\Models\Customer;
use App\Models\Job;
use App\Models\JobItem;
use App\Models\Machine;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class SampleOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates 3 realistic customer orders across different workflow stages.
     */
    public function run(): void
    {
        $operator = User::where('role', 'operator')->first();
        $duvetService = Service::where('name', 'LIKE', '%Duvet%')->first();
        $shirtService = Service::where('name', 'LIKE', '%Shirt%')->first();
        $suitService = Service::where('name', 'LIKE', '%Suit%')->first();
        $hamperService = Service::where('name', 'LIKE', '%Hamper%')->first();
        $washer1 = Machine::where('name', 'LIKE', '%Washer 1%')->first();

        // -------------------------------------------------------------
        // SCENARIO 1: Otieno Peter (Currently WASHING in Washer 1)
        // -------------------------------------------------------------
        $customer1 = Customer::create([
            'name' => 'Otieno Peter',
            'phone' => '0712345678',
        ]);

        // Lock Washer 1 since this order is actively washing
        $washer1->update(['is_available' => false]);

        $job1 = Job::create([
            'job_number' => 'LND-20260905-A8F2',
            'customer_id' => $customer1->id,
            'machine_id' => $washer1->id,
            'status' => JobStatus::IN_PROGRESS,
            'assigned_worker_id' => $operator?->id,
            'rack_location' => null,
            'notes' => 'White duvet with small coffee stain on edge.',
        ]);

        // Line Item 1: Heavy Duvet (KSh 800)
        JobItem::create([
            'job_id' => $job1->id,
            'service_id' => $duvetService->id,
            'quantity' => 1,
            'price_in_cents' => $duvetService->price_in_cents, // Snapshot price: 80000
        ]);

        // Line Item 2: 2 Shirts (KSh 200)
        JobItem::create([
            'job_id' => $job1->id,
            'service_id' => $shirtService->id,
            'quantity' => 2,
            'price_in_cents' => $shirtService->price_in_cents, // Snapshot price: 10000
        ]);

        // Payment: 50% M-Pesa Deposit of KSh 500.00 (50000 cents) -> Balance remaining: KSh 500.00
        Payment::create([
            'job_id' => $job1->id,
            'amount_in_cents' => 50000,
            'payment_method' => 'mpesa',
            'transaction_reference' => 'QHX7829KL1',
            'notes' => '50% Upfront Deposit via M-Pesa',
        ]);

        // -------------------------------------------------------------
        // SCENARIO 2: Grace Njeri (READY on Storage Rack B-14)
        // -------------------------------------------------------------
        $customer2 = Customer::create([
            'name' => 'Grace Njeri',
            'phone' => '0798765432',
        ]);

        $job2 = Job::create([
            'job_number' => 'LND-20260905-K3B9',
            'customer_id' => $customer2->id,
            'machine_id' => null, // Machine was freed when wash finished!
            'status' => JobStatus::READY,
            'assigned_worker_id' => $operator?->id,
            'rack_location' => 'Rack B-14',
            'notes' => 'Dry cleaned suits hung in protective plastic bags.',
        ]);

        // Line Item: 1 Suit (KSh 350)
        JobItem::create([
            'job_id' => $job2->id,
            'service_id' => $suitService->id,
            'quantity' => 1,
            'price_in_cents' => $suitService->price_in_cents,
        ]);

        // Payment: KSh 200 Cash Deposit -> Balance remaining: KSh 150.00
        Payment::create([
            'job_id' => $job2->id,
            'amount_in_cents' => 20000,
            'payment_method' => 'cash',
            'transaction_reference' => null,
            'notes' => 'Deposit at counter drop-off',
        ]);

        // -------------------------------------------------------------
        // SCENARIO 3: David Kiprono (RECEIVED - Waiting in Queue)
        // -------------------------------------------------------------
        $customer3 = Customer::create([
            'name' => 'David Kiprono',
            'phone' => '0722334455',
        ]);

        $job3 = Job::create([
            'job_number' => 'LND-20260905-X4M1',
            'customer_id' => $customer3->id,
            'machine_id' => null, // Waiting in queue
            'status' => JobStatus::RECEIVED,
            'assigned_worker_id' => null,
            'rack_location' => null,
            'notes' => '5kg everyday hamper basket.',
        ]);

        // Line Item: 1 Hamper (KSh 500)
        JobItem::create([
            'job_id' => $job3->id,
            'service_id' => $hamperService->id,
            'quantity' => 1,
            'price_in_cents' => $hamperService->price_in_cents,
        ]);

        // Paid in full upfront via M-Pesa -> Balance: KSh 0.00 (Fully Paid)
        Payment::create([
            'job_id' => $job3->id,
            'amount_in_cents' => 50000,
            'payment_method' => 'mpesa',
            'transaction_reference' => 'QHX9911MN5',
            'notes' => 'Full Payment at drop-off',
        ]);
    }
}
