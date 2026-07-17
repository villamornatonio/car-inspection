<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * CarResource transforms a Car model into a consistent JSON API response.
 *
 * Converts a Car Eloquent model to a JSON array with camelCase field names and
 * consistent data types. All timestamps are formatted as ISO8601 strings.
 * This resource is used in API responses (list, show, create operations) to ensure
 * consistent JSON structure across all car endpoints.
 */
class CarResource extends JsonResource
{
    /**
     * Transform the Car model into a JSON-serializable array.
     *
     * Converts database fields (snake_case) to API response fields (camelCase):
     * - id: Car's unique identifier
     * - name: Car's display name
     * - make: Vehicle manufacturer
     * - model: Vehicle model name
     * - year: Model year as integer (cast to int for consistency)
     * - createdAt: ISO8601 timestamp of creation
     *
     * @param \Illuminate\Http\Request $request The current HTTP request (unused)
     *
     * @return array<string, mixed> JSON-serializable array representation of the car
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'make' => $this->make,
            'model' => $this->model,
            'year' => (int) $this->year,
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}
