<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Http;
// use Illuminate\Support\Sleep;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

// use Illuminate\Validation\Rules\Password;

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
        // 🚀 Optimasi Asset: Prefetching agresif (Laravel 11.7+)
        Vite::useAggressivePrefetching();

        // 🛡️ Keamanan: Paksa HTTPS pastikan Herd dijadikan Secure Site atau kamu komentari baris ini:
        URL::forceHttps();

        // 🛡️ Keamanan: Standar password global (Laravel 8.43+)
        // Password::defaults(fn (): Password => app()->isProduction()
        //     ? Password::min(8)->uncompromised()->letters()->numbers()->symbols()
        //     : Password::min(8)
        // );

        // 💎 Kualitas: Mode ketat Eloquent (Laravel 9.11+)
        // Model::shouldBeStrict();

        // 💎 Kualitas: Matikan proteksi mass-assignment di local
        if (app()->isLocal()) {
            Model::unguard();
        }

        // 🛡️ Database: Cegah perintah berbahaya di production (Laravel 10.0+)
        DB::prohibitDestructiveCommands(app()->isProduction());

        // 🛡️ Database: Jamin integritas transaksi (Laravel 11.10+)
        DB::handlePotentiallyLostTransactions();

        // 📅 Tanggal: Gunakan CarbonImmutable secara global (Laravel 8.x+)
        Date::use(CarbonImmutable::class);

        // 💎 Kualitas: Otomatis load relasi (Laravel 12.8+)
        Model::automaticallyEagerLoadRelationships();

        // 🧪 Testing: Cegah request HTTP keluar saat testing (Laravel 9.x+)
        // if (app()->runningUnitTests()) {
        //     Http::preventStrayRequests();
        // }

        // 🧪 Testing: Matikan jeda sleep saat testing (Laravel 10.x+)
        // if (app()->runningUnitTests()) {
        //     Sleep::fake();
        // }
    }
}
