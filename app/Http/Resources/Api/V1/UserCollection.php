<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

final class UserCollection extends ResourceCollection
{
    public $collects = UserResource::class;

    /**
     * @return array{users: array<int, array<string, mixed>>, meta: array<string, int|string|bool|null>}
     */
    public function toArray(Request $request): array
    {
        /** @var LengthAwarePaginator|CursorPaginator $paginator */
        $paginator = $this->resource;

        return [
            'users' => UserResource::collection($this->collection)->resolve($request),
            'meta' => $this->meta($paginator),
        ];
    }

    /**
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
