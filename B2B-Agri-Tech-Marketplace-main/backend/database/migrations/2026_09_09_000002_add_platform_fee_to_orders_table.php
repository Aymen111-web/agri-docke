<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'produce_amount')) {
                $table->decimal('produce_amount', 14, 2)->default(0.00)->after('status');
            }
            if (! Schema::hasColumn('orders', 'platform_fee')) {
                $table->decimal('platform_fee', 14, 2)->default(0.00)->after('produce_amount');
            }
            if (! Schema::hasColumn('orders', 'fee_rate')) {
                $table->decimal('fee_rate', 5, 4)->default(0.0150)->after('platform_fee'); // 1.5% fee
            }
        });

        Schema::table('order_fulfillments', function (Blueprint $table) {
            if (! Schema::hasColumn('order_fulfillments', 'produce_amount')) {
                $table->decimal('produce_amount', 14, 2)->default(0.00)->after('subtotal_amount');
            }
            if (! Schema::hasColumn('order_fulfillments', 'platform_fee')) {
                $table->decimal('platform_fee', 14, 2)->default(0.00)->after('produce_amount');
            }
            if (! Schema::hasColumn('order_fulfillments', 'farmer_net_payout')) {
                $table->decimal('farmer_net_payout', 14, 2)->default(0.00)->after('platform_fee');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['produce_amount', 'platform_fee', 'fee_rate']);
        });

        Schema::table('order_fulfillments', function (Blueprint $table) {
            $table->dropColumn(['produce_amount', 'platform_fee', 'farmer_net_payout']);
        });
    }
};
