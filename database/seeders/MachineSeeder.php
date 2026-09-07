<?php

namespace Database\Seeders;

use App\Models\Machine;
use Illuminate\Database\Seeder;

class MachineSeeder extends Seeder
{
    public function run(): void
    {
        $machines = [
            ['name' => 'Washer 1 (15kg Heavy Duty)', 'is_available' => true, 'is_active' => true],
            ['name' => 'Washer 2 (10kg Commercial)', 'is_available' => true, 'is_active' => true],
            ['name' => 'Dry Cleaner Unit A', 'is_available' => true, 'is_active' => true],
            ['name' => 'Industrial Dryer 1', 'is_available' => true, 'is_active' => true],
        ];

        foreach ($machines as $machine) {
            Machine::firstOrCreate(['name' => $machine['name']], $machine);
        }
    }
}
