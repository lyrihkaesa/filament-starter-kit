<?php

declare(strict_types=1);

use App\Actions\Users\CreateUserAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('can create a user', function (): void {
    // Arrange: data untuk membuat user
    $data = [
        'name' => 'Kaesa',
        'email' => 'kaesa@example.com',
        'password' => bcrypt('secret123'),
    ];

    $action = resolve(CreateUserAction::class);

    // Act: jalankan action
    $user = $action->handle($data);

    // Assert: pastikan tersimpan di database
    expect($user)->toBeInstanceOf(User::class)
        ->and($user->name)->toBe('Kaesa')
        ->and($user->email)->toBe('kaesa@example.com');

    $this->assertDatabaseHas('users', [
        'email' => 'kaesa@example.com',
    ]);
});

it('runs inside a transaction', function (): void {
    $data = [
        'name' => 'Broken',
        'email' => 'broken@example.com',
        'password' => bcrypt('secret123'),
    ];

    $action = resolve(CreateUserAction::class);

    // Simulasi error supaya transaction rollback
    $this->expectException(Exception::class);

    DB::shouldReceive('transaction')
        ->once()
        ->andThrow(new Exception('DB error'));

    $action->handle($data);

    // Tidak ada user dengan email ini karena rollback
    $this->assertDatabaseMissing('users', [
        'email' => 'broken@example.com',
    ]);
});
