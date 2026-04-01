---
slug: 29-laravel-debugbar
sidebar_position: 29
---

# Laravel Debugbar

[Laravel Debugbar](https://github.com/fruitcake/laravel-debugbar) (sebelumnya `barryvdh/laravel-debugbar`) adalah paket untuk mengintegrasikan [PHP Debug Bar](http://phpdebugbar.com/) dengan Laravel. Toolbar ini sangat membantu untuk melihat apa yang terjadi di balik layar aplikasi Anda.

## Instalasi

Alat ini diinstal sebagai dependensi pengembangan (*development dependency*) agar tidak membebani performa atau membocorkan informasi di lingkungan produksi.

```bash
composer require fruitcake/laravel-debugbar --dev
```

Menerbitkan konfigurasi:

```bash
php artisan vendor:publish --provider="Fruitcake\LaravelDebugbar\ServiceProvider"
```

## Penggunaan

Debugbar akan aktif secara otomatis jika `APP_DEBUG=true` dan environment bukan `production` atau `testing`.

### Fitur Utama

- **QueryCollector**: Menampilkan semua query database, termasuk binding dan waktu eksekusinya.
- **RouteCollector**: Menampilkan informasi tentang rute saat ini.
- **ViewCollector**: Menampilkan daftar view yang dimuat.
- **EventsCollector**: Menampilkan semua event yang dipicu.
- **LaravelCollector**: Menampilkan versi Laravel dan environment (default: nonaktif).
- **Ajax/Livewire Support**: Menangkap request Ajax dan Livewire secara otomatis (muncul di dropdown di sisi kanan).

### Helper Functions

Ada beberapa fungsi pembantu yang memudahkan proses debugging:

```php
// Semua argumen akan di-dump sebagai pesan debug
debug($var1, $someString, $intValue, $object);

// Dump koleksi sebagai pesan debug
collect([$var1, $someString])->debug();

// Pengukuran waktu manual
start_measure('render', 'Time for rendering');
stop_measure('render');

// Atau menggunakan closure
measure('My long operation', function() {
    // Operasi yang ingin diukur...
});
```

### Facade Interface

Anda juga dapat menggunakan Facade `Debugbar` untuk mencatat pesan dengan level PSR-3:

```php
\Debugbar::info($object);
\Debugbar::error('Error!');
\Debugbar::warning('Watch out..');
\Debugbar::addThrowable($exception);
```

## Konfigurasi

Anda dapat menonaktifkan Debugbar secara manual di `.env`:

```env
DEBUGBAR_ENABLED=true
```

## Keamanan

> **Peringatan:** Gunakan Debugbar **hanya di lingkungan pengembangan**. Jangan aktifkan di website yang dapat diakses publik karena dapat membocorkan informasi sensitif dari request yang tersimpan (seperti query database, data session, dll).
