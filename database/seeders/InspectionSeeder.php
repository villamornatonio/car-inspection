<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\Inspection;
use Illuminate\Database\Seeder;

class InspectionSeeder extends Seeder
{
    public function run(): void
    {
        Car::all()->each(static function (Car $car): void {
            $count = mt_rand(1, 4);
            Inspection::factory()->count($count)->create(['car_id' => $car->id]);
        });
    }
}
