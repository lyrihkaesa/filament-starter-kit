<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\User;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;

final class UserCollection extends ResourceCollection
{
    public $collects = UserResource::class;

    /**
     * @var array<string, array<string, bool>>
     */
    private array $itemCapabilities = [];

    /**
     * @var array<string, bool>
     */
    private array $collectionCapabilities = [];

    /**
     * @param  array<string, array<string, bool>>  $itemCapabilities
     * @param  array<string, bool>  $collectionCapabilities
     */
    public function withCapabilities(array $itemCapabilities, array $collectionCapabilities = []): self
    {
        $this->itemCapabilities = $itemCapabilities;
        $this->collectionCapabilities = $collectionCapabilities;

        return $this;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function toArray(Request $request): array
    {
        /** @var Collection<int, UserResource> $collection */
        $collection = $this->collection;

        /** @var array<int, array<string, mixed>> $items */
        $items = $collection
            ->map(function (UserResource $resource) use ($request): array {
                /** @var User $user */
                $user = $resource->resource;
                $routeKey = $user->getRouteKey();
                $key = is_scalar($routeKey) ? (string) $routeKey : '';

                return $resource
                    ->withCapabilities($this->itemCapabilities[$key] ?? [
                        'view' => false,
                        'update' => false,
                        'delete' => false,
                    ])
                    ->toArray($request);
            })
            ->all();

        return $items;
    }

    /**
     * @param  array<string, array<string, mixed>>  $paginated
     * @param  array<string, mixed>  $default
     * @return array{meta: array<string, mixed>}
     */
    public function paginationInformation(Request $request, array $paginated, array $default): array
    {
        /** @var LengthAwarePaginator<int, User>|CursorPaginator<int, User> $paginator */
        $paginator = $this->resource;

        $meta = $this->meta($paginator);

        if ($this->collectionCapabilities !== []) {
            $meta['can'] = $this->collectionCapabilities;
        }

        return [
            'meta' => $meta,
        ];
    }

    /**
     * @param  LengthAwarePaginator<int, User>|CursorPaginator<int, User>  $paginator
     * @return array<string, int|string|bool|null>
     */
    private function meta(LengthAwarePaginator|CursorPaginator $paginator): array
    {
        if ($paginator instanceof LengthAwarePaginator) {
            return [
                'pagination_type' => 'page',
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'has_more_pages' => $paginator->hasMorePages(),
            ];
        }

        return [
            'pagination_type' => 'cursor',
            'per_page' => $paginator->perPage(),
            'next_cursor' => $paginator->nextCursor()?->encode(),
            'prev_cursor' => $paginator->previousCursor()?->encode(),
            'has_more_pages' => $paginator->hasMorePages(),
        ];
    }
}
