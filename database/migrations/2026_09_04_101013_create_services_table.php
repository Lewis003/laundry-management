<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * services (id, name, price_in_cents, duration_minutes, description, timestamps)
     */
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->string('name'); // Service name (e.g. "Shirt - Wash & Steam Iron", "Suit - Dry Clean")
            $table->unsignedInteger('price_in_cents'); // Base catalog price in integer cents (e.g. KSh 100 = 10000)
            $table->integer('duration_minutes')->default(60); // Estimated service turnaround time
            $table->text('description')->nullable(); // Optional details / fabric care instructions
            $table->timestamps(); // Created_at and updated_at audit trail
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
