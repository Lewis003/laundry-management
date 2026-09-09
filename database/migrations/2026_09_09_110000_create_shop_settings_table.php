<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('shop_settings')) {
            Schema::create('shop_settings', function (Blueprint $table) {
                $table->id();
                $table->string('shop_name')->default('Safishwa na Tai');
                $table->string('tagline')->default('Commercial Laundry & Dry Cleaning');
                $table->string('location')->default('Industrial Area, Nairobi');
                $table->string('phone')->default('+254 700 000 001');
                $table->string('email')->default('info@safishwa.co.ke');
                $table->string('mpesa_till_or_paybill')->default('542310');
                $table->text('receipt_footer')->nullable();
                $table->string('currency')->default('KSh');
                $table->decimal('tax_percent', 5, 2)->default(16.00);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_settings');
    }
};

