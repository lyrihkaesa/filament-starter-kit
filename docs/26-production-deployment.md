# Panduan Production Deployment

Dokumen ini memandu Anda melakukan persiapan sebelum merilis aplikasi berbasis starter kit ini ke server produksi (VPS, Cloud Server, dll).

---

## 1. Environment Configuration

Buka file `.env` di server produksi dan pastikan beberapa hal berikut:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com

# Database (PostgreSQL sangat disarankan untuk production)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=nama_db
DB_USERNAME=user_db
DB_PASSWORD=pass_db
```

Generate APP_KEY jika belum ada:

```bash
php artisan key:generate --show
```

---

## 2. Optimasi Laravel

Jalankan perintah ini di server produksi untuk performa maksimal:

```bash
php artisan optimize        # Menggabungkan route, config, dan file autoload
php artisan view:cache      # Meng-compile semua file Blade
php artisan icons:cache    # Meng-cache ikon Filament (Heroicons)
php artisan filament:cache-components # Meng-cache komponen Filament
```

---

## 3. Storage & Assets

Pastikan folder storage dapat diakses secara publik:

```bash
php artisan storage:link
```

Build aset frontend (Vite):

```bash
npm install
npm run build
```

---

## 4. Scheduler (Cron Job)

Laravel membutuhkan satu cron job utama untuk menjalankan semua jadwal tugas (task scheduling).

Tambahkan baris berikut ke crontab server Anda (`crontab -e`):

```bash
* * * * * cd /path-ke-project-anda && php artisan schedule:run >> /dev/null 2>&1
```

---

## 5. Queue Worker (Supervisor)

Karena starter kit ini menggunakan antrean (_Queue_) untuk notifikasi dan upload S3, Anda **wajib** menjalankan queue worker secara terus-menerus.

Gunakan **Supervisor** untuk memantau proses worker. Contoh konfigurasi `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path-ke-project-anda/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path-ke-project-anda/storage/logs/worker.log
stopwaitsecs=3600
```

Jangan lupa untuk me-restart supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

---

## 6. Backup Setup

Starter kit ini sudah menyertakan `spatie/laravel-backup`. Pastikan konfigurasi disk tujuan backup sudah benar (disarankan menggunakan disk eksternal atau S3).

Cek koneksi backup:

```bash
php artisan backup:run --only-list
```

Lalu Anda bisa menambahkan jadwal backup harian di `app/Console/Kernel.php` atau `routes/console.php` (Laravel 11+).

---

## 7. Checklist Terakhir

1. [ ] `APP_DEBUG=false` sudah dipastikan?
2. [ ] Database sudah di-migrate (`php artisan migrate --force`)?
3. [ ] Folder `storage` dan `bootstrap/cache` sudah punya izin tulis (_write permission_)?
4. [ ] SSL (HTTPS) sudah aktif? (Sangat disarankan memakai Let's Encrypt atau Cloudflare)
5. [ ] Scheduler dan Queue Worker berjalan lancar?

Dengan mengikuti langkah-langkah di atas, aplikasi Anda akan berjalan aman dan optimal di server produksi.

---

## Note

```php
   // 🛡️ Keamanan: Paksa HTTPS pastikan Herd dijadikan Secure Site atau kamu komentari baris ini:
        URL::forceScheme('https');


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    });

        final class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('app')
            ->path('app')
            ->favicon(secure_asset('images/logo-128x128.png'));
    }
}
```

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'livewire/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
```
