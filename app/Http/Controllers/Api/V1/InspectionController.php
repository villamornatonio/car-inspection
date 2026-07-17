<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInspectionRequest;
use App\Http\Resources\InspectionResource;
use App\Models\Inspection;
use App\Services\InspectionService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * InspectionController.
 *
 * Handles HTTP requests for inspection-related endpoints.
 * Delegates business logic to the InspectionService and returns
 * properly formatted API responses.
 *
 * This controller follows the thin controller pattern: it only handles
 * request validation, service delegation, and response formatting.
 */
class InspectionController extends Controller
{
    use ApiResponse;

    /**
     * Constructor.
     *
     * @param InspectionService $inspectionService The inspection service instance
     */
    public function __construct(private InspectionService $inspectionService)
    {
    }

    /**
     * List all inspections.
     *
     * Retrieves paginated inspections with optional filtering by car ID.
     * Results are cached for 3600 seconds (1 hour) to improve performance.
     * Cache key includes carId to provide separate caches for different filters.
     * Each inspection includes its associated car information.
     *
     * @param Request $request The HTTP request containing optional query parameters:
     *                         - carId: Filter inspections by car ID
     *                         - page: Page number for pagination (default: 1)
     *
     * @return \Illuminate\Http\JsonResponse API response with inspection collection
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $carId = $request->query('carId') ? (int)$request->query('carId') : null;
        $cacheKey = 'inspections_' . ($carId ?? 'all');
        $inspections = Cache::remember($cacheKey, 3600, function () use ($carId) {
            $paginated = $this->inspectionService->getAllPaginated($carId);
            // Convert paginator items to array for caching (avoids serialization issues with models)
            return collect($paginated->items())->map(fn ($inspection) => $inspection->toArray())->all();
        });

        // Convert cached arrays back to models for resource formatting
        $inspectionModels = collect($inspections)->map(fn ($data) => Inspection::make($data));

        return $this->success(InspectionResource::collection($inspectionModels));
    }

    /**
     * Create a new inspection.
     *
     * Creates a new inspection record with validated data from the request.
     * Returns the created inspection with HTTP 201 Created status.
     * Invalidates all inspection caches to ensure fresh data on next GET request.
     *
     * @param StoreInspectionRequest $request The validated inspection request
     *
     * @return \Illuminate\Http\JsonResponse API response with created inspection (HTTP 201)
     */
    public function store(StoreInspectionRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validatedForModel();
        $inspection = $this->inspectionService->create($data);
        
        // Invalidate all inspection caches
        Cache::forget('inspections_all');
        // Also forget the car-specific cache for this inspection
        if (isset($data['car_id'])) {
            Cache::forget('inspections_' . $data['car_id']);
        }

        return $this->created(new InspectionResource($inspection));
    }
}
