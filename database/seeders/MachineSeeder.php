<?php

namespace Database\Seeders;

use App\Models\Machine;
use Illuminate\Database\Seeder;

class MachineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Inserts our 4 physical commercial laundry machines.
     */
    public function run(): void
    {
        $machines = [
            [
                'name' => 'Commercial Washer 1 (Heavy 15kg)',
                'is_available' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Commercial Washer 2 (Standard 10kg)',
                'is_available' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Dry Cleaning Solvent Unit (Delicates)',
                'is_available' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Commercial Tumble Dryer A',
                'is_available' => true,
                'is_active' => true,
            ],
        ];

        foreach ($machines as $machineData) {
            Machine::create($machineData);
        }
    }
}
