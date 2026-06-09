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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->string('payroll_id')->unique();               // PAY-0001
            $table->foreignId('employee_id')->constrained('employees');
            $table->unsignedTinyInteger('month');                // 1-12
            $table->unsignedSmallInteger('year');
            $table->string('salary_type')->default('monthly');  // monthly/daily

            // Earnings
            $table->decimal('gross_salary', 10, 2);

            // Deductions
            $table->decimal('loan_deduction', 10, 2)->default(0);
            $table->decimal('fine_deduction', 10, 2)->default(0);
            $table->decimal('salik_deduction', 10, 2)->default(0);
            $table->decimal('penalty_deduction', 10, 2)->default(0);
            $table->decimal('other_deduction', 10, 2)->default(0);
            $table->decimal('total_deductions', 10, 2)->default(0);

            // Net
            $table->decimal('net_salary', 10, 2);

            // Attendance & Compliance
            $table->unsignedTinyInteger('attendance_days')->nullable();
            $table->unsignedTinyInteger('hours_compliance')->nullable();

            // Status & Approval
            $table->string('payment_status')->default('unpaid'); // unpaid/paid
            $table->string('payroll_status')->default('draft'); // draft/approved/rejected
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['employee_id', 'month', 'year']);
            $table->index(['month', 'year']);
            $table->index('payroll_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
