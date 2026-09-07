<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *  jobs (id, job_number, customer_id, machine_id, status, assigned_worker_id, rack_location, notes, timestamps)

     */
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->string('job_number')->unique(); // Unique ticket reference ("LND-20260904-A8F2")
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete(); // Associated customer
            $table->foreignId('machine_id')->nullable()->constrained('machines')->nullOnDelete(); // Assigned washer/dryer (null when queued or on shelf)
            $table->string('status')->default('received'); // Enum: received, in_progress, ready, picked_up, cancelled
            $table->foreignId('assigned_worker_id')->nullable()->constrained('users')->nullOnDelete(); // Staff operator
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('rack_location')->nullable(); // Numbered storage shelf (e.g. "Rack B-14")
            $table->text('notes')->nullable(); // Garment defect remarks (e.g. "Missing button on collar")
            $table->timestamps(); // Created_at and updated_at audit trail
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
