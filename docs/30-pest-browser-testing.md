---
slug: 30-pest-browser-testing
sidebar_position: 30
---

# Pest Browser Testing

Starter kit ini sekarang mendukung pengujian browser menggunakan [Pest 4](https://pestphp.com/docs/browser-testing). Pengujian browser memungkinkan Anda mensimulasikan interaksi pengguna asli dalam browser (klik, ketik, scroll, dll.).

## Instalasi

Plugin berikut telah diinstal untuk mendukung fungsionalitas ini:

```bash
composer require pestphp/pest-plugin-laravel pestphp/pest-plugin-browser --dev
```

## Penggunaan Dasar

Tes browser biasanya diletakkan di dalam direktori `tests/Browser/`.

### Contoh Tes Browser

Berikut adalah contoh tes sederhana untuk memastikan halaman login dapat diakses dan tidak memiliki error JavaScript:

```php
use function Pest\Laravel\visit;

it('has no javascript errors on the login page', function () {
    visit('/admin/login')
        ->assertSee('Sign in')
        ->assertNoJavaScriptErrors();
});
```

### Smoke Testing

Anda dapat dengan cepat memvalidasi banyak halaman sekaligus:

```php
it('has no console logs on main pages', function () {
    $pages = visit(['/admin/login', '/admin/register']);

    $pages->assertNoJavaScriptErrors()
          ->assertNoConsoleLogs();
});
```

### Interaksi Pengguna

Anda dapat mensimulasikan interaksi kompleks:

```php
it('can fill the login form', function () {
    visit('/admin/login')
        ->fill('email', 'admin@example.com')
        ->fill('password', 'password')
        ->click('button[type="submit"]')
        ->assertPathIs('/admin');
});
```

## Menjalankan Tes

Untuk menjalankan semua tes termasuk tes browser:

```bash
php artisan test --compact
```

Atau filter berdasarkan file:

```bash
php artisan test --compact --filter=Browser
```

## Tips Debugging

Jika tes gagal, Anda dapat menggunakan metode berikut untuk membantu debugging:

- `->screenshot('nama-file')`: Mengambil gambar browser saat itu.
- `->pause(2000)`: Menghentikan eksekusi tes selama beberapa milidetik.
- `->consoleLogs()`: Melihat log console browser.

> **Catatan:** Pastikan environment testing Anda sudah terkonfigurasi dengan benar (misalnya menggunakan database sqlite di memory atau database testing terpisah).
