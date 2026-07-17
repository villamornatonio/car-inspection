<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InspectionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_inspections_without_auth_returns_401(): void
    {
        $response = $this->getJson('/api/v1/inspections');

        $response->assertStatus(401)
            ->assertJsonStructure(['message']);
    }

    public function test_get_inspections_with_auth_returns_200(): void
    {
        $user = User::create(['name' => 'T', 'email' => 'a@b.com', 'password' => Hash::make('password')]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/inspections');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_post_inspection_without_auth_returns_401(): void
    {
        $response = $this->postJson('/api/v1/inspections', [
            'carId'       => 1,
            'wipers'      => true,
            'engineSound' => true,
            'headlights'  => true,
        ]);

        $response->assertStatus(401)
            ->assertJsonStructure(['message']);
    }

    public function test_store_creates_inspection(): void
    {
        $user = User::create(['name' => 'T','email' => 'a@b.com','password' => Hash::make('password')]);
        Sanctum::actingAs($user);

        $car = Car::factory()->create();
        $payload = ['carId' => $car->id, 'wipers' => true, 'engineSound' => true, 'headlights' => true];
        $res = $this->postJson('/api/v1/inspections', $payload);
        $res->assertStatus(201);
    }
}
