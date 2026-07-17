<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_login_issues_token(): void
    {
        $user = User::create(['name' => 'T', 'email' => 'a@b.com', 'password' => Hash::make('password')]);

        $res = $this->postJson('/api/v1/auth/token', ['email' => 'a@b.com', 'password' => 'password']);
        $res->assertStatus(200)->assertJson(static fn (AssertableJson $json) => $json->where('success', true)->etc()->has('data.token'));
    }

    public function test_invalid_credentials_returns_401(): void
    {
        $res = $this->postJson('/api/v1/auth/token', ['email' => 'no@one', 'password' => 'wrong']);
        $res->assertStatus(401);
    }
}
