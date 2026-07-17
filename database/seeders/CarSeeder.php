<?php

namespace Database\Seeders;

use App\Models\Car;
use Illuminate\Database\Seeder;

class CarSeeder extends Seeder
{
    public function run(): void
    {
        // 3 fixed
        Car::factory()->create(['make' => 'Toyota', 'model' => 'Corolla', 'name' => 'Toyota Corolla Demo 1']);
        Car::factory()->create(['make' => 'Honda', 'model' => 'Civic', 'name' => 'Honda Civic Demo 2']);
        Car::factory()->create(['make' => 'Ford', 'model' => 'Fusion', 'name' => 'Ford Fusion Demo 3']);

        // 12 random
        Car::factory()->count(12)->create();
    }
}
