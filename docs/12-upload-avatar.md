# Upload Avatar & Konfigurasi Storage Disk

Filament Starter Kit sudah menyediakan fitur upload avatar secara default di halaman Edit Profile. Pengaturan file upload seperti avatar sangat bergantung pada konfigurasi filesystem Laravel.

Secara default, Laravel dan Filament akan menyimpan file ke direktori lokal melalui disk `public`. Tetapi untuk production, saya sangat menyarankan memakai object storage seperti **Amazon S3**, **DigitalOcean Spaces**, **MinIO**, atau **Cloudflare Spaces**.

Dokumen ini sengaja dibuat detail karena masalah upload file sering terlihat sederhana di awal, tetapi cepat jadi sumber bug saat aplikasi mulai dipakai banyak user.

## Kenapa Storage Perlu Dipikirkan dari Awal

Developer pemula sering fokus ke "upload berhasil atau tidak". Padahal dalam production, ada pertanyaan lain:

- apakah file bisa diakses dari browser
- apakah URL file konsisten
- apakah storage server aman untuk diskalakan
- apakah file tetap aman saat deploy ulang

Karena itu, starter kit ini sudah menyiapkan flow yang fleksibel.

## 1. Menggunakan Local Storage (Default)

Secara default, Filament menggunakan disk `public` untuk mengelola file upload sesuai konfigurasi `FILAMENT_FILESYSTEM_DISK`.

### Langkah-langkah

1. Pastikan Anda sudah membuat symbolic link:

```bash
php artisan storage:link
```

2. Pastikan file `.env` mengatur koneksi aplikasi dan disk dengan benar:

```dotenv
APP_URL=http://localhost:8000

FILESYSTEM_DISK=public
FILAMENT_FILESYSTEM_DISK=public
```

Catatan: sesuaikan `APP_URL` dengan URL project Anda, misalnya jika menggunakan Laravel Herd:

```dotenv
APP_URL=http://filament-starter-kit.test
```

3. Setelah itu, file avatar akan disimpan di:

```text
storage/app/public/avatars
```

### Cocok untuk siapa

Mode ini cocok untuk:

- local development
- demo kecil
- deployment sederhana yang tidak membutuhkan object storage

## 2. Menggunakan S3 Compatible Storage (Direkomendasikan untuk Production)

Jika aplikasi Anda ditujukan untuk production, object storage biasanya jauh lebih aman dan lebih fleksibel untuk jangka panjang.

### Langkah-langkah

1. Instal package resmi Laravel untuk AWS S3:

```bash
composer require league/flysystem-aws-s3-v3 "^3.0"
```

2. Tambahkan atau perbarui konfigurasi `.env`:

```dotenv
FILESYSTEM_DISK=s3
FILAMENT_FILESYSTEM_DISK=s3

AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your_bucket_name
AWS_USE_PATH_STYLE_ENDPOINT=false

# AWS_ENDPOINT=https://sgp1.digitaloceanspaces.com # Jika menggunakan DigitalOcean / MinIO dll.
```

3. Filament akan otomatis membaca pengaturan `FILAMENT_FILESYSTEM_DISK`.

Komponen `FileUpload` dan avatar rendering melalui antarmuka `HasAvatar` di model `User` akan menyesuaikan disk yang aktif.

Anda bisa melihat implementasinya di model `User`, khususnya pada method `getFilamentAvatarUrl()`.

## Studi Kasus

### Kasus 1: Project internal kecil

Anda sedang membuat admin panel untuk tim internal dengan jumlah user terbatas. Untuk kasus ini:

- local disk sering sudah cukup
- setup lebih sederhana
- tidak perlu biaya object storage tambahan

### Kasus 2: SaaS atau aplikasi production

Kalau aplikasi mulai dipakai banyak user dan upload avatar menjadi fitur umum:

- object storage lebih aman
- file tidak hilang saat server di-redeploy
- URL file lebih mudah diatur
- lebih siap untuk scale

## Cara Pikir Saya Soal Upload File

Saya lebih suka menyiapkan struktur yang membuat perpindahan dari local ke S3 tidak memaksa refactor besar. Itu sebabnya saya tidak ingin logic URL avatar hardcoded ke satu disk tertentu.

Kalau environment berubah, idealnya kita cukup ubah konfigurasi, bukan menulis ulang business logic.

## Ringkasan

- untuk pemula, mulai dari `public` disk dan jalankan `php artisan storage:link`
- untuk production, pertimbangkan S3-compatible storage sejak awal
- starter kit ini sudah dirancang supaya disk dapat diganti lewat konfigurasi tanpa perlu banyak ubahan kode
