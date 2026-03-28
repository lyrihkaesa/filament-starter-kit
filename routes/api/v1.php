<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:api-auth')->group(function (): void {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/me', [AuthController::class, 'me'])->name('me');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::apiResource('users', UserController::class);

    Route::prefix('uploads')->as('uploads.')->group(function (): void {
        Route::post('/prepare', [App\Http\Controllers\Api\V1\UploadController::class, 'prepare'])->name('prepare');
        Route::put('/{upload}/file', [App\Http\Controllers\Api\V1\UploadController::class, 'file'])->name('file');
        Route::post('/{upload}/mark-uploaded', [App\Http\Controllers\Api\V1\UploadController::class, 'markUploaded'])->name('mark-uploaded');
        Route::get('/{upload}', [App\Http\Controllers\Api\V1\UploadController::class, 'show'])->name('show');
        Route::delete('/{upload}', [App\Http\Controllers\Api\V1\UploadController::class, 'destroy'])->name('destroy');
    });
});
