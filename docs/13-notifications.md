# Notifications & Background Jobs

Starter kit ini menggunakan sistem **Filament Notifications** yang terintegrasi dengan **Laravel Queue**.

Dokumen ini penting karena banyak developer pemula mengira notifikasi database atau email selalu "langsung jalan". Padahal, dalam aplikasi yang lebih serius, pekerjaan seperti ini sering sebaiknya dipindahkan ke background job agar request utama tetap cepat.

## Mengapa Menggunakan Queue

Beberapa proses memerlukan waktu tambahan, misalnya:

- mengirim email
- menyimpan notifikasi ke database
- memproses file
- menjalankan side effect lain setelah aksi utama berhasil

Dengan menggunakan antrean (queue), aplikasi tetap responsif karena proses tersebut dikerjakan di latar belakang.

## Konfigurasi di Lokal

Secara default di file `.env`:

```env
QUEUE_CONNECTION=database
```

Artinya, semua job akan disimpan di tabel `jobs` di database Anda.

Jika Anda baru belajar queue, anggap ini sebagai "daftar pekerjaan" yang akan diproses terpisah dari request utama.

## Menjalankan Worker

Untuk memproses antrean tersebut, Anda **harus** menjalankan worker di terminal terpisah:

```bash
php artisan queue:work
```

Kalau worker tidak berjalan, job akan tetap masuk ke database tetapi tidak pernah dikerjakan.

## Opsi Tanpa Worker (`sync`)

Jika Anda sedang melakukan pengujian cepat dan tidak ingin menjalankan worker, ubah koneksi menjadi `sync`:

```env
QUEUE_CONNECTION=sync
```

Dengan opsi ini, semua job akan diproses langsung di dalam request yang sama.

### Kapan cocok memakai `sync`

- debugging cepat di local
- eksplorasi flow awal
- saat Anda belum siap menjalankan worker terpisah

### Kapan jangan memakai `sync`

- production
- flow yang melibatkan email atau pekerjaan berat
- aplikasi yang mulai dipakai banyak user

## Contoh Implementasi: Notifikasi Logout

Fitur notifikasi saat logout diimplementasikan di:

- `app/Actions/Profile/LogoutSessionAction.php`
- `app/Actions/Profile/LogoutOtherBrowserSessionsAction.php`

Kode ini menggunakan:

```php
Notification::make()
    ->title(...)
    ->sendToDatabase($user);
```

Method `sendToDatabase()` akan menyimpan notifikasi ke database, dan perilakunya akan mengikuti driver queue yang Anda gunakan.

## Studi Kasus

Bayangkan user menekan tombol "logout dari semua browser lain".

Selain melakukan logout, sistem mungkin juga ingin:

- menghapus session terkait
- menyimpan notifikasi database
- mungkin mengirim email keamanan

Kalau semua dikerjakan langsung di request utama, UX bisa terasa lebih lambat. Dengan queue, proses tambahan bisa dipisahkan dengan lebih rapi.

## Cara Pikir Saya Soal Queue

Saya ingin developer pemula mulai terbiasa dengan gagasan bahwa:

- tidak semua proses harus selesai di request utama
- aplikasi yang responsif sering memisahkan tugas sinkron dan asynchronous
- queue bukan fitur "enterprise only", tetapi alat dasar untuk menjaga UX tetap baik

## Ringkasan

- `database` queue cocok untuk local dan setup sederhana
- `sync` cocok hanya untuk debugging atau percobaan cepat
- worker harus dijalankan jika queue tidak memakai `sync`
- notifikasi dan side effect lain sebaiknya dipikirkan sebagai bagian dari alur background processing
