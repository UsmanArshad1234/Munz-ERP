<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->string('assignment_id')->unique();     // ASN-0001
            $table->foreignId('employee_id')->constrained('employees');
            $table->foreignId('motorbike_id')->constrained('motorbikes');
            $table->date('start_date');
            $table->date('return_date')->nullable();
            $table->string('handover_condition')->nullable(); // Good, Fair, Poor
            $table->string('return_condition')->nullable();
            $table->string('status')->default('active');  // active, returned, pending_return, cancelled
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index(['employee_id', 'status']);
            $table->index(['motorbike_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
