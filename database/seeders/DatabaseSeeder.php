<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Calls all seeders in sequential order.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            MachineSeeder::class,
            ServiceSeeder::class,
            SampleOrderSeeder::class,
        ]);
    }
}
