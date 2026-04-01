# Database Testing Options

Starter kit ini mendukung dua cara utama untuk menjalankan pengujian database: **PostgreSQL** (disarankan untuk identik dengan produksi) dan **SQLite In-Memory** (paling cepat).

## 1. PostgreSQL (Default Environment)

Direkomendasikan menggunakan PostgreSQL agar lingkungan pengujian sedekat mungkin dengan lingkungan produksi.

### Persiapan
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

### Cara Menjalankan
Cukup jalankan perintah:
```bash
php artisan test
```
Laravel akan otomatis memuat `.env.testing` sebagai prioritas.

---

## 2. SQLite (Fast Testing)

Gunakan SQLite jika Anda ingin menjalankan pengujian dengan sangat cepat tanpa perlu setup database eksternal.

### Persiapan
1. Salin file `.env.sqlite.testing.example` menjadi `.env.sqlite.testing`:
   ```bash
   cp .env.sqlite.testing.example .env.sqlite.testing
   ```

### Cara Menjalankan
Gunakan flag `--env` untuk memberitahu Laravel agar menggunakan file konfigurasi SQLite:
```bash
php artisan test --env=sqlite.testing
```

---

## 3. Fallback (phpunit.xml)

Jika Anda tidak memiliki file `.env.testing` atau `.env.sqlite.testing`, Laravel akan menggunakan konfigurasi yang ada di dalam `phpunit.xml` sebagai cadangan. Secara default, fallback ini menggunakan **SQLite In-Memory**.

---

## Memastikan Database yang Digunakan

Gunakan perintah ini untuk memverifikasi database mana yang sedang aktif di lingkungan pengujian:

```bash
# Untuk melihat koneksi default di environment testing:
php artisan tinker --env=testing --execute="dump('Koneksi: ' . config('database.default'))"

# Untuk melihat koneksi saat menggunakan sqlite environment:
php artisan tinker --env=sqlite.testing --execute="dump('Koneksi: ' . config('database.default'))"
```
