<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payment_exceptions') && ! Schema::hasColumn('payment_exceptions', 'farmer_response')) {
            Schema::table('payment_exceptions', function (Blueprint $table) {
                $table->text('farmer_response')->nullable()->after('description');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payment_exceptions') && Schema::hasColumn('payment_exceptions', 'farmer_response')) {
            Schema::table('payment_exceptions', function (Blueprint $table) {
                $table->dropColumn('farmer_response');
            });
        }
    }
};
