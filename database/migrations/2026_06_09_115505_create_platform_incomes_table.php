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
        Schema::create('platform_incomes', function (Blueprint $table) {
            $table->id();
            $table->string('income_id')->unique();            // INC-0001
            $table->date('income_date');
            $table->string('source_type');                   // platform / rider
            $table->string('platform_name')->nullable();     // Talabat, Careem, Noon, Other (for source_type=platform)
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete(); // for source_type=rider
            $table->decimal('amount', 10, 2);
            $table->string('description')->nullable();
            $table->string('receipt_path')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['income_date']);
            $table->index(['source_type', 'income_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_incomes');
    }
};
