<?php

declare(strict_types=1);

use App\Http\Requests\Posts\IndexPostRequest;
use App\Http\Requests\Posts\StorePostRequest;
use App\Http\Requests\Posts\UpdatePostRequest;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\ShieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)->use(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(ShieldSeeder::class);
});

it('index post request authorize handles token ability and policy checks', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $request = IndexPostRequest::create('/api/v1/posts', 'GET');

    expect($request->authorize($user))->toBeTrue();

    $deniedToken = $user->createToken('denied-read', ['posts:write'])->accessToken;
    $user->withAccessToken($deniedToken);
    expect($request->authorize($user))->toBeFalse();

    $allowedToken = $user->createToken('allowed-read', ['posts:read'])->accessToken;
    $user->withAccessToken($allowedToken);
    expect($request->authorize($user))->toBeTrue();
});

it('index post request returns rules and prepares default pagination', function (): void {
    $request = IndexPostRequest::create('/api/v1/posts', 'GET');

    $rules = $request->rules();

    expect($rules)->toHaveKeys(['pagination', 'per_page', 'page', 'cursor']);

    $method = new ReflectionMethod(IndexPostRequest::class, 'prepareForValidation');
    $method->invoke($request);

    expect($request->input('pagination'))->toBe('page');
});

it('store post request authorize handles token ability and policy checks', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $request = StorePostRequest::create('/api/v1/posts', 'POST');

    expect($request->authorize($user))->toBeTrue();

    $deniedToken = $user->createToken('denied-create', ['posts:read'])->accessToken;
    $user->withAccessToken($deniedToken);
    expect($request->authorize($user))->toBeFalse();

    $allowedToken = $user->createToken('allowed-create', ['posts:create'])->accessToken;
    $user->withAccessToken($allowedToken);
    expect($request->authorize($user))->toBeTrue();
});

it('store post request returns validation rules', function (): void {
    $request = StorePostRequest::create('/api/v1/posts', 'POST');

    expect($request->rules())->toHaveKeys([
        'title',
        'slug',
        'content',
        'author_id',
        'thumbnail_curator_id',
        'published_at',
    ]);
});

it('update post request authorize handles token ability and policy checks', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $post = Post::factory()->create();

    $request = UpdatePostRequest::create('/api/v1/posts/'.$post->id, 'PATCH');

    expect($request->authorize($post, $user))->toBeTrue();

    $deniedToken = $user->createToken('denied-update', ['posts:read'])->accessToken;
    $user->withAccessToken($deniedToken);
    expect($request->authorize($post, $user))->toBeFalse();

    $allowedToken = $user->createToken('allowed-update', ['posts:update'])->accessToken;
    $user->withAccessToken($allowedToken);
    expect($request->authorize($post, $user))->toBeTrue();
});

it('update post request returns validation rules', function (): void {
    $post = Post::factory()->create();
    $request = UpdatePostRequest::create('/api/v1/posts/'.$post->id, 'PATCH');

    expect($request->rules($post))->toHaveKeys([
        'title',
        'slug',
        'content',
        'author_id',
        'thumbnail_curator_id',
        'published_at',
    ]);
});
