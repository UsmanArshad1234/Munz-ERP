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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->unique();     // EMP-0001
            $table->string('name');
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('nationality')->nullable();
            $table->string('job_title')->nullable();     // from settings
            $table->string('department')->nullable();    // from settings
            $table->string('status')->default('active'); // from settings
            $table->string('work_emirate')->nullable();  // from settings
            $table->string('zone')->nullable();          // from settings
            $table->string('platform_name')->nullable(); // from settings
            $table->string('platform_id')->nullable();  // platform ref ID
            $table->decimal('salary_amount', 10, 2)->nullable();
            $table->string('salary_type')->nullable();  // from settings
            $table->string('wps_status')->default('no_wps'); // wps / no_wps
            $table->string('passport_number')->nullable();
            $table->date('passport_expiry')->nullable();
            $table->string('emirates_id')->nullable();
            $table->date('emirates_id_expiry')->nullable();
            $table->date('visa_expiry')->nullable();
            $table->date('labour_card_expiry')->nullable();
            $table->string('driving_license')->nullable();
            $table->date('driving_license_expiry')->nullable();
            $table->unsignedBigInteger('assigned_bike_id')->nullable(); // FK added in bikes migration
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('work_emirate');
            $table->index('platform_name');
            $table->index('wps_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
