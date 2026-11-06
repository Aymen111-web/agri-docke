<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            if (! Schema::hasColumn('listings', 'batch_number')) {
                $table->string('batch_number', 50)->nullable()->after('id');
            }
            if (! Schema::hasColumn('listings', 'harvest_date')) {
                $table->date('harvest_date')->nullable()->after('status');
            }
            if (! Schema::hasColumn('listings', 'quality_grade')) {
                $table->string('quality_grade', 50)->nullable()->after('harvest_date');
            }
            if (! Schema::hasColumn('listings', 'minimum_order_quantity')) {
                $table->decimal('minimum_order_quantity', 12, 3)->default(1.000)->after('quality_grade');
            }
            if (! Schema::hasColumn('listings', 'price_valid_from')) {
                $table->timestamp('price_valid_from')->nullable()->after('minimum_order_quantity');
            }
            if (! Schema::hasColumn('listings', 'price_valid_until')) {
                $table->timestamp('price_valid_until')->nullable()->after('price_valid_from');
            }
            if (! Schema::hasColumn('listings', 'reference_market_price')) {
                $table->decimal('reference_market_price', 14, 2)->nullable()->after('price_valid_until');
            }
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn([
                'batch_number',
                'harvest_date',
                'quality_grade',
                'minimum_order_quantity',
                'price_valid_from',
                'price_valid_until',
                'reference_market_price',
            ]);
        });
    }
};
