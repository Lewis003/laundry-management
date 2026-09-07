<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'name'             => 'Casual Everyday Wash & Fold (Minimum Batch Load)',
                'price_in_cents'   => 35000, // KSh 350.00 (Minimum base price)
                'duration_minutes' => 45,
                'description'      => 'Everyday casual wear washed, tumble dried, and folded. Minimum batch threshold.',
            ],
            [
                'name'             => 'Office Wear Bundle (3 Shirts/Trousers Wash & Press)',
                'price_in_cents'   => 35000, // KSh 350.00
                'duration_minutes' => 45,
                'description'      => 'Pack of 3 workwear garments washed, dried, and crisp steam ironed.',
            ],
            [
                'name'             => 'Sneakers & Canvas Footwear Care (Deep Scrub & Deodorize)',
                'price_in_cents'   => 35000, // KSh 350.00
                'duration_minutes' => 40,
                'description'      => 'Hand scrub stain removal, upper washing, sole whitening, and deodorization.',
            ],
            [
                'name'             => 'Curtains & Heavy Drapery Cleaning (Per Set / Pair)',
                'price_in_cents'   => 45000, // KSh 450.00
                'duration_minutes' => 50,
                'description'      => 'Dust extraction, deep fabric wash, and hanging steam press.',
            ],
            [
                'name'             => 'Industrial Boiler Suits & Work Overalls (Heavy Grease Wash)',
                'price_in_cents'   => 45000, // KSh 450.00
                'duration_minutes' => 60,
                'description'      => 'Commercial grease extraction, heavy-duty detergent sanitization, and drying.',
            ],
            [
                'name'             => 'Hospitality & Commercial Linen (5kg Batch Wash & Iron)',
                'price_in_cents'   => 50000, // KSh 500.00
                'duration_minutes' => 50,
                'description'      => 'Hotel and Airbnb bedsheets, duvet covers, and bath towels weighed and pressed.',
            ],
            [
                'name'             => 'Executive Suit 2-Piece (Dry Clean & Steam Press)',
                'price_in_cents'   => 65000, // KSh 650.00
                'duration_minutes' => 60,
                'description'      => 'Complete coat and trousers chemical dry cleaning with form finishing on hanger.',
            ],
            [
                'name'             => 'Heavy Duvet / Comforter (King/Queen Size Sanitized)',
                'price_in_cents'   => 85000, // KSh 850.00
                'duration_minutes' => 75,
                'description'      => 'Large drum wash, anti-dustmite antibacterial treatment, and fluff dry.',
            ],
            [
                'name'             => 'Premium 3-Piece Executive Suit (Dry Clean & Form Press)',
                'price_in_cents'   => 95000, // KSh 950.00
                'duration_minutes' => 70,
                'description'      => 'Coat, waistcoat, and trousers dry cleaned with luxury fabric finishing.',
            ],
            [
                'name'             => 'Delicate Evening Dress / Silk Gown (Special Solvent Care)',
                'price_in_cents'   => 120000, // KSh 1,200.00
                'duration_minutes' => 80,
                'description'      => 'Specialized solvent care for silk, chiffon, lace, and beaded designer wear.',
            ],
            [
                'name'             => 'Heavy Winter Trench Coat / Leather Jacket Care',
                'price_in_cents'   => 150000, // KSh 1,500.00
                'duration_minutes' => 90,
                'description'      => 'Deep conditioning, leather/suede treatment, and heavy stain removal.',
            ],
        ];

        Schema::disableForeignKeyConstraints();
        DB::table('services')->truncate();
        Schema::enableForeignKeyConstraints();

        foreach ($services as $serviceData) {
            $payload = [
                'name'             => $serviceData['name'],
                'price_in_cents'   => $serviceData['price_in_cents'],
                'duration_minutes' => $serviceData['duration_minutes'],
                'description'      => $serviceData['description'],
            ];

            if (Schema::hasColumn('services', 'price')) {
                $payload['price'] = $serviceData['price_in_cents'] / 100;
            }
            if (Schema::hasColumn('services', 'price_cents')) {
                $payload['price_cents'] = $serviceData['price_in_cents'];
            }
            if (Schema::hasColumn('services', 'slug')) {
                $payload['slug'] = Str::slug($serviceData['name']);
            }
            if (Schema::hasColumn('services', 'is_active')) {
                $payload['is_active'] = true;
            }

            Service::create($payload);
        }
    }
}
