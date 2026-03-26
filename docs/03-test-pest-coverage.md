# Setup Test Coverage di Windows (Xdebug + Pest)

Dokumentasi ini menjelaskan cara mengaktifkan **code coverage** di Windows untuk project Laravel + Pest menggunakan Xdebug.

Untuk developer pemula, code coverage adalah laporan yang menunjukkan bagian kode mana yang benar-benar tersentuh oleh test. Ini bukan ukuran mutlak kualitas test, tetapi sangat membantu untuk melihat area yang belum diuji sama sekali.

## Kenapa Saya Tetap Menulis Ini

Banyak developer Windows kesulitan di langkah ini karena:

- versi PHP berbeda-beda
- lokasi `php.ini` kadang membingungkan
- file `.dll` Xdebug harus sesuai detail build PHP
- kadang Herd, Laragon, atau PHP manual saling bertabrakan

Supaya onboarding lebih mudah, saya sengaja dokumentasikan langkahnya secara eksplisit.

## 1. Download Xdebug

1. Buka [Xdebug Wizard](https://xdebug.org/wizard).
2. Jalankan perintah berikut lalu salin hasilnya ke wizard:

```bash
php -i
```

3. Download file `.dll` yang sesuai dengan konfigurasi PHP Anda. Anda juga bisa download manual dari [halaman rilis Xdebug](https://xdebug.org/download#releases).

Contoh untuk PHP 8.4 NTS VC17 x64:

```text
php_xdebug-3.4.5-8.4-nts-vs17-x86_64.dll
```

## 2. Letakkan File DLL

Simpan file `.dll` ke folder `ext` sesuai versi PHP yang sedang Anda pakai.

Jika Anda menggunakan Herd, contohnya bisa seperti ini:

```text
C:\Users\<username>\.config\herd\bin\php84\ext\
```

Untuk mempermudah konfigurasi, rename file tersebut menjadi:

```text
php_xdebug.dll
```

## 3. Konfigurasi `php.ini`

Cari tahu lokasi `php.ini` aktif:

```bash
php --ini
```

Kemudian tambahkan atau pastikan baris berikut ada:

```ini
zend_extension=opcache
zend_extension=php_xdebug
xdebug.mode=coverage
```

Catatan:

- `opcache` biasanya memang sudah ada
- `php_xdebug` diletakkan setelah `opcache`
- karena file ada di folder `ext`, Anda tidak perlu menuliskan path lengkap selama PHP membaca folder `ext` yang benar

## 4. Verifikasi Instalasi

Jalankan:

```bash
php -v
```

Jika berhasil, output akan menampilkan informasi Xdebug tanpa warning.

Contoh:

```text
PHP 8.4.x (cli) ...
with Xdebug v3.4.5, Copyright (c) 2002-2025, by Derick Rethans
```

## 5. Jalankan Test Coverage

Gunakan perintah berikut untuk menjalankan test dengan coverage:

```bash
vendor/bin/pest --coverage
```

Jika berhasil, laporan coverage akan muncul tanpa error `No code coverage driver is available`.

## Studi Kasus

Misalnya Anda baru menulis `CreateUserAction` beserta test-nya. Test mungkin sudah lewat, tetapi Anda ingin tahu:

- apakah branch gagal validasi ikut diuji
- apakah proses role assignment sudah tersentuh
- apakah logika notifikasi ikut diuji

Coverage membantu memberi gambaran awal. Kalau Anda melihat file action hanya tercakup 20%, berarti mungkin test yang Anda tulis baru menyentuh happy path saja.

## Error yang Sering Terjadi

### `No code coverage driver is available`

Biasanya berarti:

- Xdebug belum terpasang
- `xdebug.mode=coverage` belum aktif
- PHP CLI yang Anda jalankan bukan PHP yang konfigurasi `php.ini`-nya tadi diubah

### Xdebug sudah terpasang, tapi coverage tetap gagal

Sering terjadi jika:

- ada lebih dari satu instalasi PHP
- terminal memakai PHP berbeda dari Herd
- Anda mengedit `php.ini` yang salah

Untuk memastikan, cek:

```bash
where php
php --ini
php -v
```

## Kesimpulan

Kalau test suite adalah sabuk pengaman, maka coverage adalah dashboard tambahan yang membantu Anda melihat area mana yang belum tersentuh. Bukan tujuan akhir, tetapi sangat berguna saat Anda mulai membangun kebiasaan testing yang lebih serius.
