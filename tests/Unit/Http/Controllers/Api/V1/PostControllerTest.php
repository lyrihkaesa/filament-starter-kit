<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\PostController;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Collection;
use Mockery;
use ReflectionClass;

it('collection items fallback when paginator has no getCollection method', function (): void {
    $controller = new PostController();
    $reflection = new ReflectionClass(PostController::class);
    $method = $reflection->getMethod('collectionItems');

    $fakePaginator = Mockery::mock(CursorPaginator::class);

    /** @var Collection $result */
    $result = $method->invoke($controller, $fakePaginator);

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result->isEmpty())->toBeTrue();
});
