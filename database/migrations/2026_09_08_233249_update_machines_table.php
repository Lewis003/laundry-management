<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            if (!Schema::hasColumn('machines', 'type')) {
                $table->string('type')->default('washer');
            }
            if (!Schema::hasColumn('machines', 'capacity_kg')) {
                $table->decimal('capacity_kg', 5, 2)->default(15.00);
            }
            if (!Schema::hasColumn('machines', 'status')) {
                $table->string('status')->default('available');
            }
            if (!Schema::hasColumn('machines', 'last_maintenance_date')) {
                $table->date('last_maintenance_date')->nullable();
            }
            if (!Schema::hasColumn('machines', 'next_maintenance_date')) {
                $table->date('next_maintenance_date')->nullable();
            }
            if (!Schema::hasColumn('machines', 'notes')) {
                $table->text('notes')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Safe down
    }
};
