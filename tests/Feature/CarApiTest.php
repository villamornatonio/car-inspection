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

    public function test_index_requires_auth(): void
    {
        $this->getJson('/api/v1/cars')->assertStatus(401);
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
