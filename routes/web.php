<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => to_route('login'));

Route::get('/login', fn () => to_route('filament.app.auth.login'))->name('login');
