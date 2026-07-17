<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInspectionRequest;
use App\Http\Resources\InspectionResource;
use App\Services\InspectionService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

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
        $carId = $request->query('carId');
        $inspections = $this->inspectionService->getAllPaginated($carId);

        return $this->success(InspectionResource::collection($inspections));
    }

    /**
     * Create a new inspection.
     *
     * Creates a new inspection record with validated data from the request.
     * Returns the created inspection with HTTP 201 Created status.
     *
     * @param StoreInspectionRequest $request The validated inspection request
     *
     * @return \Illuminate\Http\JsonResponse API response with created inspection (HTTP 201)
     */
    public function store(StoreInspectionRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validatedForModel();
        $inspection = $this->inspectionService->create($data);

        return $this->created(new InspectionResource($inspection));
    }
}
