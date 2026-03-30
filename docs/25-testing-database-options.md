# Database Testing Options

Starter kit ini mendukung dua cara utama untuk menjalankan pengujian database: **SQLite In-Memory** (cepat & mudah) dan **PostgreSQL** (disarankan untuk produksi).

## 1. SQLite In-Memory (Default)

Secara default, pengujian akan menggunakan basis data SQLite di memori. Ini adalah cara tercepat dan tidak memerlukan konfigurasi apa pun karena semuanya terjadi di RAM saat pengujian berjalan.

### Konfigurasi
Pengaturan ini ada di file `phpunit.xml`:
```xml
<php>
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value=":memory:"/>
</php>
```

### Cara Menjalankan
Cukup jalankan perintah:
```bash
php artisan test
```

---

## 2. PostgreSQL (Production-like Testing)

Jika Anda ingin menguji aplikasi di lingkungan yang identik dengan produksi, Anda bisa menggunakan PostgreSQL.

### Persiapan
1.  Salin file `.env.testing.example` menjadi `.env.testing`:
    ```bash
    cp .env.testing.example .env.testing
    ```
2.  Buat database khusus untuk pengujian di PostgreSQL Anda, misalnya: `filament_starter_kit_test`.
3.  Pastikan kredensial di file `.env.testing` sudah benar:
    ```env
    DB_CONNECTION=pgsql
    DB_HOST=127.0.0.1
    DB_PORT=5432
    DB_DATABASE=filament_starter_kit_test
    DB_USERNAME=postgres
    DB_PASSWORD=
    ```

### Cara Mengaktifkan
Agar `phpunit.xml` memprioritaskan konfigurasi dari `.env.testing`, Anda harus mengomentari baris `DB_DATABASE` di `phpunit.xml`:

```xml
<php>
    <env name="DB_CONNECTION" value="sqlite"/>
    <!-- <env name="DB_DATABASE" value=":memory:"/> -->
</php>
```

Dengan mengomentari baris di atas, Laravel akan secara otomatis mengambil nilai dari file `.env.testing` jika file tersebut ada.

---

## Cara Mengetahui Database yang Sedang Digunakan

Anda dapat memastikan koneksi basis data mana yang sedang aktif di lingkungan pengujian dengan menggunakan perintah **Artisan Tinker** atau **About** yang diarahkan ke *environment* `testing`.

### 1. Menggunakan Artisan Tinker (Paling Akurat)
Jalankan perintah ini untuk melihat koneksi utama yang sedang aktif:
```bash
php artisan tinker --env=testing --execute="dump('Koneksi: ' . config('database.default'), 'Database: ' . config('database.connections.' . config('database.default') . '.database'))"
```
*   Jika muncul `"Koneksi: sqlite"` dan `"Database: :memory:"`, berarti Anda menggunakan **SQLite Memory**.
*   Jika muncul `"Koneksi: pgsql"` dan `"Database: filament_starter_kit_test"`, berarti Anda menggunakan **PostgreSQL**.

### 2. Menggunakan Perintah About
Laravel menyediakan ringkasan konfigurasi yang sangat rapi. Tambahkan flag `--env=testing` untuk melihat pengaturan khusus pengujian:
```bash
php artisan about --env=testing
```
Lihat pada bagian **Database** untuk memastikan `Connection` dan `Database` yang digunakan.

---

## Troubleshooting

### File `filament_starter_kit_test_*` muncul di root
Jika Anda menjalankan tes secara paralel menggunakan perintah `php artisan test --parallel`, Pest/PHPUnit mungkin akan membuat file-file database SQLite sementara. Anda bisa menghapusnya dengan aman jika pengujian sudah selesai:
```bash
del filament_starter_kit_test*
```
atau (Linux/Mac):
```bash
rm filament_starter_kit_test*
```
