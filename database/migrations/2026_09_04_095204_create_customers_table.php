<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('phone')->unique()->index();
                $table->string('email')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('customers', function (Blueprint $table) {
                if (!Schema::hasColumn('customers', 'phone')) {
                    $table->string('phone')->unique()->index()->after('name');
                }
                if (!Schema::hasColumn('customers', 'notes')) {
                    $table->text('notes')->nullable()->after('email');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
