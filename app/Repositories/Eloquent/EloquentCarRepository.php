<?php

namespace App\Repositories\Eloquent;

use App\Models\Car;
use App\Repositories\CarRepository;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Eloquent implementation of the CarRepository interface.
 *
 * This repository provides concrete implementations for car data access operations
 * using Laravel's Eloquent ORM. It abstracts database interactions behind the
 * CarRepository interface, enabling easy testing and potential swapping of implementations.
 *
 * @implements CarRepository
 */
class EloquentCarRepository implements CarRepository
{
    /**
     * Construct the EloquentCarRepository with a Car model instance.
     *
     * The Car model is injected to enable Eloquent query building and database operations.
     * Using dependency injection promotes testability and loose coupling.
     *
     * @param Car $model The Car Eloquent model used for database queries
     */
    public function __construct(private Car $model)
    {
    }

    /**
     * Retrieve a paginated collection of all cars.
     *
     * Executes a paginated query against the cars table, returning a LengthAwarePaginator
     * instance that includes pagination metadata (total, per_page, current_page, etc.).
     * This method is commonly used by controllers to support browsing cars page-by-page.
     *
     * @param int $perPage The number of cars per page (default: 15)
     *
     * @return LengthAwarePaginator A paginated collection of Car models
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->query()->paginate($perPage);
    }

    /**
     * Find a single car by its primary key.
     *
     * Retrieves a car record from the database by ID. Returns null if the car
     * does not exist, allowing callers to handle missing records gracefully.
     * This method uses Eloquent's built-in find() which is optimized for single-record lookups.
     *
     * @param int $id The car's primary key ID
     *
     * @return Car|null The Car model instance if found, null otherwise
     */
    public function find(int $id): ?Car
    {
        return $this->model->find($id);
    }

    /**
     * Create and persist a new car record in the database.
     *
     * Creates a new Car instance with the provided data array and immediately
     * persists it to the database. The data array should contain only fillable
     * attributes as defined in the Car model (name, make, model, year, etc.).
     * The created model instance with assigned ID is returned.
     *
     * @param array $data Key-value pairs of car attributes (name, make, model, year)
     *
     * @return Car The newly created Car model instance with ID assigned
     */
    public function create(array $data): Car
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing car record by its ID.
     *
     * Retrieves the car by ID and updates its attributes with the provided data array.
     * Returns true if the update succeeded, false if the car was not found.
     * Only fillable attributes as defined in the Car model are updated.
     * This method uses find() internally to support graceful handling of missing records.
     *
     * @param int $id The car's primary key ID
     * @param array $data Key-value pairs of attributes to update
     *
     * @return bool True if update succeeded, false if car was not found
     */
    public function update(int $id, array $data): bool
    {
        $car = $this->find($id);

        return $car ? $car->update($data) : false;
    }

    /**
     * Delete a car record by its ID.
     *
     * Retrieves the car by ID and deletes it from the database.
     * Returns true if the deletion succeeded, false if the car was not found.
     * This method handles the case where the specified car doesn't exist gracefully.
     * Deletion may cascade to related records depending on foreign key constraints
     * (e.g., inspections may be cascade-deleted if configured).
     *
     * @param int $id The car's primary key ID
     *
     * @return bool True if deletion succeeded, false if car was not found
     */
    public function delete(int $id): bool
    {
        $car = $this->find($id);

        return $car ? $car->delete() : false;
    }
}
