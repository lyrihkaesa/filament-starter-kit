<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /**
         * 🚀 Asset Prefetching
         * Meningkatkan performa saat mengakses asset
         * https://github.com/nunomaduro/essentials/blob/main/src/Configurables/AggressivePrefetching.php
         */
        Vite::useAggressivePrefetching();

        /**
         * ✅ Force HTTPS in production
         * Memastikan semua URL menggunakan https://
         * Penting untuk keamanan saat live deployment
         * https://github.com/nunomaduro/essentials/blob/main/src/Configurables/ForceScheme.php
         */
        // if (app()->isProduction()) {
        URL::forceHttps();
        // }

        /**
         * https://github.com/nunomaduro/essentials/blob/main/src/Configurables/SetDefaultPassword.php
         */
        // Password::defaults(fn (): ?Password => app()->isProduction() ? Password::min(8)->max(255)->uncompromised() : null);

        /**
         * ✅ Eloquent Strict Models
         * Hindari bug diam-diam karena:
         * - Akses atribut yang tidak ada
         * - Lazy loading yang tidak diatur
         * - Penugasan atribut yang tidak terdefinisi
         * https://github.com/nunomaduro/essentials/blob/main/src/Configurables/ShouldBeStrict.php
         */
        // Model::shouldBeStrict();

        /**
         * ✅ Mass Assignment Optional Unguard (hanya untuk local/dev)
         * Berguna saat seeding atau mocking tanpa perlu $fillable
         * https://github.com/nunomaduro/essentials/blob/main/src/Configurables/Unguard.php
         */
        // if (app()->isLocal()) {
        //     Model::unguard();
        // }

        /**
         * https://github.com/nunomaduro/essentials/blob/main/src/Configurables/ProhibitDestructiveCommands.php
         */
        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        /**
         * https://github.com/nunomaduro/essentials/blob/main/src/Configurables/AutomaticallyEagerLoadRelationships.php
         */
        // $this->automaticallyEagerLoadRelationships();

        /**
         * ✅ Immutable DateTime
         * Gunakan CarbonImmutable untuk mencegah perubahan tanggal tidak sengaja
         * https://github.com/nunomaduro/essentials/blob/main/src/Configurables/ImmutableDates.php
         */
        Date::use(CarbonImmutable::class);

        // Todo: Testing
        // https://github.com/nunomaduro/essentials/blob/main/src/Configurables/FakeSleep.php
        // https://github.com/nunomaduro/essentials/blob/main/src/Configurables/PreventStrayRequests.php
    }

    public function automaticallyEagerLoadRelationships(): void
    {
        if (! method_exists(Model::class, 'automaticallyEagerLoadRelationships')) {
            return;
        }

        Model::automaticallyEagerLoadRelationships();
    }
}
