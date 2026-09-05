<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates default staff accounts with roles and hashed passwords.
     */
    public function run(): void
    {
        // 1. Store Manager / Admin (Full Access to Revenue, Expenditures, Staff)
        User::create([
            'name' => 'Lewis Sila (Manager)',
            'email' => 'admin@taison.co.ke',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 2. Front-Desk Cashier (Customer intake, M-Pesa deposits, receipts)
        User::create([
            'name' => 'Sarah Wanjiku (Cashier)',
            'email' => 'cashier@taison.co.ke',
            'password' => Hash::make('password'),
            'role' => 'cashier',
        ]);

        // 3. Washing Floor Attendant / Operator (Machine operation & racking)
        User::create([
            'name' => 'James Otieno (Washer)',
            'email' => 'operator@taison.co.ke',
            'password' => Hash::make('password'),
            'role' => 'operator',
        ]);
    }
}
