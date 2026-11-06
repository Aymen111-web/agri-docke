<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        User::create([
            'first_name'        => 'Awol',
            'second_name'       => 'Buyer',
            'phone'             => '+251918982161',
            'password'          => Hash::make('123456'),
            'account_status'    => 'active',
            'phone_verified_at' => now(),
        ]);
    }

    public function test_login_with_international_phone_format()
    {
        $response = $this->postJson('/api/auth/login', [
            'phone'    => '+251918982161',
            'password' => '123456',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'user', 'token']);
    }

    public function test_login_with_local_phone_format()
    {
        $response = $this->postJson('/api/auth/login', [
            'phone'    => '0918982161',
            'password' => '123456',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'user', 'token']);
    }

    public function test_login_with_raw_nine_digit_phone_format()
    {
        $response = $this->postJson('/api/auth/login', [
            'phone'    => '918982161',
            'password' => '123456',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'user', 'token']);
    }

    public function test_login_with_wrong_password_fails()
    {
        $response = $this->postJson('/api/auth/login', [
            'phone'    => '0918982161',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson(['error' => 'Invalid credentials.']);
    }
}

