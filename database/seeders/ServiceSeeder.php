<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Inserts our 11 official laundry menu services with Kenyan prices in cents.
     */
    public function run(): void
    {
        $services = [
            // 1. Everyday Wear
            [
                'name' => 'Shirt (Wash & Steam Iron)',
                'price_in_cents' => 10000, // KSh 100.00
                'duration_minutes' => 30,
                'description' => 'Gentle machine wash with crisp collar and cuff steam press.',
            ],
            [
                'name' => 'Trousers / Jeans (Wash & Press)',
                'price_in_cents' => 12000, // KSh 120.00
                'duration_minutes' => 30,
                'description' => 'Fabric-safe washing with sharp crease pressing.',
            ],
            [
                'name' => 'T-Shirt / Polo (Wash & Fold)',
                'price_in_cents' => 8000, // KSh 80.00
                'duration_minutes' => 20,
                'description' => 'Standard everyday wash, tumble dry, and neat fold.',
            ],
            [
                'name' => 'Dress / Skirt (Delicate Wash & Iron)',
                'price_in_cents' => 20000, // KSh 200.00
                'duration_minutes' => 45,
                'description' => 'Delicate cycle wash with professional steam finishing.',
            ],

            // 2. Executive & Formal Dry-Cleaning
            [
                'name' => '2-Piece Suit (Dry Clean & Steam Press)',
                'price_in_cents' => 35000, // KSh 350.00
                'duration_minutes' => 60,
                'description' => 'Eco-solvent dry cleaning with structured shoulder pressing.',
            ],
            [
                'name' => '3-Piece Suit (Dry Clean & Steam Press)',
                'price_in_cents' => 45000, // KSh 450.00
                'duration_minutes' => 60,
                'description' => 'Full 3-piece dry clean (Jacket, Trousers, Waistcoat).',
            ],
            [
                'name' => 'Blazer / Coat (Dry Clean & Press)',
                'price_in_cents' => 30000, // KSh 300.00
                'duration_minutes' => 40,
                'description' => 'Deep solvent clean for wool, tweed, or polyester coats.',
            ],

            // 3. Heavy Bedding & Linens
            [
                'name' => 'Heavy Duvet / Comforter (Machine Wash)',
                'price_in_cents' => 80000, // KSh 800.00
                'duration_minutes' => 90,
                'description' => '15kg heavy commercial machine wash with thermal sanitization.',
            ],
            [
                'name' => 'Heavy Blanket (Machine Wash & Dry)',
                'price_in_cents' => 60000, // KSh 600.00
                'duration_minutes' => 75,
                'description' => 'Deep wash and anti-shrink tumble dry for heavy blankets.',
            ],
            [
                'name' => 'Bedsheets & Pillowcases (Wash & Iron)',
                'price_in_cents' => 25000, // KSh 250.00
                'duration_minutes' => 30,
                'description' => 'Complete bed linen wash with flatwork ironing.',
            ],

            // 4. Bulk Hamper Wash
            [
                'name' => 'Everyday Clothes Hamper (Wash & Fold up to 5kg)',
                'price_in_cents' => 50000, // KSh 500.00
                'duration_minutes' => 60,
                'description' => 'Bulk everyday family clothes wash, tumble dry, and pack.',
            ],
        ];

        foreach ($services as $serviceData) {
            Service::create($serviceData);
        }
    }
}
