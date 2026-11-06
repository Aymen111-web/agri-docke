<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('second_name');

            // Phone is the primary identity/login credential (doc: "Phone-based
            // registration and OTP login").
            $table->string('phone', 13)->unique();
            $table->timestamp('phone_verified_at')->nullable();

            // Nullable: primary auth is phone + OTP, not password-based.
            $table->string('password')->nullable();

            // Admin is a privilege that exists SEPARATELY from the farmer/buyer
            // capability system (see user_capabilities). It is not "applied for".
            $table->boolean('is_admin')->default(false);

            $table->enum('account_status', ['active', 'suspended'])->default('active');

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index('account_status');
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};

