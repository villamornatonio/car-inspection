<?php

namespace Tests\Feature;

use App\Jobs\CreateCarJob;
use App\Models\User;
use App\Services\CarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Mockery;
use RuntimeException;
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

    public function test_store_returns_422_when_required_fields_are_missing(): void
    {
        Bus::fake();
        $user = User::create(['name' => 'T', 'email' => 'a@b.com', 'password' => Hash::make('password')]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/cars', []);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'errors'])
            ->assertJsonValidationErrors(['name', 'make', 'model', 'year']);

        Bus::assertNothingDispatched();
    }

    public function test_store_returns_500_when_service_fails(): void
    {
        Bus::fake();
        $user = User::create(['name' => 'T', 'email' => 'a@b.com', 'password' => Hash::make('password')]);
        Sanctum::actingAs($user);

        $mock = Mockery::mock(CarService::class);
        $mock->shouldReceive('createAsync')
            ->once()
            ->andThrow(new RuntimeException('Queue unavailable'));
        $this->app->instance(CarService::class, $mock);

        $response = $this->postJson('/api/v1/cars', [
            'name' => 'Test Car',
            'make' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2020,
        ]);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'Failed to create car',
                'errors' => [],
            ]);

        Bus::assertNothingDispatched();
    }
}
