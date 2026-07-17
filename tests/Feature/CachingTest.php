<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CachingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Prevent stale in-memory cache keys from previous tests affecting assertions.
        Cache::flush();
    }

    public function test_1_cars_list_response_is_cached(): void
    {
        $user = User::create(['name' => 'T', 'email' => 'a@b.com', 'password' => Hash::make('password')]);
        Sanctum::actingAs($user);

        // Create some cars
        Car::factory(3)->create();

        // First request - populates cache
        $response1 = $this->getJson('/api/v1/cars');
        $response1->assertStatus(200);
        $data1 = $response1->json();

        // Second request - should return exact same cached data
        $response2 = $this->getJson('/api/v1/cars');
        $response2->assertStatus(200);
        $data2 = $response2->json();

        // Verify responses are identical (cache hit)
        $this->assertEquals($data1, $data2);
    }

    public function test_2_inspections_list_response_is_cached(): void
    {
        $user = User::create(['name' => 'T', 'email' => 'a@b.com', 'password' => Hash::make('password')]);
        Sanctum::actingAs($user);

        $car = Car::factory()->create();

        // Create some inspections
        $this->postJson('/api/v1/inspections', [
            'carId' => $car->id,
            'wipers' => true,
            'engineSound' => true,
            'headlights' => true,
        ])->assertStatus(201);

        // First request - populates cache
        $response1 = $this->getJson('/api/v1/inspections');
        $response1->assertStatus(200);
        $data1 = $response1->json();

        // Second request - should return cached data
        $response2 = $this->getJson('/api/v1/inspections');
        $response2->assertStatus(200);
        $data2 = $response2->json();

        // Verify responses are identical (cache hit)
        $this->assertEquals($data1, $data2);
    }

    public function test_3_cache_keys_differ_by_car_filter(): void
    {
        $user = User::create(['name' => 'T', 'email' => 'a@b.com', 'password' => Hash::make('password')]);
        Sanctum::actingAs($user);

        $car1 = Car::factory()->create();
        $car2 = Car::factory()->create();

        // Create inspection for car1
        $createResponse = $this->postJson('/api/v1/inspections', [
            'carId' => $car1->id,
            'wipers' => true,
            'engineSound' => true,
            'headlights' => true,
        ]);
        $createResponse->assertStatus(201);

        // Verify the inspection was actually created in DB
        $this->assertDatabaseHas('inspections', [
            'car_id' => $car1->id,
        ]);

        // Get all inspections (no filter)
        $responseAll = $this->getJson('/api/v1/inspections');
        $responseAll->assertStatus(200);
        $allInspections = $responseAll->json('data');
        $this->assertCount(1, $allInspections, 'Expected 1 total inspection');

        // Get inspections for car1 only
        $response1 = $this->getJson('/api/v1/inspections?carId=' . $car1->id);
        $response1->assertStatus(200);
        $car1Inspections = $response1->json('data');

        // Get inspections for car2 (should be empty - different cache key)
        $response2 = $this->getJson('/api/v1/inspections?carId=' . $car2->id);
        $response2->assertStatus(200);
        $car2Inspections = $response2->json('data');

        // Verify different cache keys return different results
        $this->assertCount(1, $car1Inspections, 'Expected 1 inspection for car1 with ID ' . $car1->id);
        $this->assertCount(0, $car2Inspections, 'Expected 0 inspections for car2 with ID ' . $car2->id);
    }

    public function test_4_cars_cache_is_forgotten_on_store(): void
    {
        $user = User::create(['name' => 'T', 'email' => 'a@b.com', 'password' => Hash::make('password')]);
        Sanctum::actingAs($user);

        // Create initial cars
        Car::factory(2)->create();

        // Get initial list (populates cache)
        $response1 = $this->getJson('/api/v1/cars');
        $response1->assertStatus(200);
        $count1 = count($response1->json('data'));

        // Verify cache key exists
        $this->assertNotNull(Cache::get('cars_paginated'));

        // Create car via API (should forget cache)
        $this->postJson('/api/v1/cars', [
            'name' => 'New Car',
            'make' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2023,
        ])->assertStatus(202);

        // Verify cache key was forgotten
        $this->assertNull(Cache::get('cars_paginated'));
    }

    public function test_5_inspections_cache_is_forgotten_on_store(): void
    {
        $user = User::create(['name' => 'T', 'email' => 'a@b.com', 'password' => Hash::make('password')]);
        Sanctum::actingAs($user);

        $car = Car::factory()->create();

        // Create initial inspection
        $this->postJson('/api/v1/inspections', [
            'carId' => $car->id,
            'wipers' => true,
            'engineSound' => true,
            'headlights' => true,
        ])->assertStatus(201);

        // Get inspections list (populates cache)
        $response1 = $this->getJson('/api/v1/inspections');
        $response1->assertStatus(200);

        // Verify cache key exists
        $this->assertNotNull(Cache::get('inspections_all'));

        // Create another inspection (should forget cache)
        $this->postJson('/api/v1/inspections', [
            'carId' => $car->id,
            'wipers' => true,
            'engineSound' => true,
            'headlights' => true,
        ])->assertStatus(201);

        // Verify cache key was forgotten
        $this->assertNull(Cache::get('inspections_all'));
    }
}
