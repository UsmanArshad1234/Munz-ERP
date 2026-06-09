<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL enum alter — add 'superadmin' to allowed values
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('owner', 'superadmin', 'admin') NOT NULL DEFAULT 'admin'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('owner', 'admin') NOT NULL DEFAULT 'admin'");
    }
};
