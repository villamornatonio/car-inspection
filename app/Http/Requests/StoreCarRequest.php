<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StoreCarRequest form request validator.
 *
 * Validates incoming HTTP requests for creating new cars.
 * Performs validation on all required car fields (name, make, model, year).
 * Automatically returns a 422 validation error response if validation fails.
 * All authenticated users are authorized to create cars.
 */
class StoreCarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * All users authenticated via Sanctum bearer token are authorized to create cars.
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
     * Validates all required car attributes:
     * - name: Required string, max 255 characters
     * - make: Required string (manufacturer), max 255 characters
     * - model: Required string (vehicle model), max 255 characters
     * - year: Required integer, valid model year between 1900 and next year
     *
     * @return array<string, \Illuminate\Validation\Rules\Rule|array|string> Validation rules keyed by field name
     */
    public function rules(): array
    {
        $nextYear = now()->addYear()->year;
        return [
            'name' => 'required|string|max:255',
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => "required|integer|between:1900,$nextYear",
        ];
    }
}
