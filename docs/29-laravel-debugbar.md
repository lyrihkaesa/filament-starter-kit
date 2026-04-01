---
slug: 29-laravel-debugbar
sidebar_position: 29
---

# Laravel Debugbar

[Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar) adalah alat bantu debugging yang sangat berguna untuk melihat apa yang terjadi di balik layar aplikasi Laravel Anda. Alat ini menampilkan informasi seperti query database, file view yang dimuat, detail request, session, dan banyak lagi dalam sebuah toolbar di bagian bawah browser.

## Instalasi

Alat ini diinstal sebagai dependensi pengembangan (*development dependency*) agar tidak membebani performa di lingkungan produksi.

```bash
composer require fruitcake/laravel-debugbar --dev
```

Konfigurasi diterbitkan ke `config/debugbar.php`:

```bash
php artisan vendor:publish --provider="Barryvdh\Debugbar\ServiceProvider"
```

## Penggunaan

Debugbar akan otomatis muncul di bagian bawah browser selama variabel lingkungan `APP_DEBUG` bernilai `true` di file `.env`.

### Fitur Utama

- **Queries**: Menampilkan semua query database yang dijalankan pada request saat ini, termasuk waktu eksekusinya. Sangat membantu untuk mendeteksi masalah N+1.
- **Timeline**: Visualisasi waktu proses aplikasi untuk melihat bagian mana yang memakan waktu lama.
- **Models**: Menunjukkan model Eloquent mana saja yang diambil dari database.
- **Messages**: Anda dapat mengirim pesan kustom ke Debugbar untuk debugging cepat:
  ```php
  \Debugbar::info($object);
  \Debugbar::error('Error!');
  \Debugbar::warning('Watch out..');
  \Debugbar::addMessage('Another message', 'mylabel');
  ```
- **Livewire Integration**: Secara otomatis menangkap request dan event Livewire, yang sangat penting saat bekerja dengan Filament.

## Konfigurasi

Anda dapat mengaktifkan atau menonaktifkan Debugbar secara manual di `.env`:

```env
DEBUGBAR_ENABLED=true
```

Secara default, jika `DEBUGBAR_ENABLED` tidak diatur, ia akan mengikuti nilai `APP_DEBUG`.

## Troubleshooting

### Debugbar Tidak Muncul
1. Pastikan `APP_DEBUG=true` di file `.env`.
2. Pastikan file CSS dan JS Debugbar dapat dimuat (cek di console browser).
3. Jalankan `php artisan debugbar:clear` untuk membersihkan storage debugbar.
4. Jika menggunakan API atau response JSON, Debugbar tidak akan muncul secara visual tetapi datanya dikirim melalui header (bisa dilihat di tab Network di DevTools browser atau menggunakan ekstensi browser khusus).
