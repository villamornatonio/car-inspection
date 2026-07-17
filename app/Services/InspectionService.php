<?php

namespace App\Services;

use App\Repositories\InspectionRepository;

/**
 * InspectionService.
 *
 * Business logic layer for inspection operations.
 * Encapsulates all inspection-related business rules and delegates
 * data access to the InspectionRepository.
 *
 * This service acts as a facade between controllers and the repository,
 * ensuring loose coupling and single responsibility.
 */
class InspectionService
{
    /**
     * Constructor.
     *
     * @param InspectionRepository $repository The inspection repository instance
     */
    public function __construct(private InspectionRepository $repository)
    {
    }

    /**
     * Get all inspections with optional car filtering.
     *
     * Retrieves paginated inspection records, optionally filtered by car ID.
     * Each inspection includes its associated car information.
     *
     * @param int|null $carId Optional car ID to filter inspections
     * @param int $perPage Number of results per page (default: 15)
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator Paginated inspection collection
     */
    public function getAllPaginated(?int $carId = null, int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->repository->paginate($carId, $perPage);
    }

    /**
     * Get a single inspection by ID.
     *
     * @param int $id The inspection ID
     *
     * @return \App\Models\Inspection|null The inspection with related car data, or null if not found
     */
    public function getById(int $id): ?\App\Models\Inspection
    {
        return $this->repository->find($id);
    }

    /**
     * Create a new inspection.
     *
     * @param array $data Inspection data (car_id, notes, status, etc.)
     *
     * @return \App\Models\Inspection The newly created inspection instance
     */
    public function create(array $data): \App\Models\Inspection
    {
        return $this->repository->create($data);
    }

    /**
     * Update an existing inspection.
     *
     * @param int $id The inspection ID
     * @param array $data The data to update
     *
     * @return bool True if update was successful, false otherwise
     */
    public function update(int $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    /**
     * Delete an inspection.
     *
     * @param int $id The inspection ID
     *
     * @return bool True if deletion was successful, false otherwise
     */
    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
