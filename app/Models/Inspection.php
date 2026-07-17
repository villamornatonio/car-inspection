<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Inspection model representing a vehicle inspection record.
 *
 * This Eloquent model represents an inspection entity persisted in the inspections table.
 * Each inspection records the condition of specific vehicle components (wipers, engine sound,
 * headlights) at a point in time. Inspections belong to a single car and support factory-based
 * testing. Boolean attributes and performed_at timestamps are automatically cast to appropriate types.
 *
 * @property int $id The inspection's unique identifier (primary key)
 * @property int $car_id The ID of the car being inspected (foreign key)
 * @property bool $wipers Whether the windshield wipers are in good condition
 * @property bool $engine_sound Whether the engine sounds normal
 * @property bool $headlights Whether the headlights are functioning
 * @property \Illuminate\Support\Carbon|null $performed_at The timestamp when the inspection was performed (nullable)
 * @property \Illuminate\Support\Carbon $created_at Timestamp when the inspection record was created
 * @property \Illuminate\Support\Carbon $updated_at Timestamp when the inspection record was last updated
 * @property Car|null $car The associated Car model
 */
class Inspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id', 'wipers', 'engine_sound', 'headlights', 'performed_at',
    ];

    protected $casts = [
        'wipers' => 'boolean',
        'engine_sound' => 'boolean',
        'headlights' => 'boolean',
        'performed_at' => 'datetime',
    ];

    /**
     * Get the car that this inspection belongs to.
     *
     * Defines a many-to-one relationship where an inspection belongs to exactly one car.
     * This method retrieves the associated Car model. Accessing this relationship after
     * eager-loading (via ->with('car')) prevents additional database queries (N+1 prevention).
     * If the car is deleted with cascade delete configured, the inspection is also deleted.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo The car relationship
     */
    public function car(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Car::class);
    }
}
