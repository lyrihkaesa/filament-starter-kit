<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 *
 * @property-read User $resource
 */
final class UserResource extends JsonResource
{
    /**
     * @var array<string, bool>
     */
    private array $capabilities = [
        'view' => false,
        'update' => false,
        'delete' => false,
    ];

    /**
     * @param  array<string, bool>  $capabilities
     */
    public function withCapabilities(array $capabilities): self
    {
        $this->capabilities = $capabilities;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $this->resource;
        $routeKey = $user->getRouteKey();

        return [
            'id' => is_scalar($routeKey) ? (string) $routeKey : '',
            'name' => is_scalar($user->name) ? (string) $user->name : '',
            'email' => is_scalar($user->email) ? (string) $user->email : '',
            'avatar_url' => $user->avatarMedia?->url,
            'email_verified_at' => $user->email_verified_at instanceof CarbonInterface ? $user->email_verified_at->toISOString() : null,
            'created_at' => $user->created_at instanceof CarbonInterface ? $user->created_at->toISOString() : null,
            'updated_at' => $user->updated_at instanceof CarbonInterface ? $user->updated_at->toISOString() : null,
            'deleted_at' => $user->deleted_at instanceof CarbonInterface ? $user->deleted_at->toISOString() : null,
            'anonymized_at' => $user->anonymized_at instanceof CarbonInterface ? $user->anonymized_at->toISOString() : null,
            'can' => $this->capabilities,
        ];
    }
}
