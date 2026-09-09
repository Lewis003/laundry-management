<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category'); // supplies, utilities, maintenance, rent, wages, other
            $table->unsignedBigInteger('amount_in_cents'); // e.g. 450000 = KSh 4,500.00
            $table->date('expense_date');
            $table->string('payment_method')->default('mpesa'); // mpesa, bank_transfer, cash
            $table->string('reference_code')->nullable(); // M-Pesa receipt / invoice #
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
