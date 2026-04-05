# Laravel Backup dengan Spatie

Starter kit ini sudah terintegrasi dengan [`spatie/laravel-backup`](https://github.com/spatie/laravel-backup) untuk membuat backup file aplikasi dan database secara otomatis ke:

- filesystem lokal pada disk `backups`
- filesystem `s3`

Konfigurasi ini sekarang mengikuti pendekatan default dokumentasi Spatie untuk v10: disk lokal backup boleh dipisah, tetapi S3 cukup memakai disk `s3` biasa tanpa filesystem tambahan.

Secara bawaan, konfigurasi backup disimpan di [config/backup.php](/E:/Projects/Laravel/filament-starter-kit/config/backup.php) dan lokasi backup lokal ada di [config/filesystems.php](/E:/Projects/Laravel/filament-starter-kit/config/filesystems.php).

## Ringkasan Default

- Frekuensi backup: harian (`daily`)
- Jam backup: `01:00`
- Cleanup backup lama: `01:30`
- Monitoring kesehatan backup: `06:00`
- Retensi: hapus backup yang lebih tua dari 30 hari
- Arsip zip terenkripsi dengan password
- Disk tujuan: `backups,s3`
- Notifikasi: email

Jika Anda ingin tetap memakai pola harian, cukup isi `.env` lalu aktifkan Laravel Scheduler di server.

## 1. Konfigurasi `.env`

Tambahkan atau sesuaikan nilai berikut di file `.env`:

```env
BACKUP_DESTINATION_DISKS=backups,s3
BACKUP_NOTIFICATION_EMAIL=hello@example.com
BACKUP_ARCHIVE_PASSWORD=change-this-backup-password
BACKUP_ENCRYPTION=aes256
BACKUP_SCHEDULE_FREQUENCY=daily
BACKUP_SCHEDULE_TIME=01:00
BACKUP_CLEANUP_TIME=01:30
BACKUP_MONITOR_TIME=06:00
BACKUP_MONITOR_MAX_AGE_DAYS=2
BACKUP_MAX_STORAGE_MB=10240
BACKUP_KEEP_ALL_DAYS=30
BACKUP_KEEP_DAILY_DAYS=0
BACKUP_KEEP_WEEKLY_WEEKS=0
BACKUP_KEEP_MONTHLY_MONTHS=0
BACKUP_KEEP_YEARLY_YEARS=0
```

Penjelasan penting:

- `BACKUP_DESTINATION_DISKS=backups,s3` artinya satu file backup akan disalin ke local dan S3.
- `BACKUP_ARCHIVE_PASSWORD` wajib diisi jika Anda ingin file zip benar-benar terenkripsi.
- `BACKUP_ENCRYPTION=aes256` membuat isi zip backup dienkripsi dengan AES-256.
- `BACKUP_NOTIFICATION_EMAIL` adalah email tujuan notifikasi berhasil/gagal backup.
- `BACKUP_KEEP_ALL_DAYS=30` berarti backup lebih tua dari 30 hari akan dibersihkan.
- Nilai `0` pada daily/weekly/monthly/yearly retention membuat backup tidak disimpan melewati 30 hari.

## 2. Lokasi Backup Lokal

Backup lokal disimpan pada disk `backups` dengan root:

```text
storage/app/backups
```

Ini sengaja dipisah dari disk `local` biasa supaya file backup tidak bercampur dengan file aplikasi lain.

## 3. Konfigurasi S3

Backup remote memakai disk `s3` bawaan Laravel. Isi variabel berikut:

```env
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=your-backup-bucket
AWS_URL=
AWS_ENDPOINT=
AWS_USE_PATH_STYLE_ENDPOINT=false
```

Catatan:

- Bisa dipakai untuk AWS S3 asli.
- Bisa juga dipakai untuk layanan kompatibel S3 seperti MinIO, Cloudflare R2, DigitalOcean Spaces, dan lainnya dengan menyesuaikan `AWS_ENDPOINT`.
- Karena memakai disk `s3` default, backup akan masuk ke root bucket, bukan ke folder `backups/` khusus.
- Jika S3 belum siap, sementara ubah `BACKUP_DESTINATION_DISKS=backups` agar backup lokal tetap jalan.

### Lokasi Backup di S3

Dengan konfigurasi default package, backup akan tersimpan di bucket dengan pola seperti ini:

```text
s3://your-bucket/{APP_NAME}/2026-04-01-01-00-00.zip
```

Artinya:

- nama folder level pertama mengikuti `APP_NAME`
- file zip backup ada di dalam folder itu
- jika `APP_NAME="filament-starter-kit"`, contoh lokasinya menjadi:

```text
s3://your-bucket/filament-starter-kit/2026-04-01-01-00-00.zip
```

Kalau suatu hari Anda ingin backup masuk ke folder `backups/` juga, itu bisa dilakukan, tetapi perlu custom disk atau custom path lagi. Untuk saat ini saya kembalikan ke pola default Spatie sesuai permintaan Anda.

## 3a. Enkripsi Arsip Backup

Arsip zip backup sekarang disiapkan untuk terenkripsi. Agar aktif, isi password berikut di `.env`:

```env
BACKUP_ARCHIVE_PASSWORD=ganti-dengan-password-backup-yang-kuat
BACKUP_ENCRYPTION=aes256
```

Catatan penting:

- Jika `BACKUP_ARCHIVE_PASSWORD` kosong, package tidak akan mengenkripsi zip.
- Gunakan password yang berbeda dari password database atau password akun admin.
- Simpan password backup ini di password manager.
- Saat restore nanti, Anda membutuhkan password ini untuk membuka arsip backup.

## 4. Support SQLite

Jika aplikasi memakai SQLite, pastikan `.env` seperti ini:

```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

Untuk proses dump SQLite, package membutuhkan binary `sqlite3` pada server.

Jika `sqlite3` tidak ada di `PATH`, isi:

```env
SQLITE_DUMP_BINARY_PATH=/usr/bin/
```

Contoh cek di VPS:

```bash
which sqlite3
```

Lalu jalankan backup manual:

```bash
php artisan backup:run
```

## 5. Support PostgreSQL

Jika aplikasi memakai PostgreSQL, pastikan `.env` seperti ini:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=filament_starter_kit
DB_USERNAME=postgres
DB_PASSWORD=secret
```

Untuk PostgreSQL, package membutuhkan binary `pg_dump` pada server.

Jika `pg_dump` tidak ada di `PATH`, isi:

```env
PG_DUMP_BINARY_PATH=/usr/bin/
```

Contoh cek di VPS:

```bash
which pg_dump
```

Lalu jalankan backup manual:

```bash
php artisan backup:run
```

## 6. Menjalankan Backup Manual

Perintah yang paling sering dipakai:

```bash
php artisan backup:run
php artisan backup:list
php artisan backup:monitor
php artisan backup:clean
```

Kegunaan:

- `backup:run` membuat backup baru
- `backup:list` melihat daftar backup yang tersedia
- `backup:monitor` mengecek apakah backup masih sehat
- `backup:clean` menghapus backup lama sesuai retensi

## 7. Notifikasi Email dengan Mailtrap

Untuk uji notifikasi email, Anda bisa memakai Mailtrap dengan mailer SMTP Laravel yang sudah ada.

Contoh konfigurasi:

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_FROM_ADDRESS="backup@example.com"
MAIL_FROM_NAME="${APP_NAME}"

BACKUP_NOTIFICATION_EMAIL=ops@example.com
BACKUP_ARCHIVE_PASSWORD=ganti-dengan-password-backup-yang-kuat
```

Cara uji:

1. Simpan konfigurasi Mailtrap di `.env`.
2. Jalankan `php artisan config:clear`.
3. Jalankan `php artisan backup:run`.
4. Cek inbox Mailtrap Anda.

Yang akan Anda terima:

- email sukses jika backup berhasil
- email gagal jika backup atau upload ke S3 gagal
- email cleanup dan health monitor sesuai event

Jika Anda ingin menguji notifikasi tanpa menunggu scheduler, cukup pakai `php artisan backup:run` dan `php artisan backup:monitor`.

## 8. Scheduler Harian, Mingguan, Bulanan

Starter kit ini memakai scheduler Laravel dari [bootstrap/app.php](/E:/Projects/Laravel/filament-starter-kit/bootstrap/app.php). Frekuensinya diatur lewat `.env`.

### Harian

```env
BACKUP_SCHEDULE_FREQUENCY=daily
BACKUP_SCHEDULE_TIME=01:00
BACKUP_MONITOR_MAX_AGE_DAYS=2
```

Ini opsi yang direkomendasikan dan sesuai preferensi Anda.

### Mingguan

```env
BACKUP_SCHEDULE_FREQUENCY=weekly
BACKUP_SCHEDULE_WEEKLY_DAY=1
BACKUP_SCHEDULE_TIME=01:00
BACKUP_MONITOR_MAX_AGE_DAYS=8
```

Keterangan `BACKUP_SCHEDULE_WEEKLY_DAY`:

- `0` = Minggu
- `1` = Senin
- `2` = Selasa
- `3` = Rabu
- `4` = Kamis
- `5` = Jumat
- `6` = Sabtu

### Bulanan

```env
BACKUP_SCHEDULE_FREQUENCY=monthly
BACKUP_SCHEDULE_MONTHLY_DAY=1
BACKUP_SCHEDULE_TIME=01:00
BACKUP_MONITOR_MAX_AGE_DAYS=32
```

Untuk bulanan, `BACKUP_SCHEDULE_MONTHLY_DAY=1` berarti berjalan setiap tanggal 1.

## 9. Menjalankan Scheduler di VPS

Backup otomatis tidak akan jalan kalau scheduler Laravel belum dijalankan di server.

Tambahkan cron berikut di VPS:

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

Kalau Anda deploy ke server Linux dengan Supervisor atau systemd, yang penting `php artisan schedule:run` berjalan tiap menit.

## 10. Menghapus Backup Lama yang Tidak Terpakai

Kebutuhan Anda adalah backup lebih dari 30 hari tidak terlalu berguna. Konfigurasi default project ini sudah disiapkan untuk itu:

```env
BACKUP_KEEP_ALL_DAYS=30
BACKUP_KEEP_DAILY_DAYS=0
BACKUP_KEEP_WEEKLY_WEEKS=0
BACKUP_KEEP_MONTHLY_MONTHS=0
BACKUP_KEEP_YEARLY_YEARS=0
```

Artinya:

- semua backup disimpan maksimal 30 hari
- setelah lewat 30 hari, backup lama dihapus
- backup terbaru tetap dipertahankan oleh strategi cleanup Spatie

Untuk menjalankan pembersihan manual di VPS:

```bash
php artisan backup:clean
```

Untuk melihat hasil pembersihan:

```bash
php artisan backup:list
```

## 11. Rekomendasi Operasional

- Untuk kebutuhan normal, pakai backup harian.
- Simpan backup ke `backups` dan `s3` sekaligus agar ada salinan lokal dan off-site.
- Gunakan bucket private.
- Pahami bahwa dengan disk `s3` default, backup tersimpan di root bucket pada folder `APP_NAME`.
- Selalu isi `BACKUP_ARCHIVE_PASSWORD` agar arsip zip terenkripsi.
- Pastikan `pg_dump` atau `sqlite3` tersedia di server sesuai database yang dipakai.
- Uji notifikasi dengan Mailtrap sebelum memakai SMTP produksi.
- Biarkan `backup:clean` tetap aktif agar storage VPS tidak penuh.

## 12. Checklist Cepat

1. Install binary database sesuai kebutuhan: `sqlite3` atau `pg_dump`.
2. Isi `.env` untuk database, S3, Mailtrap, dan password enkripsi backup.
3. Jalankan `php artisan config:clear`.
4. Uji `php artisan backup:run`.
5. Uji `php artisan backup:list`.
6. Uji `php artisan backup:monitor`.
7. Aktifkan cron `php artisan schedule:run` di VPS.
