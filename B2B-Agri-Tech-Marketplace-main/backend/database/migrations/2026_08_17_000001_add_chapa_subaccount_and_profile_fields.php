<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add subaccount, bank, photo fields to users
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'chapa_subaccount_id')) {
                $table->string('chapa_subaccount_id')->nullable()->after('password')->index();
            }
            if (! Schema::hasColumn('users', 'bank_code')) {
                $table->string('bank_code')->nullable()->after('chapa_subaccount_id');
            }
            if (! Schema::hasColumn('users', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('bank_code');
            }
            if (! Schema::hasColumn('users', 'account_number')) {
                $table->string('account_number')->nullable()->after('bank_name');
            }
            if (! Schema::hasColumn('users', 'account_name')) {
                $table->string('account_name')->nullable()->after('account_number');
            }
            if (! Schema::hasColumn('users', 'profile_photo_path')) {
                $table->string('profile_photo_path')->nullable()->after('account_name');
            }
            if (! Schema::hasColumn('users', 'account_status')) {
                $table->string('account_status')->default('active')->after('is_admin');
            }
        });

        // 2. Add order_fulfillment_id to payments table
        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'order_fulfillment_id')) {
                $table->foreignId('order_fulfillment_id')
                    ->nullable()
                    ->after('order_id')
                    ->constrained('order_fulfillments')
                    ->cascadeOnDelete();
            }
        });

        // Make order_id on payments nullable or drop unique index if it exists, to support multi-fulfillment payments
        try {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropUnique('payments_order_id_unique');
            });
        } catch (\Exception $e) {
            // Index might not exist or already dropped
        }

        // 3. Modify order_fulfillments status to include 'buyer_received'
        try {
            DB::statement("ALTER TABLE order_fulfillments MODIFY COLUMN status ENUM('pending', 'accepted', 'buyer_received', 'rejected', 'completed', 'cancelled') DEFAULT 'pending'");
        } catch (\Exception $e) {
            // Fallback or SQLite
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = ['chapa_subaccount_id', 'bank_code', 'bank_name', 'account_number', 'account_name', 'profile_photo_path'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'order_fulfillment_id')) {
                $table->dropForeign(['order_fulfillment_id']);
                $table->dropColumn('order_fulfillment_id');
            }
        });
    }
};
