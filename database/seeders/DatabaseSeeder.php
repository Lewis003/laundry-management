<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Machine;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with Safishwa na Tai default records.
     */
    public function run(): void
    {
        // 1. Staff & Role Accounts
        $users = [
            [
                'name'     => 'System Administrator',
                'email'    => 'admin@taison.co.ke',
                'password' => Hash::make('Lewis123'),
                'role'     => 'admin',
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

        foreach ($users as $userData) {
            $userPayload = [
                'name'     => $userData['name'],
                'password' => $userData['password'],
            ];

            if (Schema::hasColumn('users', 'role')) {
                $userPayload['role'] = $userData['role'];
            }

            User::updateOrCreate(
                ['email' => $userData['email']],
                $userPayload
            );
        }

        // 2. Commercial Equipment (Washers, Dryers & Press Units)
        $machines = [
            [
                'name'        => 'Washer-Extractor 01 (Speed Queen 18kg)',
                'model'       => 'SQ-COMM-18KG',
                'type'        => 'washer',
                'capacity_kg' => 18,
            ],
            [
                'name'        => 'Washer-Extractor 02 (Girbau Heavy 25kg)',
                'model'       => 'GB-HEAVY-25KG',
                'type'        => 'washer',
                'capacity_kg' => 25,
            ],
            [
                'name'        => 'Tumble Dryer 01 (Electrolux Pro 20kg)',
                'model'       => 'EL-DRY-20KG',
                'type'        => 'dryer',
                'capacity_kg' => 20,
            ],
            [
                'name'        => 'Steam Press & Finishing Table 01',
                'model'       => 'PONY-STEAM-PRO',
                'type'        => 'press',
                'capacity_kg' => 10,
            ],
        ];

        foreach ($machines as $item) {
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
                $machinePayload['is_available'] = true;
            }
            if (Schema::hasColumn('machines', 'is_active')) {
                $machinePayload['is_active'] = true;
            }
            if (Schema::hasColumn('machines', 'status')) {
                $machinePayload['status'] = 'available';
            }

            Machine::updateOrCreate(
                ['name' => $item['name']],
                $machinePayload
            );
        }

        // 3. Official Laundry Services Catalog (Minimum Price: KSh 350.00)
        $services = [
            [
                'name'             => 'Casual Everyday Wash & Fold (Minimum Batch Load)',
                'category'         => 'wash_fold',
                'price'            => 350.00,
                'price_in_cents'   => 35000,
                'duration_minutes' => 45,
            ],
            [
                'name'             => 'Office Wear Bundle (3 Shirts/Trousers Wash & Press)',
                'category'         => 'bundles',
                'price'            => 350.00,
                'price_in_cents'   => 35000,
                'duration_minutes' => 45,
            ],
            [
                'name'             => 'Sneakers & Canvas Footwear Care (Deep Scrub & Deodorize)',
                'category'         => 'shoes',
                'price'            => 350.00,
                'price_in_cents'   => 35000,
                'duration_minutes' => 40,
            ],
            [
                'name'             => 'Curtains & Heavy Drapery Cleaning (Per Set / Pair)',
                'category'         => 'household',
                'price'            => 450.00,
                'price_in_cents'   => 45000,
                'duration_minutes' => 50,
            ],
            [
                'name'             => 'Industrial Boiler Suits & Overalls (Heavy Grease Wash)',
                'category'         => 'workwear',
                'price'            => 450.00,
                'price_in_cents'   => 45000,
                'duration_minutes' => 60,
            ],
            [
                'name'             => 'Hospitality & Commercial Linen (5kg Batch Wash & Iron)',
                'category'         => 'commercial',
                'price'            => 500.00,
                'price_in_cents'   => 50000,
                'duration_minutes' => 50,
            ],
            [
                'name'             => 'Executive Suit 2-Piece (Dry Clean & Steam Press)',
                'category'         => 'dry_cleaning',
                'price'            => 650.00,
                'price_in_cents'   => 65000,
                'duration_minutes' => 60,
            ],
            [
                'name'             => 'Heavy Duvet / Comforter (King/Queen Size Sanitized)',
                'category'         => 'bedding',
                'price'            => 850.00,
                'price_in_cents'   => 85000,
                'duration_minutes' => 75,
            ],
            [
                'name'             => 'Premium 3-Piece Executive Suit (Dry Clean & Form Press)',
                'category'         => 'dry_cleaning',
                'price'            => 950.00,
                'price_in_cents'   => 95000,
                'duration_minutes' => 70,
            ],
            [
                'name'             => 'Delicate Evening Dress / Silk Gown (Special Solvent Care)',
                'category'         => 'specialty',
                'price'            => 1200.00,
                'price_in_cents'   => 120000,
                'duration_minutes' => 80,
            ],
            [
                'name'             => 'Heavy Winter Trench Coat / Leather Jacket Care',
                'category'         => 'specialty',
                'price'            => 1500.00,
                'price_in_cents'   => 150000,
                'duration_minutes' => 90,
            ],
        ];

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

            Service::updateOrCreate(
                ['name' => $serviceData['name']],
                $servicePayload
            );
        }

        // 4. Initial Registered Customers (Strictly Name & Phone)
        $customers = [
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
        ];

        foreach ($customers as $customerData) {
            $customerPayload = [
                'name' => $customerData['name'],
            ];

            if (Schema::hasColumn('customers', 'phone')) {
                $customerPayload['phone'] = $customerData['phone'];
            }

            Customer::updateOrCreate(
                ['phone' => $customerData['phone']],
                $customerPayload
            );
        }
    }
}
