# Upload Avatar & Konfigurasi Storage Disk

Filament Starter Kit sudah menyediakan fitur upload avatar secara default di halaman Edit Profile. Pengaturan file upload (seperti Avatar) sangat bergantung pada konfigurasi sistem penyimpanan (filesystem) di Laravel. 

Secara default, Laravel dan Filament akan menyimpan file ke direktori lokal (disk `public`), namun untuk penggunaan skala produksi (production) sangat disarankan untuk menggunakan Object Storage seperti **Amazon S3**, **DigitalOcean Spaces**, **MinIO**, atau **Cloudflare Spaces**.

Berikut adalah panduan lengkap cara mengonfigurasi penyimpanan Avatar, baik menggunakan Local Storage maupun S3.

## 1. Menggunakan Local Storage (Default)

Secara default, Filament menggunakan disk `public` untuk mengelola file upload (sesuai dengan konfigurasi `default_filesystem_disk` pada file `config/filament.php` atau env `FILAMENT_FILESYSTEM_DISK`).

### Langkah-langkah:
1. Pastikan Anda telah menjalankan perintah symbolic link agar file di `storage/app/public` dapat diakses dari browser:
   ```bash
   php artisan storage:link
   ```
2. Pastikan file `.env` Anda mengatur koneksi aplikasi dan disk:
   ```dotenv
   APP_URL=http://localhost:8000
   
   FILESYSTEM_DISK=public
   FILAMENT_FILESYSTEM_DISK=public
   ```
   *(Catatan: Sesuaikan `APP_URL` dengan URL project Anda, misalnya menggunakan Laravel Herd `http://filament-starter-kit.test`)*
3. Sekarang setiap kali User mengunggah avatar, file tersebut akan tersimpan di dalam folder `storage/app/public/avatars`.

---

## 2. Menggunakan S3 Compatible Storage (Direkomendasikan untuk Production)

Jika aplikasi Anda ditujukan untuk environment profesional dan Anda ingin menyimpan file di S3 atau layanan yang kompatibel dengan S3 (misal: MinIO, R2, DO Spaces), Anda perlu menginstal package tambahan dan mengatur konfigurasi `.env`.

### Langkah-langkah:
1. Instal package resmi Laravel untuk AWS S3:
   ```bash
   composer require league/flysystem-aws-s3-v3 "^3.0"
   ```
2. Tambahkan atau perbarui konfigurasi disk di file `.env` Anda dengan detail koneksi S3:
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
3. Filament akan secara otomatis membaca pengaturan `FILAMENT_FILESYSTEM_DISK`. Komponen `FileUpload` dan avatar rendering (melalui antarmuka `HasAvatar` di Model `User`) otomatis menyesuaikan dan mengambil URL dari S3.
   
*(Anda bisa melihat implementasinya di `app/Models/User.php` pada method `getFilamentAvatarUrl()` di mana Starter Kit sudah menangani disk dinamis tersebut).*

## Ringkasan
- Starter Kit ini sangat fleksibel dan dapat digunakan oleh **Pemula** dengan langsung menjalankan `php artisan storage:link` (Local Public Disk).
- Untuk **Profesional**, cukup install package `league/flysystem-aws-s3-v3`, masukkan credentials di `.env`, lalu ganti `FILESYSTEM_DISK` ke `s3` tanpa perlu memodifikasi kode apapun.
