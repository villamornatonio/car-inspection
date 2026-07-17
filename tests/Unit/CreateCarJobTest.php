<?php

namespace Tests\Unit;

use App\Jobs\CreateCarJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateCarJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_creates_car(): void
    {
        $payload = ['name' => 'X','make' => 'Y','model' => 'Z','year' => 2000];
        $job = new CreateCarJob($payload, 'tracking-123');
        $job->handle();

        $this->assertDatabaseHas('cars', ['name' => 'X', 'make' => 'Y', 'model' => 'Z']);
    }
}
