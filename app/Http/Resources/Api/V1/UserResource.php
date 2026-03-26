<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\User
 */
final class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->resource->getRouteKey(),
            'name' => (string) $this->resource->name,
            'email' => (string) $this->resource->email,
            'avatar' => $this->resource->avatar !== null ? (string) $this->resource->avatar : null,
            'email_verified_at' => $this->resource->email_verified_at?->toISOString(),
            'created_at' => $this->resource->created_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
            'deleted_at' => $this->resource->deleted_at?->toISOString(),
            'anonymized_at' => $this->resource->anonymized_at?->toISOString(),
        ];
    }
}
