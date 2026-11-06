<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            if (! Schema::hasColumn('listings', 'region')) {
                $table->string('region', 100)->nullable()->after('quality_grade');
            }
            if (! Schema::hasColumn('listings', 'zone')) {
                $table->string('zone', 100)->nullable()->after('region');
            }
            if (! Schema::hasColumn('listings', 'process')) {
                $table->string('process', 100)->nullable()->after('zone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn(['region', 'zone', 'process']);
        });
    }
};
