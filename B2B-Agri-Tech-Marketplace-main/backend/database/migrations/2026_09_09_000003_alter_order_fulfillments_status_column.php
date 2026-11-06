<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_fulfillments', function (Blueprint $table) {
            $table->string('status', 30)->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('order_fulfillments', function (Blueprint $table) {
            $table->enum('status', ['pending', 'accepted', 'paid_in_escrow', 'dispatched', 'in_transit', 'buyer_received', 'rejected', 'completed', 'cancelled'])
                ->default('pending')
                ->change();
        });
    }
};
