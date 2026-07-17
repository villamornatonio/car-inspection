<?php

namespace Database\Factories;

use App\Models\Inspection;
use Illuminate\Database\Eloquent\Factories\Factory;

class InspectionFactory extends Factory
{
    protected $model = Inspection::class;

    public function definition()
    {
        return [
            'wipers' => $this->faker->boolean(80),
            'engine_sound' => $this->faker->boolean(90),
            'headlights' => $this->faker->boolean(95),
            'performed_at' => $this->faker->optional()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
