<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *  payments (id, job_id, amount_in_cents, payment_method, transaction_reference, notes, timestamps)
     * CRITICAL RULE: Immutable ledger storing every deposit and final settlement!
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->foreignId('job_id')->constrained('jobs')->restrictOnDelete(); // Associated order ticket
            $table->unsignedInteger('amount_in_cents'); // Amount paid in integer cents (e.g. KSh 500 = 50000)
            $table->string('payment_method')->default('cash'); // 'mpesa', 'cash', 'card'
            $table->string('transaction_reference')->nullable(); // M-Pesa transaction code (e.g. "QHX7829KL")
            $table->string('notes')->nullable(); // e.g. "50% upfront deposit" or "Final balance settlement"
            $table->timestamps(); // Timestamp of transaction
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
