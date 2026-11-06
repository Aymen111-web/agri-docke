<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_fulfillments', function (Blueprint $table) {
            if (! Schema::hasColumn('order_fulfillments', 'delivery_status')) {
                $table->string('delivery_status', 30)->default('pending')->after('status');
            }
            if (! Schema::hasColumn('order_fulfillments', 'inspection_status')) {
                $table->string('inspection_status', 30)->default('pending')->after('delivery_status');
            }
            if (! Schema::hasColumn('order_fulfillments', 'payout_status')) {
                $table->string('payout_status', 30)->default('pending')->after('inspection_status');
            }
            if (! Schema::hasColumn('order_fulfillments', 'accepted_quantity')) {
                $table->decimal('accepted_quantity', 12, 3)->nullable()->after('subtotal_amount');
            }
            if (! Schema::hasColumn('order_fulfillments', 'rejected_quantity')) {
                $table->decimal('rejected_quantity', 12, 3)->nullable()->after('accepted_quantity');
            }
            if (! Schema::hasColumn('order_fulfillments', 'inspection_notes')) {
                $table->text('inspection_notes')->nullable()->after('rejected_quantity');
            }
            if (! Schema::hasColumn('order_fulfillments', 'inspected_at')) {
                $table->timestamp('inspected_at')->nullable()->after('inspection_notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_fulfillments', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_status',
                'inspection_status',
                'payout_status',
                'accepted_quantity',
                'rejected_quantity',
                'inspection_notes',
                'inspected_at',
            ]);
        });
    }
};
