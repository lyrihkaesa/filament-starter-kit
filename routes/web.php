<?php

declare(strict_types=1);

use App\Filament\Pages\Auth\RestoreAccount;
use App\Livewire\Posts\Index as PostsIndex;
use App\Livewire\Posts\Show as PostsShow;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => to_route('login'));

Route::get('/login', fn () => to_route('filament.app.auth.login'))->name('login');

Route::get('/restore-account/{id}', RestoreAccount::class)
    ->middleware(['signed'])
    ->name('restore-account');

Route::get('/posts', PostsIndex::class)->name('posts.index');
Route::get('/posts/{post:slug}', PostsShow::class)->name('posts.show');
