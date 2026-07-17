<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

/**
 * ApiResponse trait.
 *
 * Provides consistent JSON response formatting methods for API controllers.
 * This trait standardizes API responses across the application with a uniform
 * structure containing success status, message, data, and optional errors.
 * All methods return Illuminate\Http\JsonResponse with appropriate HTTP status codes.
 *
 * Response structure includes: success (boolean), message (string), data (mixed), errors (array).
 */
trait ApiResponse
{
    /**
     * Return a successful JSON response with optional data.
     *
     * Constructs a success response with success flag set to true, a message describing
     * the operation result, optional response data, and an HTTP status code (default 200).
     * This is the base method used by other success response methods.
     * Typically used for successful GET, POST, PUT, PATCH operations.
     *
     * @param mixed $data Optional response data to include in the response (can be array, object, null)
     * @param string $message Human-readable message describing the operation result (default: 'OK')
     * @param int $code HTTP status code (default: 200 OK)
     *
     * @return JsonResponse A JSON response with success=true, message, data, and appropriate status code
     */
    protected function success($data = null, string $message = 'OK', int $code = 200): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data], $code);
    }

    /**
     * Return a created (201) JSON response.
     *
     * Constructs a success response with HTTP 201 Created status code, typically used
     * when a new resource has been created and immediately persisted. Delegates to
     * the success() method with status code 201.
     * Used primarily for POST endpoints that create new resources.
     *
     * @param mixed $data Optional response data, typically the newly created resource
     * @param string $message Human-readable message describing the creation (default: 'Created')
     *
     * @return JsonResponse A JSON response with success=true, message, data, and HTTP 201 status
     */
    protected function created($data = null, string $message = 'Created'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    /**
     * Return an accepted (202) JSON response for async operations.
     *
     * Constructs a success response with HTTP 202 Accepted status code, used when
     * a request has been accepted for processing but the work is not yet complete.
     * Typically used for asynchronous operations where the result will be available later.
     * Delegates to the success() method with status code 202.
     * Used for POST endpoints that queue async jobs (e.g., async car creation via Horizon).
     *
     * @param mixed $data Optional response data, typically tracking ID or job reference
     * @param string $message Human-readable message describing the async operation (default: 'Accepted')
     *
     * @return JsonResponse A JSON response with success=true, message, data, and HTTP 202 status
     */
    protected function accepted($data = null, string $message = 'Accepted'): JsonResponse
    {
        return $this->success($data, $message, 202);
    }

    /**
     * Return an error JSON response with optional detailed errors.
     *
     * Constructs an error response with success flag set to false, an error message,
     * optional detailed error information, and an HTTP status code (default 500).
     * This is the base method used by other error response methods.
     * The errors array typically contains validation errors or detailed error information
     * keyed by field name or error category.
     *
     * @param string $message Human-readable error message (default: 'Error')
     * @param array $errors Optional array of detailed error information, typically validation errors keyed by field
     * @param int $code HTTP status code (default: 500 Internal Server Error)
     *
     * @return JsonResponse A JSON response with success=false, message, errors, and appropriate status code
     */
    protected function error(string $message = 'Error', array $errors = [], int $code = 500): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message, 'errors' => $errors], $code);
    }

    /**
     * Return a not found (404) error response.
     *
     * Constructs an error response with HTTP 404 Not Found status code, used when
     * a requested resource does not exist. Delegates to the error() method with
     * status code 404 and empty errors array.
     * Used for GET/PUT/DELETE endpoints when the resource is not found.
     *
     * @param string $message Human-readable error message (default: 'Not Found')
     *
     * @return JsonResponse A JSON response with success=false, message, empty errors, and HTTP 404 status
     */
    protected function notFound(string $message = 'Not Found'): JsonResponse
    {
        return $this->error($message, [], 404);
    }

    /**
     * Return a validation error (422) response.
     *
     * Constructs an error response with HTTP 422 Unprocessable Entity status code,
     * used when request validation fails. Delegates to the error() method with
     * status code 422 and the provided errors array. The errors array typically
     * contains field-level validation error messages keyed by field name.
     * Used for POST/PUT/PATCH endpoints when request data validation fails.
     *
     * @param array $errors Array of validation errors keyed by field name (e.g., ['email' => ['Email is required'], 'name' => ['Name must be unique']])
     * @param string $message Human-readable error message (default: 'Validation Error')
     *
     * @return JsonResponse A JSON response with success=false, message, errors, and HTTP 422 status
     */
    protected function validationError(array $errors, string $message = 'Validation Error'): JsonResponse
    {
        return $this->error($message, $errors, 422);
    }
}
