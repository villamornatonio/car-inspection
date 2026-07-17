<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * StoreInspectionRequest form request validator.
 *
 * Validates incoming HTTP requests for creating new vehicle inspections.
 * Performs validation on inspection fields (carId, condition checks, performed_at timestamp).
 * Automatically returns a 422 validation error response if validation fails.
 * Provides mapping from camelCase API input to snake_case model attributes.
 * All authenticated users are authorized to create inspections.
 */
class StoreInspectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * All users authenticated via Sanctum bearer token are authorized to create inspections.
     * In production, you may want to add role-based authorization checks.
     *
     * @return bool Always returns true for authenticated users
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Validates inspection fields:
     * - carId: Required integer, must exist in cars table (foreign key constraint)
     * - wipers: Required boolean (true/false)
     * - engineSound: Required boolean (true/false)
     * - headlights: Required boolean (true/false)
     * - performedAt: Optional ISO8601 date string
     *
     * Note: Uses camelCase naming to match API conventions; mapped to snake_case in validatedForModel().
     *
     * @return array<string, \Illuminate\Validation\Rules\Rule|array|string> Validation rules keyed by field name
     */
    public function rules(): array
    {
        return [
            'carId' => ['required', 'integer', Rule::exists('cars', 'id')],
            'wipers' => 'required|boolean',
            'engineSound' => 'required|boolean',
            'headlights' => 'required|boolean',
            'performedAt' => 'nullable|date',
        ];
    }

    /**
     * Get validated data with camelCase input mapped to snake_case model attributes.
     *
     * Converts the API's camelCase input field names to snake_case database column names.
     * This allows the API to follow JavaScript conventions (camelCase) while the model
     * and database follow Laravel conventions (snake_case).
     *
     * Mapping:
     * - carId → car_id (foreign key)
     * - wipers → wipers (boolean check)
     * - engineSound → engine_sound (boolean check)
     * - headlights → headlights (boolean check)
     * - performedAt → performed_at (nullable timestamp)
     *
     * @return array<string, mixed> Validated and mapped data ready for model creation
     */
    public function validatedForModel(): array
    {
        $v = $this->validated();
        return [
            'car_id' => $v['carId'],
            'wipers' => $v['wipers'],
            'engine_sound' => $v['engineSound'],
            'headlights' => $v['headlights'],
            'performed_at' => $v['performedAt'] ?? null,
        ];
    }
}
