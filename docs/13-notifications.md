# 13 - Notifications & Background Jobs

Starter kit ini menggunakan sistem **Filament Notifications** yang terintegrasi dengan **Laravel Queue**.

## 🚀 Mengapa Menggunakan Queue?

Beberapa proses seperti mengirim email atau menyimpan notifikasi ke database bisa memakan waktu. Dengan menggunakan antrean (Queue), aplikasi tetap responsif karena proses tersebut dikerjakan di latar belakang.

## 🛠 Konfigurasi di Lokal

Secara default di file `.env`:
```env
QUEUE_CONNECTION=database
```

Artinya, semua job akan disimpan di tabel `jobs` di database SQLite Anda.

### Menjalankan Worker
Untuk memproses antrean tersebut, Anda **harus** menjalankan perintah ini di terminal terpisah:
```bash
php artisan queue:work
```

### Opsi Tanpa Worker (Sync)
Jika Anda sedang melakukan pengujian cepat dan tidak ingin menjalankan worker, ubah koneksi menjadi `sync`:
```env
QUEUE_CONNECTION=sync
```
Dengan opsi ini, semua antrean akan diproses secara sinkron (langsung) di dalam request yang sama.

## 🔔 Contoh Implementasi: Notifikasi Logout

Fitur notifikasi saat logout diimplementasikan di:
- `app/Actions/Profile/LogoutSessionAction.php`
- `app/Actions/Profile/LogoutOtherBrowserSessionsAction.php`

Kode ini menggunakan:
```php
Notification::make()
    ->title(...)
    ->sendToDatabase($user);
```
Perintah `sendToDatabase()` secara otomatis akan memasukkan notifikasi ke dalam antrean jika driver queue Anda bukan `sync`.
