<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Car model representing a vehicle in the system.
 *
 * This Eloquent model represents a car entity persisted in the cars table.
 * Each car has basic properties (name, make, model, year) and one-to-many
 * relationships with inspections. The model supports factory-based testing
 * and follows Laravel conventions for timestamps and fillable attributes.
 *
 * @property int $id The car's unique identifier (primary key)
 * @property string $name The car's display name or custom identifier
 * @property string $make The vehicle manufacturer (e.g., 'Toyota', 'Ford', 'Tesla')
 * @property string $model The vehicle model (e.g., 'Camry', 'Mustang', 'Model S')
 * @property int $year The model year of the vehicle
 * @property \Illuminate\Support\Carbon $created_at Timestamp when the car was created
 * @property \Illuminate\Support\Carbon $updated_at Timestamp when the car was last updated
 */
class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'make', 'model', 'year',
    ];

    /**
     * Get all inspections for this car.
     *
     * Defines a one-to-many relationship where a car has many inspections.
     * This method retrieves all inspection records associated with this car.
     * The relationship is typically used to display inspection history or
     * filter inspections by vehicle.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany The inspections relationship
     */
    public function inspections(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Inspection::class);
    }
}
