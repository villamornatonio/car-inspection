<?php

namespace App\Repositories;

use App\Models\Inspection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * InspectionRepository interface.
 *
 * Defines the contract for inspection data access operations. This interface abstracts
 * the data persistence layer from business logic, enabling easy testing through mock
 * implementations and allowing swapping of data persistence strategies. Inspections
 * can be filtered by car ID and support pagination for browsing large result sets.
 *
 * Implementations should handle all CRUD operations for Inspection entities and return
 * appropriate types as specified in each method signature. Eager-loading related data
 * (such as car information) is recommended to prevent N+1 query problems.
 */
interface InspectionRepository
{
    /**
     * Retrieve a paginated collection of inspections with optional car filtering.
     *
     * Returns a LengthAwarePaginator instance containing inspections paginated according
     * to the specified page size. Can optionally filter results to only include inspections
     * for a specific car ID. The paginator includes metadata for pagination UI components.
     * Implementations should eager-load related car data to prevent N+1 query problems.
     *
     * @param int|null $carId Optional car ID to filter inspections; null returns all inspections
     * @param int $perPage The number of inspections to return per page (default: 15)
     *
     * @return LengthAwarePaginator A paginated collection of Inspection instances, optionally filtered by car
     */
    public function paginate(?int $carId = null, int $perPage = 15): LengthAwarePaginator;

    /**
     * Find a single inspection by its primary key identifier.
     *
     * Retrieves a specific inspection record by ID from the data store.
     * Returns the Inspection instance if found, or null if the inspection does not exist.
     * Implementations should handle missing records gracefully without throwing exceptions.
     * Implementations should eager-load related car data when possible.
     *
     * @param int $id The inspection's unique identifier (primary key)
     *
     * @return Inspection|null The Inspection instance if found, null if not found
     */
    public function find(int $id): ?Inspection;

    /**
     * Create and persist a new inspection record to the data store.
     *
     * Accepts an array of inspection attributes and creates a new Inspection instance
     * that is immediately persisted to the database. The newly created instance with an
     * assigned ID is returned. Implementations should only accept fillable attributes
     * as defined by the data model.
     *
     * @param array $data Key-value pairs of inspection attributes (car_id, wipers, engine_sound, headlights, performed_at, etc.)
     *
     * @return Inspection The newly created Inspection instance with ID assigned
     */
    public function create(array $data): Inspection;

    /**
     * Update an existing inspection record in the data store.
     *
     * Retrieves the inspection by ID and updates its attributes with the provided data
     * array. Returns true if the update was successful, false if the inspection was not
     * found. Implementations should handle missing inspections gracefully by returning
     * false rather than throwing exceptions. Only fillable attributes should be updated.
     *
     * @param int $id The inspection's unique identifier (primary key)
     * @param array $data Key-value pairs of attributes to update
     *
     * @return bool True if the update succeeded, false if inspection was not found
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete an inspection record from the data store.
     *
     * Removes an inspection record identified by the given ID. Returns true if the deletion
     * was successful, false if the inspection was not found. Implementations should handle
     * missing inspections gracefully without throwing exceptions.
     *
     * @param int $id The inspection's unique identifier (primary key)
     *
     * @return bool True if deletion succeeded, false if inspection was not found
     */
    public function delete(int $id): bool;
}
