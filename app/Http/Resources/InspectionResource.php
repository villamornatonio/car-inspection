<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * InspectionResource transforms an Inspection model into a consistent JSON API response.
 *
 * Converts an Inspection Eloquent model to a JSON array with camelCase field names
 * and consistent data types. Includes the related car data when eager-loaded.
 * Booleans are explicitly cast for consistency, and timestamps are formatted as
 * ISO8601 strings. This resource is used in inspection API responses (list, show, create).
 */
class InspectionResource extends JsonResource
{
    /**
     * Transform the Inspection model into a JSON-serializable array.
     *
     * Converts database fields (snake_case) to API response fields (camelCase):
     * - id: Inspection's unique identifier
     * - carId: Associated car ID (foreign key)
     * - wipers: Boolean condition of wipers (cast explicitly)
     * - engineSound: Boolean condition of engine sound
     * - headlights: Boolean condition of headlights
     * - performedAt: ISO8601 timestamp of when inspection was performed (nullable)
     * - createdAt: ISO8601 timestamp of when record was created
     * - car: Nested CarResource if car relationship was eager-loaded (via with('car'))
     *
     * @param \Illuminate\Http\Request $request The current HTTP request (unused)
     *
     * @return array<string, mixed> JSON-serializable array representation of the inspection
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'carId' => $this->car_id,
            'wipers' => (bool) $this->wipers,
            'engineSound' => (bool) $this->engine_sound,
            'headlights' => (bool) $this->headlights,
            'performedAt' => $this->performed_at?->toIso8601String(),
            'createdAt' => $this->created_at?->toIso8601String(),
            'car' => $this->whenLoaded('car', fn () => new CarResource($this->car)),
        ];
    }
}
