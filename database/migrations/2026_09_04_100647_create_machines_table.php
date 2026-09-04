<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * (id, name, is_available, is_active, timestamps)
     */
    public function up(): void
    {
        Schema::create('machines', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->string('name')->unique(); // Machine name (e.g. "Commercial Washer 1 - 15kg")
            $table->boolean('is_available')->default(true); // Concurrency capacity flag (true = empty, false = washing)
            $table->boolean('is_active')->default(true); // Maintenance toggle (true = active, false = offline)
            $table->timestamps(); // Created_at and updated_at audit trail
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machines');
    }
};
