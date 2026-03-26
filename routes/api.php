<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->as('v1.')
    ->group(__DIR__.'/api/v1.php');
