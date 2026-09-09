<?php

namespace Database\Seeders;

use App\Actions\CreateJobAction;
use App\Enums\JobStatus;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Job;
use App\Models\Machine;
use App\Models\Payment;
use App\Models\Service;
use App\Models\ShopSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with realistic production-ready starting records.
     */
    public function run(): void
    {
        // 1. Shop & Branch Configuration (Dynamic Multi-Owner Foundation)
        ShopSetting::updateOrCreate(
            ['id' => 1],
            [
                'shop_name'             => 'Safishwa na Tai',
                'tagline'               => 'Commercial Laundry & Dry Cleaning',
                'location'              => 'Industrial Area, Nairobi',
                'phone'                 => '+254 700 000 001',
                'email'                 => 'info@safishwa.co.ke',
                'mpesa_till_or_paybill' => '542310',
                'receipt_footer'        => 'Thank you for choosing Safishwa na Tai! Garments not collected within 30 days are subject to disposal/auction.',
                'currency'              => 'KSh',
                'tax_percent'           => 16.00,
            ]
        );

        // 2. Staff Accounts & Role Matrix
        $users = [
            [
                'name'     => 'System Administrator',
                'email'    => 'admin@taison.co.ke',
                'password' => Hash::make('Lewis123'),
                'role'     => 'admin',
            ],
            [
                'name'     => 'Plant Operations Manager',
                'email'    => 'manager@taison.co.ke',
                'password' => Hash::make('password'),
                'role'     => 'manager',
            ],
            [
                'name'     => 'Front Desk Cashier',
                'email'    => 'cashier@taison.co.ke',
                'password' => Hash::make('password'),
                'role'     => 'cashier',
            ],
            [
                'name'     => 'Wash Bay Operator',
                'email'    => 'operator@taison.co.ke',
                'password' => Hash::make('password1'),
                'role'     => 'operator',
            ],
        ];

        $createdUsers = [];
        foreach ($users as $userData) {
            $userPayload = [
                'name'     => $userData['name'],
                'password' => $userData['password'],
            ];

            if (Schema::hasColumn('users', 'role')) {
                $userPayload['role'] = $userData['role'];
            }

            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                $userPayload
            );
            $createdUsers[$userData['role']] = $user;
        }

        $adminUser = $createdUsers['admin'] ?? User::first();
        $cashierUser = $createdUsers['cashier'] ?? $adminUser;

        // 3. Commercial Equipment Fleet (Washers, Dryers & Press Units)
        $machinesData = [
            [
                'name'        => 'Washer-Extractor 01 (Speed Queen 18kg)',
                'model'       => 'SQ-COMM-18KG',
                'type'        => 'washer',
                'capacity_kg' => 18,
                'status'      => 'available',
                'is_avail'    => true,
            ],
            [
                'name'        => 'Washer-Extractor 02 (Girbau Heavy 25kg)',
                'model'       => 'GB-HEAVY-25KG',
                'type'        => 'washer',
                'capacity_kg' => 25,
                'status'      => 'in_use',
                'is_avail'    => false,
            ],
            [
                'name'        => 'Tumble Dryer 01 (Electrolux Pro 20kg)',
                'model'       => 'EL-DRY-20KG',
                'type'        => 'dryer',
                'capacity_kg' => 20,
                'status'      => 'available',
                'is_avail'    => true,
            ],
            [
                'name'        => 'Steam Press & Finishing Table 01',
                'model'       => 'PONY-STEAM-PRO',
                'type'        => 'press',
                'capacity_kg' => 10,
                'status'      => 'available',
                'is_avail'    => true,
            ],
        ];

        $seededMachines = [];
        foreach ($machinesData as $item) {
            $machinePayload = [
                'name' => $item['name'],
            ];

            if (Schema::hasColumn('machines', 'model')) {
                $machinePayload['model'] = $item['model'];
            }
            if (Schema::hasColumn('machines', 'type')) {
                $machinePayload['type'] = $item['type'];
            }
            if (Schema::hasColumn('machines', 'capacity_kg')) {
                $machinePayload['capacity_kg'] = $item['capacity_kg'];
            } elseif (Schema::hasColumn('machines', 'capacity')) {
                $machinePayload['capacity'] = $item['capacity_kg'];
            }
            if (Schema::hasColumn('machines', 'is_available')) {
                $machinePayload['is_available'] = $item['is_avail'];
            }
            if (Schema::hasColumn('machines', 'is_active')) {
                $machinePayload['is_active'] = true;
            }
            if (Schema::hasColumn('machines', 'status')) {
                $machinePayload['status'] = $item['status'];
            }

            $m = Machine::updateOrCreate(
                ['name' => $item['name']],
                $machinePayload
            );
            $seededMachines[$item['type']] = $m;
        }

        // 4. Official Laundry Services Catalog
        $services = [
            [
                'name'             => 'Casual Everyday Wash & Fold (Batch Load)',
                'category'         => 'wash_fold',
                'price'            => 350.00,
                'price_in_cents'   => 35000,
                'duration_minutes' => 45,
            ],
            [
                'name'             => 'Office Wear Bundle (3 Shirts/Pants Press)',
                'category'         => 'bundles',
                'price'            => 350.00,
                'price_in_cents'   => 35000,
                'duration_minutes' => 45,
            ],
            [
                'name'             => 'Sneakers & Canvas Footwear Care',
                'category'         => 'shoes',
                'price'            => 350.00,
                'price_in_cents'   => 35000,
                'duration_minutes' => 40,
            ],
            [
                'name'             => 'Curtains & Heavy Drapery Cleaning (Pair)',
                'category'         => 'household',
                'price'            => 450.00,
                'price_in_cents'   => 45000,
                'duration_minutes' => 50,
            ],
            [
                'name'             => 'Industrial Boiler Suits & Heavy Overalls',
                'category'         => 'workwear',
                'price'            => 450.00,
                'price_in_cents'   => 45000,
                'duration_minutes' => 60,
            ],
            [
                'name'             => 'Hospitality & Commercial Linen (5kg Load)',
                'category'         => 'commercial',
                'price'            => 500.00,
                'price_in_cents'   => 50000,
                'duration_minutes' => 50,
            ],
            [
                'name'             => 'Executive Suit 2-Piece (Dry Clean & Press)',
                'category'         => 'dry_cleaning',
                'price'            => 650.00,
                'price_in_cents'   => 65000,
                'duration_minutes' => 60,
            ],
            [
                'name'             => 'Heavy Duvet / Comforter (King/Queen)',
                'category'         => 'bedding',
                'price'            => 850.00,
                'price_in_cents'   => 85000,
                'duration_minutes' => 75,
            ],
            [
                'name'             => 'Premium 3-Piece Executive Suit Dry Clean',
                'category'         => 'dry_cleaning',
                'price'            => 950.00,
                'price_in_cents'   => 95000,
                'duration_minutes' => 70,
            ],
            [
                'name'             => 'Delicate Evening Dress / Silk Gown Care',
                'category'         => 'specialty',
                'price'            => 1200.00,
                'price_in_cents'   => 120000,
                'duration_minutes' => 80,
            ],
            [
                'name'             => 'Heavy Winter Trench Coat / Leather Care',
                'category'         => 'specialty',
                'price'            => 1500.00,
                'price_in_cents'   => 150000,
                'duration_minutes' => 90,
            ],
        ];

        $seededServices = [];
        foreach ($services as $serviceData) {
            $servicePayload = [
                'name' => $serviceData['name'],
            ];

            if (Schema::hasColumn('services', 'price_in_cents')) {
                $servicePayload['price_in_cents'] = $serviceData['price_in_cents'];
            }
            if (Schema::hasColumn('services', 'price_cents')) {
                $servicePayload['price_cents'] = $serviceData['price_in_cents'];
            }
            if (Schema::hasColumn('services', 'price')) {
                $servicePayload['price'] = $serviceData['price'];
            }
            if (Schema::hasColumn('services', 'duration_minutes')) {
                $servicePayload['duration_minutes'] = $serviceData['duration_minutes'];
            }
            if (Schema::hasColumn('services', 'slug')) {
                $servicePayload['slug'] = Str::slug($serviceData['name']);
            }
            if (Schema::hasColumn('services', 'description')) {
                $servicePayload['description'] = $serviceData['name'];
            }
            if (Schema::hasColumn('services', 'category')) {
                $servicePayload['category'] = $serviceData['category'];
            }
            if (Schema::hasColumn('services', 'is_active')) {
                $servicePayload['is_active'] = true;
            }

            $s = Service::updateOrCreate(
                ['name' => $serviceData['name']],
                $servicePayload
            );
            $seededServices[] = $s;
        }

        // 5. Registered Customer Base
        $customersData = [
            [
                'name'  => 'Lewis Sila',
                'phone' => '0113361407',
            ],
            [
                'name'  => 'Gamma Operations',
                'phone' => '0712345678',
            ],
            [
                'name'  => 'Vincent Bundi',
                'phone' => '0711223344',
            ],
            [
                'name'  => 'Nairobi Safari Club',
                'phone' => '0722112233',
            ],
            [
                'name'  => 'Apex Logistics Ltd',
                'phone' => '0733445566',
            ],
        ];

        $seededCustomers = [];
        foreach ($customersData as $customerData) {
            $customerPayload = [
                'name' => $customerData['name'],
            ];

            if (Schema::hasColumn('customers', 'phone')) {
                $customerPayload['phone'] = $customerData['phone'];
            }

            $c = Customer::updateOrCreate(
                ['phone' => $customerData['phone']],
                $customerPayload
            );
            $seededCustomers[] = $c;
        }

        // 6. Distinct Operational Expenses ("Where Does Company Money Go?")
        $expensesData = [
            [
                'title'           => '10,000L Industrial Clean Water Tanker Delivery',
                'category'        => 'water',
                'amount_in_cents' => 850000, // KSh 8,500.00
                'expense_date'    => Carbon::today()->subDays(2)->toDateString(),
                'payment_method'  => 'mpesa',
                'reference_code'  => 'QHX9182390',
                'notes'           => 'Direct borehole clean water tanker for wash bays',
            ],
            [
                'title'           => 'KPLC Commercial 3-Phase Power Units (Tokens)',
                'category'        => 'electricity',
                'amount_in_cents' => 1420000, // KSh 14,200.00
                'expense_date'    => Carbon::today()->subDays(3)->toDateString(),
                'payment_method'  => 'mpesa',
                'reference_code'  => 'QKZ8839201',
                'notes'           => 'Plant industrial prepaid electricity tokens',
            ],
            [
                'title'           => 'Wash Bay Operators & Attendants Weekly Wages',
                'category'        => 'salaries',
                'amount_in_cents' => 1800000, // KSh 18,000.00
                'expense_date'    => Carbon::today()->subDays(1)->toDateString(),
                'payment_method'  => 'bank_transfer',
                'reference_code'  => 'SAL-WK36-2026',
                'notes'           => 'Weekly compensation for 3 plant operators and front cashier',
            ],
            [
                'title'           => '60L Drum Concentrated Industrial Detergent & Softener',
                'category'        => 'supplies',
                'amount_in_cents' => 950000, // KSh 9,500.00
                'expense_date'    => Carbon::today()->subDays(5)->toDateString(),
                'payment_method'  => 'mpesa',
                'reference_code'  => 'SUP-CHEM-049',
                'notes'           => 'Enzyme detergent + stain lifter solvent',
            ],
            [
                'title'           => 'Girbau Heavy Washer Bearing Lubrication & Belt Service',
                'category'        => 'maintenance',
                'amount_in_cents' => 350000, // KSh 3,500.00
                'expense_date'    => Carbon::today()->subDays(4)->toDateString(),
                'payment_method'  => 'mpesa',
                'reference_code'  => 'SRV-MCH-012',
                'notes'           => 'Routine 250-cycle preventative mechanical service',
            ],
            [
                'title'           => 'Industrial Area Bay Plant Premises Monthly Rent',
                'category'        => 'rent',
                'amount_in_cents' => 3500000, // KSh 35,000.00
                'expense_date'    => Carbon::now()->startOfMonth()->toDateString(),
                'payment_method'  => 'bank_transfer',
                'reference_code'  => 'RNT-202609-01',
                'notes'           => 'Factory premise lease settlement',
            ],
            [
                'title'           => 'Thermal POS Receipt Rolls & Heavy Garment Tag Pins',
                'category'        => 'other',
                'amount_in_cents' => 120000, // KSh 1,200.00
                'expense_date'    => Carbon::today()->toDateString(),
                'payment_method'  => 'cash',
                'reference_code'  => 'PTY-2026-90',
                'notes'           => 'Front counter stationery and tagging tags',
            ],
        ];

        foreach ($expensesData as $exp) {
            Expense::updateOrCreate(
                ['reference_code' => $exp['reference_code']],
                [
                    'title'           => $exp['title'],
                    'category'        => $exp['category'],
                    'amount_in_cents' => $exp['amount_in_cents'],
                    'expense_date'    => $exp['expense_date'],
                    'payment_method'  => $exp['payment_method'],
                    'notes'           => $exp['notes'],
                    'created_by'      => $adminUser->id,
                ]
            );
        }

        // 7. Orders across Workflow Lifecycle with Rack Locations & Payments
        $createJobAction = app(CreateJobAction::class);

        // Order 1: RECEIVED (Front desk intake, awaiting wash bay assignment)
        if (!Job::where('notes', 'Sample Order 1 - Intake')->exists()) {
            $createJobAction->execute(
                $seededCustomers[0], // Lewis Sila
                [
                    ['service_id' => $seededServices[0]->id, 'quantity' => 1], // Wash & Fold
                    ['service_id' => $seededServices[1]->id, 'quantity' => 1], // Office Wear Bundle
                ],
                'Sample Order 1 - Intake',
                40000, // Deposit KSh 400.00
                'mpesa',
                $cashierUser->id,
                true
            );
        }

        // Order 2: IN_PROGRESS (Currently washing in Washer 02)
        $washer02 = Machine::where('name', 'like', '%Washer-Extractor 02%')->first();
        if (!Job::where('notes', 'Sample Order 2 - Washing Bay')->exists()) {
            $job2 = $createJobAction->execute(
                $seededCustomers[1], // Gamma Operations
                [
                    ['service_id' => $seededServices[4]->id, 'quantity' => 2], // Boiler Suits
                    ['service_id' => $seededServices[2]->id, 'quantity' => 1], // Sneakers
                ],
                'Sample Order 2 - Washing Bay',
                100000, // Deposit KSh 1,000.00
                'mpesa',
                $cashierUser->id,
                true
            );

            $job2->update([
                'status'     => JobStatus::IN_PROGRESS ?? 'in_progress',
                'machine_id' => $washer02?->id,
            ]);
        }

        // Order 3: READY (Packaged & Shelved at Rack A-04, Paid in full)
        if (!Job::where('notes', 'Sample Order 3 - Ready Shelved')->exists()) {
            $job3 = $createJobAction->execute(
                $seededCustomers[2], // Vincent Bundi
                [
                    ['service_id' => $seededServices[6]->id, 'quantity' => 1], // Executive Suit
                    ['service_id' => $seededServices[7]->id, 'quantity' => 1], // Heavy Duvet
                ],
                'Sample Order 3 - Ready Shelved',
                0,
                'mpesa',
                $cashierUser->id,
                true
            );

            $totalCents = (int) round($job3->total_price * 100);
            Payment::create([
                'job_id'            => $job3->id,
                'amount_in_cents'   => $totalCents,
                'payment_method'    => 'mpesa',
                'payment_reference' => 'QHN' . strtoupper(Str::random(7)),
                'received_by'       => $cashierUser->id,
                'paid_at'           => Carbon::now()->subHours(2),
            ]);

            $job3->update([
                'status'        => JobStatus::READY ?? 'ready',
                'rack_location' => 'Rack A-04',
            ]);
        }

        // Order 4: READY (Packaged & Shelved at Shelf 02-B, Partial balance pending at collection)
        if (!Job::where('notes', 'Sample Order 4 - Ready Pending Balance')->exists()) {
            $job4 = $createJobAction->execute(
                $seededCustomers[3], // Nairobi Safari Club
                [
                    ['service_id' => $seededServices[5]->id, 'quantity' => 3], // Commercial Linen
                ],
                'Sample Order 4 - Ready Pending Balance',
                100000, // Deposit KSh 1,000.00
                'mpesa',
                $cashierUser->id,
                true
            );

            $job4->update([
                'status'        => JobStatus::READY ?? 'ready',
                'rack_location' => 'Shelf 02-B',
            ]);
        }

        // Order 5: COLLECTED (Fully completed, paid in full, handed over from Rack B-01)
        if (!Job::where('notes', 'Sample Order 5 - Collected')->exists()) {
            $job5 = $createJobAction->execute(
                $seededCustomers[4], // Apex Logistics Ltd
                [
                    ['service_id' => $seededServices[8]->id, 'quantity' => 2], // 2x Premium 3-Piece Suit
                ],
                'Sample Order 5 - Collected',
                0,
                'mpesa',
                $cashierUser->id,
                true
            );

            $totalCents5 = (int) round($job5->total_price * 100);
            Payment::create([
                'job_id'            => $job5->id,
                'amount_in_cents'   => $totalCents5,
                'payment_method'    => 'mpesa',
                'payment_reference' => 'QHP' . strtoupper(Str::random(7)),
                'received_by'       => $cashierUser->id,
                'paid_at'           => Carbon::now()->subDay(),
            ]);

            $job5->update([
                'status'        => JobStatus::COLLECTED ?? 'collected',
                'rack_location' => 'Rack B-01',
            ]);
        }
    }
}
