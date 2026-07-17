<?php

namespace Tests\Feature;

use App\Jobs\CreateCarJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CarApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_cars_without_auth_returns_401(): void
    {
        $response = $this->getJson('/api/v1/cars');

        $response->assertStatus(401)
                 ->assertJsonStructure(['message']);
    }

    public function test_get_cars_with_auth_returns_200(): void
    {
        $user = User::create(['name' => 'T', 'email' => 'a@b.com', 'password' => Hash::make('password')]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/cars');

        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_post_car_without_auth_returns_401(): void
    {
        $response = $this->postJson('/api/v1/cars', [
            'name'  => 'Test Car',
            'make'  => 'Toyota',
            'model' => 'Corolla',
            'year'  => 2023,
        ]);

        $response->assertStatus(401)
                 ->assertJsonStructure(['message']);
    }

    public function test_store_dispatches_job(): void
    {
        Bus::fake();
        $user = User::create(['name' => 'T','email' => 'a@b.com','password' => Hash::make('password')]);
        Sanctum::actingAs($user);

        $payload = ['name' => 'X','make' => 'Toyota','model' => 'Corolla','year' => 2010];
        $res = $this->postJson('/api/v1/cars', $payload);
        $res->assertStatus(202);

        Bus::assertDispatched(CreateCarJob::class);
    }
}
