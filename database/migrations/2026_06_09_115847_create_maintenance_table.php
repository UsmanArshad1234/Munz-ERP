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
        Schema::create('maintenance', function (Blueprint $table) {
            $table->id();
            $table->string('maintenance_id')->unique();       // MNT-0001
            $table->foreignId('motorbike_id')->constrained('motorbikes');
            $table->date('maintenance_date');
            $table->string('maintenance_type');              // oil_change/tire/brake/engine/accident_repair/general/other
            $table->decimal('cost', 10, 2)->default(0);
            $table->string('description')->nullable();
            $table->string('vendor_name')->nullable();
            $table->string('receipt_path')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->string('status')->default('completed'); // completed/pending/in_progress
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['motorbike_id', 'maintenance_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance');
    }
};
