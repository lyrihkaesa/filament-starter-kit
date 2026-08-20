<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Activity;
use App\Models\PersonalAccessToken;
use App\Policies\ActivityPolicy;
use App\Support\Activity\ActivitySubjectType;
use Awcodes\Curator\Config\GlideManager;
use Awcodes\Curator\Glide\SymfonyResponseFactory;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
// use Illuminate\Support\Facades\Http;
// use Illuminate\Support\Sleep;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\DriverInterface;
use Laravel\Sanctum\Sanctum;

// use Illuminate\Validation\Rules\Password;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ImageManager::class, function (): ImageManager {
            /** @var class-string<DriverInterface> $driver */
            $driver = config('image.driver', Driver::class);

            // Jika di .env IMAGE_DRIVER=imagick, gunakan ImagickDriver
            // Jika tidak, gunakan GdDriver
            return new ImageManager($driver);
        });

        // Alias untuk memastikan jika ada vendor yang memanggil via interface
        $this->app->alias(ImageManager::class, 'image-manager');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Activity::class, ActivityPolicy::class);
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        // 🛡️ Keamanan Dokumentasi API Scramble: Batasi akses dokumentasi API
        Gate::define('viewApiDocs', function (?User $user): bool {
            if (app()->isLocal()) {
                return true;
            }

            return $user !== null && ($user->hasRole('super_admin') || $user->email === 'admin@example.com');
        });

        // @codeCoverageIgnoreStart

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

        // Paksa alias morph yang stabil agar tidak bergantung pada FQCN model.
        Relation::enforceMorphMap(ActivitySubjectType::morphMap());

        // 💎 Kualitas: Matikan proteksi mass-assignment di local
        if (app()->isLocal()) {
            Model::unguard();
        }

        // 🛡️ Database: Cegah perintah berbahaya di production (Laravel 10.0+)
        DB::prohibitDestructiveCommands(app()->isProduction());

        // 🛡️ Database: Jamin integritas transaksi (Laravel 11.10+)
        /** @var Connection $connection */
        $connection = DB::connection();
        if (method_exists($connection, 'handlePotentiallyLostTransactions')) {
            $connection->handlePotentiallyLostTransactions();
        }

        // 📅 Tanggal: Gunakan CarbonImmutable secara global (Laravel 8.x+)
        Date::use(CarbonImmutable::class);

        // 🖼️ Media: Paksa Glide (Curator) menggunakan driver yang sama dengan aplikasi
        $configureGlide = function (GlideManager $manager): void {
            $configDriver = config('image.driver');
            $driver = ($configDriver === \Intervention\Image\Drivers\Imagick\Driver::class) ? 'imagick' : 'gd';

            $manager->serverConfig([
                'driver' => $driver,
                'response' => new SymfonyResponseFactory(resolve('request')),
                'source' => storage_path('app'),
                'source_path_prefix' => config('curator.default_directory', 'public'),
                'cache' => storage_path('app'),
                'cache_path_prefix' => '.cache',
                'max_image_size' => 2000 * 2000,
                'base_url' => $manager->getBasePath(),
            ]);
        };

        $this->app->resolving(GlideManager::class, $configureGlide);

        // Jika sudah terlanjur di-resolve, jalankan konfigurasinya sekarang
        if ($this->app->resolved(GlideManager::class)) {
            $configureGlide($this->app->make(GlideManager::class));
        }

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

        // @codeCoverageIgnoreEnd

        RateLimiter::for('api', function (Request $request): Limit {
            /** @var string $key */
            $key = $request->user()?->getAuthIdentifier() ?? $request->ip();

            return Limit::perMinute(60)->by($key);
        });

        RateLimiter::for('api-auth', fn (Request $request): Limit => Limit::perMinute(5)->by($request->ip()));
    }
}
