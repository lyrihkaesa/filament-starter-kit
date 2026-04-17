<?php

declare(strict_types=1);

arch()->preset()->php();
arch()->preset()->strict()->ignoring([
    'App\Filament',
    'App\Http\Requests',
    'App\Models',
    'App\Console\Commands',
    'App\Database\Seeders',
]);
arch()->preset()->laravel()->ignoring('App\Providers\Filament');
arch()->preset()->security()->ignoring([
    'assert',
]);

arch('controllers')
    ->expect('App\Http\Controllers')
    ->not->toBeUsed();
