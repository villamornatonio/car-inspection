<?php

namespace Database\Factories;

use App\Models\Car;
use Illuminate\Database\Eloquent\Factories\Factory;

class CarFactory extends Factory
{
    protected $model = Car::class;

    public function definition()
    {
        $makes = [
            'Toyota' => ['Corolla', 'Camry', 'Prius'],
            'Honda' => ['Civic', 'Accord', 'Fit'],
            'Ford' => ['Focus', 'Fiesta', 'Fusion'],
            'Chevrolet' => ['Cruze', 'Malibu', 'Spark'],
        ];
        $make = $this->faker->randomElement(array_keys($makes));
        $model = $this->faker->randomElement($makes[$make]);

        return [
            'name' => $make . ' ' . $model . ' ' . $this->faker->unique()->numberBetween(1, 9999),
            'make' => $make,
            'model' => $model,
            'year' => $this->faker->numberBetween(1990, now()->year),
        ];
    }
}
