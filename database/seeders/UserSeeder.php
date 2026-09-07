<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin / Owner
        User::updateOrCreate(
            ['email' => 'admin@taison.co.ke'],
            [
                'name' => 'Lewis Sila (Admin)',
                'password' => Hash::make('Lewis123'),
                'role' => 'admin',
            ]
        );

        // 2. Cashier (Front Desk & Payments)
        User::updateOrCreate(
            ['email' => 'cashier@taison.co.ke'],
            [
                'name' => 'Grace Wanjiku (Cashier)',
                'password' => Hash::make('Cashier123'),
                'role' => 'cashier',
            ]
        );

        // 3. Operator (Washing Bay & Machine Operations)
        User::updateOrCreate(
            ['email' => 'operator@taison.co.ke'],
            [
                'name' => 'Otieno Juma (Operator)',
                'password' => Hash::make('Operator123'),
                'role' => 'operator',
            ]
        );
    }
}
