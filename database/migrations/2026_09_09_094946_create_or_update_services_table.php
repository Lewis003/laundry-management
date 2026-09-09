<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('services')) {
            Schema::create('services', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->unsignedInteger('price_cents'); // Pure integer cents (e.g. 85000 = KSh 850.00)
                $table->string('category')->default('wash_fold'); // wash_fold, dry_clean, duvets, ironing
                $table->boolean('is_active')->default(true);
                $table->text('description')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('services', function (Blueprint $table) {
                if (!Schema::hasColumn('services', 'price_cents')) {
                    $table->unsignedInteger('price_cents')->default(35000)->after('name');
                }
                if (!Schema::hasColumn('services', 'category')) {
                    $table->string('category')->default('wash_fold')->after('price_cents');
                }
                if (!Schema::hasColumn('services', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('category');
                }
            });
        }
    }

    public function down(): void
    {
        // Safe down
    }
};
