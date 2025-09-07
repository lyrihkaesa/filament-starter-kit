<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return to_route('login');
});

Route::get('/login', function () {
    return redirect()->route('filament.app.auth.login');
})->name('login');
