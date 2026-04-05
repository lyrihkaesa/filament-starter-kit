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

## Tutorial: Mendeteksi & Memperbaiki N+1 Query

N+1 query adalah masalah performa paling umum di Laravel. Masalah ini terjadi ketika Anda mengambil daftar data (misal: 10 post), lalu di dalam loop Anda memanggil relasi (misal: `$post->user->name`). Laravel akan melakukan 1 query untuk mengambil 10 post, DAN 10 query tambahan untuk mengambil user masing-masing post. Total = 11 query (N+1).

### Cara Mendeteksi dengan Debugbar

1. Buka halaman yang lambat atau yang ingin Anda cek.
2. Lihat toolbar Debugbar di bagian bawah, klik tab **Queries**.
3. Cari pola query yang berulang-ulang terhadap tabel yang sama dengan hanya perbedaan ID.
   - Contoh: `SELECT * FROM users WHERE id = 1`, `SELECT * FROM users WHERE id = 2`, ...
4. Debugbar biasanya akan memberi peringatan (background merah atau ikon peringatan) jika mendeteksi jumlah query yang tidak wajar.

### Cara Memperbaiki

Solusinya adalah menggunakan **Eager Loading**.

**Kasu :** Menampilkan daftar Post dengan Nama Usernya.

❌ **Salah (N+1):**
```php
$posts = Post::all(); // 1 query

foreach ($posts as $post) {
    echo $post->user->name; // ini memicu N query tambahan!
}
```

✅ **Benar (Eager Loading):**
```php
$posts = Post::with('user')->get(); // Hanya 2 query total

foreach ($posts as $post) {
    echo $post->user->name; // Data sudah siap di memori
}
```

Jika Anda sudah terlanjur memiliki instance model dan ingin memuat relasinya nanti (Lazy Eager Loading):

```php
$user->load('posts');
```

Setelah menerapkan `with()` atau `load()`, cek kembali tab **Queries** di Debugbar. Jumlah query seharusnya berkurang drastis menjadi hanya 2 atau beberapa saja.

---


Anda dapat menonaktifkan Debugbar secara manual di `.env`:

```env
DEBUGBAR_ENABLED=true
```

## Keamanan

> **Peringatan:** Gunakan Debugbar **hanya di lingkungan pengembangan**. Jangan aktifkan di website yang dapat diakses publik karena dapat membocorkan informasi sensitif dari request yang tersimpan (seperti query database, data session, dll).
