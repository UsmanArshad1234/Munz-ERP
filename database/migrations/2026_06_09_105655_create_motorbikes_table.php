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
        Schema::create('motorbikes', function (Blueprint $table) {
            $table->id();
            $table->string('bike_id')->unique();           // BK-0001
            $table->string('plate_number');
            $table->string('plate_code')->nullable();
            $table->string('emirate')->nullable();         // registration emirate
            $table->string('zone')->nullable();
            $table->string('bike_type')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->smallInteger('year')->nullable();
            $table->string('color')->nullable();
            $table->string('chassis_number')->nullable();
            $table->string('engine_number')->nullable();
            $table->string('insurance_company')->nullable();
            $table->date('insurance_expiry')->nullable();
            $table->date('mulkiya_expiry')->nullable();
            $table->string('status')->default('available'); // from settings
            $table->unsignedBigInteger('current_rider_id')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('emirate');
            $table->index('insurance_expiry');
            $table->index('mulkiya_expiry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('motorbikes');
    }
};
