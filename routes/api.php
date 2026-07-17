<?php

/**
 * API Routes Configuration.
 *
 * Defines all RESTful API endpoints for the Whip Around Car Inspection service.
 *
 * ## API Architecture
 *
 * The API follows RESTful principles with the following structure:
 * - **Versioning**: V1 (future upgrades use v2, v3, etc.)
 * - **Authentication**: Sanctum token-based (bearer token in Authorization header)
 * - **Response Format**: JSON with consistent envelope structure
 * - **Status Codes**: 200 OK, 201 Created, 202 Accepted, 4xx errors, 5xx server errors
 *
 * ## Authentication Flow
 *
 * 1. Client requests token: `POST /api/v1/auth/token` with credentials
 * 2. Server returns bearer token (Sanctum personal access token)
 * 3. Client includes token in all subsequent requests: `Authorization: Bearer {token}`
 * 4. Server validates token via `auth:sanctum` middleware
 * 5. Request proceeds to protected endpoint
 *
 * ## Route Organization
 *
 * Routes are grouped by:
 * - **Version Prefix**: All routes prefixed with `/v1/` for semantic versioning
 * - **Authentication**: Public auth endpoint separate; protected resources require sanctum middleware
 * - **Resource Groups**: Each resource (cars, inspections) has index (list) and store (create) actions
 *
 * ## Future Extensibility
 *
 * - Additional actions (show, update, delete) can be added to resource groups
 * - New resources create new route groups under v1
 * - Version 2 would be: `Route::prefix('v2')->group(...)`
 * - Middleware can be extended per route group (rate limiting, logging, etc.)
 */

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CarController;
use App\Http\Controllers\Api\V1\InspectionController;
use Illuminate\Support\Facades\Route;

/**
 * API V1 Route Group.
 *
 * All endpoints under `/api/v1/` prefix.
 */
Route::prefix('v1')->group(static function (): void {
    /**
     * Authentication Endpoints (Public - No Authentication Required).
     */

    /**
     * POST /api/v1/auth/token.
     *
     * Issue a new authentication token.
     *
     * **Request Body:**
     * ```json
     * {
     *   "email": "user@example.com",
     *   "password": "password"
     * }
     * ```
     *
     * **Success Response (200 OK):**
     * ```json
     * {
     *   "success": true,
     *   "message": "Token issued",
     *   "data": {
     *     "token": "1|txoY7Fbq6iC4RWF35oAqmbyUKSqOmLEkzE5l5D1a774533bf"
     *   }
     * }
     * ```
     *
     * **Error Response (401 Unauthorized):**
     * ```json
     * {
     *   "success": false,
     *   "message": "Invalid credentials",
     *   "errors": []
     * }
     * ```
     *
     * **Usage:**
     * Store the token and include in all subsequent requests via:
     * `Authorization: Bearer {token}`
     */
    Route::post('auth/token', [AuthController::class, 'issueToken']);

    /**
     * Protected Routes (Require Sanctum Authentication).
     *
     * All routes in this group require valid bearer token via `auth:sanctum` middleware.
     * Include token in request header: `Authorization: Bearer {your_token}`
     */
    Route::middleware('auth:sanctum')->group(static function (): void {
        /**
         * Car Resource Endpoints.
         *
         * Manage vehicle records for inspection tracking.
         */

        /**
         * GET /api/v1/cars.
         *
         * List all cars with pagination.
         *
         * **Query Parameters:**
         * - `page`: Page number (default: 1)
         * - `per_page`: Results per page (default: 15)
         *
         * **Success Response (200 OK):**
         * ```json
         * {
         *   "success": true,
         *   "message": "Success",
         *   "data": [
         *     {
         *       "id": 1,
         *       "name": "Toyota Camry",
         *       "make": "Toyota",
         *       "model": "Camry",
         *       "year": 2022,
         *       "createdAt": "2026-07-17T00:00:00+00:00"
         *     }
         *   ],
         *   "meta": {
         *     "total": 16,
         *     "per_page": 15,
         *     "current_page": 1
         *   }
         * }
         * ```
         *
         * **Implementation Details:**
         * - Uses CarService::getAllPaginated() for business logic
         * - Repository pattern abstracts database queries
         * - Data returned via CarResource for consistent formatting
         */
        Route::get('cars', [CarController::class, 'index']);

        /**
         * POST /api/v1/cars.
         *
         * Create a new car (async via Horizon job queue).
         *
         * **Request Body:**
         * ```json
         * {
         *   "name": "Tesla Model 3",
         *   "make": "Tesla",
         *   "model": "Model 3",
         *   "year": 2024
         * }
         * ```
         *
         * **Success Response (202 Accepted):**
         * ```json
         * {
         *   "success": true,
         *   "message": "Accepted",
         *   "data": {
         *     "trackingId": "d8b4069f-3a16-44e6-90bd-14c411d13be9",
         *     "status": "queued",
         *     "payload": {
         *       "name": "Tesla Model 3",
         *       "make": "Tesla",
         *       "model": "Model 3",
         *       "year": 2024
         *     }
         *   }
         * }
         * ```
         *
         * **Implementation Details:**
         * - Returns HTTP 202 Accepted (async processing)
         * - Job queued to Redis 'cars' channel
         * - Horizon daemon processes job within 1-3 seconds
         * - Client polls GET /api/v1/cars to verify creation
         * - Tracking ID allows job status tracking (future enhancement)
         */
        Route::post('cars', [CarController::class, 'store']);

        /**
         * Inspection Resource Endpoints.
         *
         * Manage vehicle inspection records.
         */

        /**
         * GET /api/v1/inspections.
         *
         * List all inspections with optional car filtering.
         *
         * **Query Parameters:**
         * - `carId`: Filter inspections by car ID (optional)
         * - `page`: Page number (default: 1)
         * - `per_page`: Results per page (default: 15)
         *
         * **Success Response (200 OK):**
         * ```json
         * {
         *   "success": true,
         *   "message": "Success",
         *   "data": [
         *     {
         *       "id": 1,
         *       "car_id": 1,
         *       "notes": "Paint damage on rear bumper",
         *       "status": "completed",
         *       "createdAt": "2026-07-17T00:00:00+00:00"
         *     }
         *   ],
         *   "meta": {
         *     "total": 30,
         *     "per_page": 15,
         *     "current_page": 1
         *   }
         * }
         * ```
         *
         * **Filter Example:**
         * `GET /api/v1/inspections?carId=5` — Get all inspections for car ID 5
         *
         * **Implementation Details:**
         * - Uses InspectionService::getAllPaginated() for business logic
         * - Repository pattern abstracts filtering queries
         * - Each inspection includes related car information
         */
        Route::get('inspections', [InspectionController::class, 'index']);

        /**
         * POST /api/v1/inspections.
         *
         * Create a new inspection record.
         *
         * **Request Body:**
         * ```json
         * {
         *   "car_id": 1,
         *   "notes": "Paint damage on rear bumper",
         *   "status": "completed"
         * }
         * ```
         *
         * **Success Response (201 Created):**
         * ```json
         * {
         *   "success": true,
         *   "message": "Created",
         *   "data": {
         *     "id": 30,
         *     "car_id": 1,
         *     "notes": "Paint damage on rear bumper",
         *     "status": "completed",
         *     "createdAt": "2026-07-17T13:30:00+00:00"
         *   }
         * }
         * ```
         *
         * **Implementation Details:**
         * - Returns HTTP 201 Created
         * - Validates input via StoreInspectionRequest
         * - Uses InspectionService::create() for business logic
         * - Synchronous creation (stored immediately, not queued)
         */
        Route::post('inspections', [InspectionController::class, 'store']);
    });
});
