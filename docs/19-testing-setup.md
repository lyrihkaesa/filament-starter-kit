# Testing Setup, Strategy & Architecture

Dokumen ini menggabungkan semua yang perlu Anda ketahui tentang **setup environment pengujian** di starter kit ini:

1. **Opsi database untuk testing** — PostgreSQL vs SQLite
2. **Architecture tests** — menjaga pola desain dengan Pest Arch
3. **Browser testing** — simulasi interaksi pengguna nyata di browser

---

## Bagian 1: Opsi Database untuk Testing

Starter kit ini mendukung dua cara utama untuk menjalankan pengujian database: **PostgreSQL** (disarankan untuk identik dengan produksi) dan **SQLite In-Memory** (paling cepat).

### 1. PostgreSQL (Default — Paling Mirip Production)

Direkomendasikan menggunakan PostgreSQL agar lingkungan pengujian sedekat mungkin dengan lingkungan produksi.

**Persiapan:**

1. Salin file `.env.testing.example` menjadi `.env.testing`:
   ```bash
   cp .env.testing.example .env.testing
   ```
2. Pastikan database untuk pengujian sudah ada di PostgreSQL Anda.
3. Sesuaikan kredensial di file `.env.testing`:
   ```env
   DB_CONNECTION=pgsql
   DB_DATABASE=filament_starter_kit_test
   ```

**Cara menjalankan:**

```bash
php artisan test
```

Laravel akan otomatis memuat `.env.testing` sebagai prioritas.

---

### 2. SQLite (Fast Testing)

Gunakan SQLite jika Anda ingin menjalankan pengujian dengan sangat cepat tanpa perlu setup database eksternal.

**Persiapan:**

1. Salin file `.env.sqlite.testing.example` menjadi `.env.sqlite.testing`:
   ```bash
   cp .env.sqlite.testing.example .env.sqlite.testing
   ```

**Cara menjalankan:**

```bash
php artisan test --env=sqlite.testing
```

---

### 3. Fallback (phpunit.xml)

Jika Anda tidak memiliki file `.env.testing` atau `.env.sqlite.testing`, Laravel akan menggunakan konfigurasi yang ada di dalam `phpunit.xml` sebagai cadangan. Secara default, fallback ini menggunakan **SQLite In-Memory**.

---

### Memastikan Database yang Digunakan

Gunakan perintah ini untuk memverifikasi database mana yang sedang aktif:

```bash
# Untuk melihat koneksi default di environment testing:
php artisan tinker --env=testing --execute="dump('Koneksi: ' . config('database.default'))"

# Untuk melihat koneksi saat menggunakan sqlite environment:
php artisan tinker --env=sqlite.testing --execute="dump('Koneksi: ' . config('database.default'))"
```

---

## Bagian 2: Architecture Tests (Pest Arch)

Architecture tests memastikan pola desain yang telah ditetapkan tidak dilanggar — terutama **Action Pattern** — tanpa perlu review manual setiap saat.

### Tujuan

- Menjamin konsistensi penggunaan **Action Pattern**
- Memastikan tidak ada business logic yang bocor ke Controller atau Filament Page
- Menjaga kebersihan kode dengan strict typing dan standar PHP modern
- Mencegah penggunaan fitur-fitur yang dianggap tidak aman atau deprecated

### Konfigurasi Standar

Kami menggunakan preset bawaan Pest untuk mencakup area-area penting:

- `arch()->preset()->php()`: Memastikan penggunaan fitur PHP yang modern dan aman.
- `arch()->preset()->strict()`: Memaksa penggunaan `declare(strict_types=1)` dan type hints.
- `arch()->preset()->laravel()`: Mengikuti praktik terbaik Laravel.
- `arch()->preset()->security()`: Menghindari fungsi-fungsi PHP yang berbahaya.

### Aturan Khusus: Larangan Controller

Salah satu aturan paling penting dalam starter kit ini adalah **larangan penggunaan Controller**. Karena kita menggunakan Filament untuk admin dan Action untuk logic, Controller seringkali hanya menjadi boilerplate yang tidak perlu atau tempat persembunyian logic yang salah.

### Implementasi Kode

Berikut adalah konten file `tests/Feature/ArchitectureTest.php`:

```php
<?php

declare(strict_types=1);

arch()->preset()->php();
arch()->preset()->strict();
arch()->preset()->laravel();
arch()->preset()->security()->ignoring([
    'assert',
]);

arch('controllers')
    ->expect('App\Http\Controllers')
    ->not->toBeUsed();
```

---

## Bagian 3: Browser Testing (Pest Browser Plugin)

Starter kit ini mendukung pengujian browser menggunakan [Pest 4's browser plugin](https://pestphp.com/docs/browser-testing). Pengujian browser memungkinkan Anda mensimulasikan interaksi pengguna asli dalam browser (klik, ketik, scroll, dll.).

### Instalasi

Plugin berikut telah diinstal untuk mendukung fungsionalitas ini:

```bash
composer require pestphp/pest-plugin-laravel pestphp/pest-plugin-browser --dev
```

### Penggunaan Dasar

Tes browser biasanya diletakkan di dalam direktori `tests/Browser/`.

**Contoh Tes Dasar:**

```php
use function Pest\Laravel\visit;

it('has no javascript errors on the login page', function () {
    visit('/admin/login')
        ->assertSee('Sign in')
        ->assertNoJavaScriptErrors();
});
```

**Smoke Testing (banyak halaman sekaligus):**

```php
it('has no console logs on main pages', function () {
    $pages = visit(['/admin/login', '/admin/register']);

    $pages->assertNoJavaScriptErrors()
          ->assertNoConsoleLogs();
});
```

**Interaksi Pengguna:**

```php
it('can fill the login form', function () {
    visit('/admin/login')
        ->fill('email', 'admin@example.com')
        ->fill('password', 'password')
        ->click('button[type="submit"]')
        ->assertPathIs('/admin');
});
```

### Menjalankan Tes Browser

```bash
# Semua tes
php artisan test --compact

# Filter tes browser saja
php artisan test --compact --filter=Browser
```

### Tips Debugging

Jika tes gagal, gunakan metode berikut untuk membantu debugging:

- `->screenshot('nama-file')`: Mengambil gambar browser saat itu.
- `->pause(2000)`: Menghentikan eksekusi tes selama beberapa milidetik.
- `->consoleLogs()`: Melihat log console browser.

> **Catatan:** Pastikan environment testing sudah terkonfigurasi dengan benar (misalnya menggunakan database SQLite in-memory atau database testing terpisah).

---

## Ringkasan

| Kebutuhan | Perintah |
|-----------|---------|
| Tes dengan PostgreSQL | `php artisan test` |
| Tes dengan SQLite | `php artisan test --env=sqlite.testing` |
| Architecture test | Otomatis masuk saat `php artisan test` |
| Browser test saja | `php artisan test --filter=Browser` |

---

## Bagian 4: Isolasi Seeder untuk Environment Testing

Agar testing stabil dan tidak rusak saat `ShieldSeeder.php` di-generate ulang, project ini memakai pola berikut:

- `database/seeders/ShieldSeeder.php` dianggap generated file dari `php artisan shield:generate --all`.
- Untuk environment `testing`, seeding role/permission/user menggunakan `database/seeders/TestingShieldSeeder.php`.
- `DatabaseSeeder` melakukan branching berdasarkan environment.

Contoh pola di `DatabaseSeeder`:

```php
if (app()->environment('testing')) {
    $this->call([
        TestingShieldSeeder::class,
    ]);

    return;
}

$this->call([
    ShieldSeeder::class,
    PostSeeder::class,
]);
```

Kenapa ini penting:

- Perubahan manual pada `ShieldSeeder.php` tidak aman karena akan tertimpa generate berikutnya.
- Test tetap repeatable meski metadata Shield berubah.
- Password/hash test user mengikuti konfigurasi hash di environment testing.
