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

    public function test_index_requires_auth(): void
    {
        $this->getJson('/api/v1/inspections')->assertStatus(401);
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
