<?php

namespace App\Repositories;

use App\Models\Car;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * CarRepository interface.
 *
 * Defines the contract for car data access operations. This interface abstracts
 * the data persistence layer from business logic, enabling easy testing through
 * mock implementations and allowing swapping of data persistence strategies
 * (e.g., from Eloquent to a different ORM or database system).
 *
 * Implementations should handle all CRUD operations for Car entities and return
 * appropriate types as specified in each method signature.
 */
interface CarRepository
{
    /**
     * Retrieve a paginated collection of all cars from the data store.
     *
     * Returns a LengthAwarePaginator instance containing cars paginated according to
     * the specified page size. The paginator includes metadata such as total count,
     * current page, and last page for use in pagination UI components.
     * Implementations should support standard pagination parameters.
     *
     * @param int $perPage The number of cars to return per page (default: 15)
     *
     * @return LengthAwarePaginator A paginated collection of Car instances
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Find a single car by its primary key identifier.
     *
     * Retrieves a specific car record by ID from the data store.
     * Returns the Car instance if found, or null if the car does not exist.
     * Implementations should handle missing records gracefully without throwing exceptions.
     *
     * @param int $id The car's unique identifier (primary key)
     *
     * @return Car|null The Car instance if found, null if not found
     */
    public function find(int $id): ?Car;

    /**
     * Create and persist a new car record to the data store.
     *
     * Accepts an array of car attributes and creates a new Car instance that is
     * immediately persisted to the database. The newly created instance with an
     * assigned ID is returned. Implementations should only accept fillable attributes
     * as defined by the data model.
     *
     * @param array $data Key-value pairs of car attributes (name, make, model, year, etc.)
     *
     * @return Car The newly created Car instance with ID assigned
     */
    public function create(array $data): Car;

    /**
     * Update an existing car record in the data store.
     *
     * Retrieves the car by ID and updates its attributes with the provided data array.
     * Returns true if the update was successful, false if the car was not found.
     * Implementations should handle missing cars gracefully by returning false rather
     * than throwing exceptions. Only fillable attributes should be updated.
     *
     * @param int $id The car's unique identifier (primary key)
     * @param array $data Key-value pairs of attributes to update
     *
     * @return bool True if the update succeeded, false if car was not found
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a car record from the data store.
     *
     * Removes a car record identified by the given ID. Returns true if the deletion
     * was successful, false if the car was not found. Implementations should handle
     * missing cars gracefully without throwing exceptions. Related records may be
     * cascade-deleted depending on foreign key constraints.
     *
     * @param int $id The car's unique identifier (primary key)
     *
     * @return bool True if deletion succeeded, false if car was not found
     */
    public function delete(int $id): bool;
}
