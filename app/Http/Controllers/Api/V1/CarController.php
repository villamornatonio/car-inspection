<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCarRequest;
use App\Http\Resources\CarResource;
use App\Services\CarService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

/**
 * CarController.
 *
 * Handles HTTP requests for car-related endpoints.
 * Delegates business logic to the CarService and returns properly formatted API responses.
 */
class CarController extends Controller
{
    use ApiResponse;

    /**
     * Constructor.
     *
     * Receives the CarService via dependency injection from Laravel's service container.
     * The readonly property ensures the service reference cannot be modified after construction.
     *
     * @param CarService $carService The car service instance for business logic delegation
     */
    public function __construct(private readonly CarService $carService)
    {
    }

    /**
     * List all cars with pagination.
     *
     * Retrieves paginated car records and returns them as a JSON API response.
     * Each car is transformed using the CarResource formatter for consistent output.
     *
     * @param Request $request The HTTP request (supports pagination parameters)
     *
     * @return \Illuminate\Http\JsonResponse JSON response with paginated car collection
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $cars = $this->carService->getAllPaginated(15);

        return $this->success(CarResource::collection($cars));
    }

    /**
     * Create a new car (async via Horizon job queue).
     *
     * Accepts validated car data and dispatches an async CreateCarJob to the Redis queue.
     * Returns immediately with a tracking ID (HTTP 202 Accepted), allowing the client
     * to poll for job status. The actual car creation happens asynchronously via Horizon.
     *
     * **Async Flow:**
     * 1. Request arrives with validated car data
     * 2. CarService::createAsync() generates tracking ID and queues CreateCarJob
     * 3. Response returns immediately with status='queued' (HTTP 202)
     * 4. Horizon daemon processes the job (typically within 1-3 seconds)
     * 5. Car is persisted to database
     * 6. Client can poll GET /api/v1/cars to verify creation
     *
     * @param StoreCarRequest $request The validated car creation request (name, make, model, year)
     *
     * @return \Illuminate\Http\JsonResponse JSON response with tracking ID (HTTP 202 Accepted)
     */
    public function store(StoreCarRequest $request): \Illuminate\Http\JsonResponse
    {
        $payload = $request->validated();
        $result = $this->carService->createAsync($payload);

        return $this->accepted($result);
    }
}
