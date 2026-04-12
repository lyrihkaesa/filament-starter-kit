<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\UploadController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:api-auth')->group(function (): void {
    Route::post('/register', [AuthController::class, 'store'])->name('register');
    Route::post('/login', [AuthController::class, 'create'])->name('login');
});

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/me', [AuthController::class, 'show'])->name('me');
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::apiResource('users', UserController::class);
    Route::apiResource('posts', PostController::class);

    Route::prefix('uploads')->as('uploads.')->group(function (): void {
        Route::post('/prepare', [UploadController::class, 'store'])->name('prepare');
        Route::post('/{upload}/mark-uploaded', [UploadController::class, 'edit'])->name('mark-uploaded');
        Route::get('/{upload}', [UploadController::class, 'show'])->name('show');
        Route::delete('/{upload}', [UploadController::class, 'destroy'])->name('destroy');
    });
});

Route::put('/uploads/{upload}/file', [UploadController::class, 'update'])
    ->middleware('signed')
    ->name('uploads.file');
