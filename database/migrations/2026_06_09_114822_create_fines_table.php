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
        Schema::create('fines', function (Blueprint $table) {
            $table->id();
            $table->string('fine_id')->unique();             // FN-0001
            $table->foreignId('employee_id')->constrained('employees');
            $table->date('fine_date');
            $table->string('fine_type');                    // salik/traffic_fine/company_penalty/other
            $table->decimal('amount', 10, 2);
            $table->string('description')->nullable();
            $table->string('receipt_path')->nullable();
            $table->foreignId('payroll_id')->nullable()->constrained('payrolls')->nullOnDelete();
            $table->string('status')->default('pending'); // pending/deducted/waived
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['employee_id', 'status']);
            $table->index(['fine_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fines');
    }
};
