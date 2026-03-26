<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Mockery;
use ReflectionClass;

it('collection items fallback when method does not exist', function (): void {
    $controller = new UserController();
    $reflection = new ReflectionClass(UserController::class);
    $method = $reflection->getMethod('collectionItems');

    // We mock LengthAwarePaginator but don't give it getCollection method
    // Actually, to trigger the fallback, it must NOT have getCollection
    $fakePaginator = Mockery::mock(LengthAwarePaginator::class);

    /** @var Collection $result */
    $result = $method->invoke($controller, $fakePaginator);

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result->isEmpty())->toBeTrue();
});
