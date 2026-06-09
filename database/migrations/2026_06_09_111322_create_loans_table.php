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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('loan_id')->unique();             // LN-0001
            $table->foreignId('employee_id')->constrained('employees');
            $table->date('loan_date');
            $table->decimal('loan_amount', 10, 2);          // original amount
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('remaining_balance', 10, 2);    // auto-calculated
            $table->decimal('monthly_deduction', 10, 2)->nullable();
            $table->unsignedSmallInteger('number_of_installments')->nullable();
            $table->unsignedSmallInteger('remaining_installments')->nullable();
            $table->string('status')->default('active');    // active/paid/cancelled/on_hold
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['employee_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
