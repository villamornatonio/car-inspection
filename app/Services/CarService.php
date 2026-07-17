<?php

namespace App\Services;

use App\Jobs\CreateCarJob;
use App\Repositories\CarRepository;
use Illuminate\Support\Str;

/**
 * CarService.
 *
 * Business logic layer for car operations.
 * Encapsulates all car-related business rules and delegates data access
 * to the CarRepository. This service acts as a facade between controllers
 * and the repository, ensuring loose coupling and single responsibility.
 *
 * ## Constructor Injection Pattern
 *
 * The CarRepository is injected via constructor (not fetched via app() or new).
 * This enables:
 * - Easy mocking for unit tests (pass a mock repository instead of real one)
 * - Clear dependency declaration
 * - Automatic resolution by Laravel's service container
 * - Consistent architecture across the application
 *
 * All methods delegate to the repository, ensuring data access is abstracted.
 */
class CarService
{
    /**
     * Constructor.
     *
     * @param CarRepository $carRepository The car repository for data access
     */
    public function __construct(private readonly CarRepository $carRepository)
    {
    }

    /**
     * Dispatch async car creation job.
     *
     * Creates a background job to persist the car data. Returns immediately with
     * a tracking ID so the client can poll for job completion. Actual database
     * insertion happens asynchronously via Horizon within seconds.
     *
     * **Flow:**
     * 1. Generate unique tracking UUID
     * 2. Dispatch CreateCarJob to Redis 'cars' queue
     * 3. Return tracking ID and queued status immediately
     * 4. Horizon daemon picks up and processes the job
     * 5. Car is created in database
     *
     * @param array $data Car data array with keys: name, make, model, year
     *
     * @return array{trackingId: string, status: string, payload: array} Result containing:
     *                                                                   - trackingId: UUID string to track job progress
     *                                                                   - status: Always 'queued' for async
     *                                                                   - payload: Echo of input data
     */
    public function createAsync(array $data): array
    {
        $trackingId = (string) Str::uuid();

        // Dispatch async job onto cars queue
        CreateCarJob::dispatch($data, $trackingId)->onQueue('cars');

        return [
            'trackingId' => $trackingId,
            'status' => 'queued',
            'payload' => $data,
        ];
    }

    /**
     * Create a car synchronously.
     *
     * Immediately persists car data to database without queueing.
     * Use only when immediate feedback is required. For API responses,
     * prefer createAsync() for better performance and user experience.
     *
     * @param array $data Car data array with keys: name, make, model, year
     *
     * @return array{id: int, status: string, data: array} Result containing:
     *                                                     - id: Database car ID
     *                                                     - status: Always 'created'
     *                                                     - data: Full car model as array
     */
    public function createSync(array $data): array
    {
        $car = $this->carRepository->create($data);

        return [
            'id' => $car->id,
            'status' => 'created',
            'data' => $car->toArray(),
        ];
    }

    /**
     * Get all cars with pagination.
     *
     * @param int $perPage Number of results per page (default: 15)
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator Paginated car collection
     */
    public function getAllPaginated(int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->carRepository->paginate($perPage);
    }

    /**
     * Get a single car by ID.
     *
     * @param int $id The car ID
     *
     * @return \App\Models\Car|null The car or null if not found
     */
    public function getById(int $id): ?\App\Models\Car
    {
        return $this->carRepository->find($id);
    }

    /**
     * Update a car by ID.
     *
     * @param int $id The car ID
     * @param array $data The fields to update
     *
     * @return bool True if successful, false if car not found
     */
    public function update(int $id, array $data): bool
    {
        return $this->carRepository->update($id, $data);
    }

    /**
     * Delete a car by ID.
     *
     * @param int $id The car ID
     *
     * @return bool True if successful, false if car not found
     */
    public function delete(int $id): bool
    {
        return $this->carRepository->delete($id);
    }
}
