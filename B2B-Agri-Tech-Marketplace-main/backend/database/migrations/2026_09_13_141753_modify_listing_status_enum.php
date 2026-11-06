<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE listings MODIFY COLUMN status ENUM('active', 'inactive', 'sold_out', 'suspended') DEFAULT 'active'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE listings MODIFY COLUMN status ENUM('active', 'inactive', 'sold_out') DEFAULT 'active'");
    }
};
