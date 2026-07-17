<?php

namespace App\Repositories\Eloquent;

use App\Models\Inspection;
use App\Repositories\InspectionRepository;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Eloquent implementation of the InspectionRepository interface.
 *
 * This repository provides concrete data access operations for inspections using
 * Laravel's Eloquent ORM. It supports filtering by car ID and includes eager-loading
 * of related car data to prevent N+1 query problems. The implementation abstracts
 * database interactions behind the InspectionRepository interface, enabling testability
 * and flexible implementations.
 *
 * @implements InspectionRepository
 */
class EloquentInspectionRepository implements InspectionRepository
{
    /**
     * Construct the EloquentInspectionRepository with an Inspection model instance.
     *
     * The Inspection model is injected to enable Eloquent query building and database
     * operations. Using dependency injection promotes testability and loose coupling.
     *
     * @param Inspection $model The Inspection Eloquent model used for database queries
     */
    public function __construct(private Inspection $model)
    {
    }

    /**
     * Retrieve a paginated collection of inspections with optional car filtering.
     *
     * Executes a paginated query that can be optionally filtered by car ID.
     * Always eager-loads the related car data using ->with('car') to prevent N+1 query
     * problems when displaying inspection details alongside car information.
     * Returns a LengthAwarePaginator instance with pagination metadata.
     *
     * @param int|null $carId Optional car ID to filter inspections; null returns all inspections
     * @param int $perPage The number of inspections per page (default: 15)
     *
     * @return LengthAwarePaginator A paginated collection of Inspection models with eager-loaded cars
     */
    public function paginate(?int $carId = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->query()->with('car');

        if ($carId !== null) {
            $query->where('car_id', $carId);
        }

        return $query->paginate($perPage);
    }

    /**
     * Find a single inspection by its primary key with eager-loaded relationships.
     *
     * Retrieves an inspection record from the database by ID and eager-loads its related
     * car data. Returns null if the inspection does not exist, allowing callers to handle
     * missing records gracefully. The eager-loading prevents separate queries when accessing
     * the related car information.
     *
     * @param int $id The inspection's primary key ID
     *
     * @return Inspection|null The Inspection model instance with eager-loaded car if found, null otherwise
     */
    public function find(int $id): ?Inspection
    {
        return $this->model->with('car')->find($id);
    }

    /**
     * Create and persist a new inspection record in the database.
     *
     * Creates a new Inspection instance with the provided data array and immediately
     * persists it to the database. The data array should contain only fillable attributes
     * as defined in the Inspection model (car_id, wipers, engine_sound, headlights, etc.).
     * The created model instance with assigned ID is returned.
     *
     * @param array $data Key-value pairs of inspection attributes (car_id, wipers, engine_sound, headlights, performed_at)
     *
     * @return Inspection The newly created Inspection model instance with ID assigned
     */
    public function create(array $data): Inspection
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing inspection record by its ID.
     *
     * Retrieves the inspection by ID (with eager-loaded car data) and updates its attributes
     * with the provided data array. Returns true if the update succeeded, false if the
     * inspection was not found. Only fillable attributes as defined in the Inspection model are updated.
     * This method uses find() internally to support graceful handling of missing records.
     *
     * @param int $id The inspection's primary key ID
     * @param array $data Key-value pairs of attributes to update
     *
     * @return bool True if update succeeded, false if inspection was not found
     */
    public function update(int $id, array $data): bool
    {
        $inspection = $this->find($id);

        return $inspection ? $inspection->update($data) : false;
    }

    /**
     * Delete an inspection record by its ID.
     *
     * Retrieves the inspection by ID and deletes it from the database.
     * Returns true if the deletion succeeded, false if the inspection was not found.
     * This method handles the case where the specified inspection doesn't exist gracefully.
     *
     * @param int $id The inspection's primary key ID
     *
     * @return bool True if deletion succeeded, false if inspection was not found
     */
    public function delete(int $id): bool
    {
        $inspection = $this->find($id);

        return $inspection ? $inspection->delete() : false;
    }
}
