<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Safety: add wps_status if missing on production DB
            if (!Schema::hasColumn('employees', 'wps_status')) {
                $table->string('wps_status')->default('no_wps')->after('salary_type');
                $table->index('wps_status');
            }

            $table->string('passport_document')->nullable()->after('passport_expiry');
            $table->string('visa_document')->nullable()->after('visa_expiry');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['passport_document', 'visa_document']);
        });
    }
};
