# Setup Test Coverage di Windows (Xdebug + Pest)

Dokumentasi ini menjelaskan cara mengaktifkan **code coverage** di Windows untuk project Laravel + Pest dengan menggunakan Xdebug.

---

## 1. Download Xdebug

1. Buka [Xdebug Wizard](https://xdebug.org/wizard).
2. Jalankan perintah berikut dan copy hasilnya ke wizard:

    ```bash
    php -i
    ```

3. Download file `.dll` yang sesuai dengan konfigurasi PHP Anda. Atau [download manual](https://xdebug.org/download#releases).
   Contoh untuk PHP 8.4 (NTS, VC17, x64):
    ```
    php_xdebug-3.4.5-8.4-nts-vs17-x86_64.dll
    ```

---

## 2. Letakkan File DLL

-   Simpan file `.dll` ke folder `ext` sesuai versi PHP Herd.
    Contoh path (ubah `php84` sesuai versi PHP yang Anda gunakan):

    ```
    C:\Users\<username>\.config\herd\bin\php84\ext\
    ```

-   Untuk mempermudah konfigurasi, rename file tersebut menjadi:

    ```
    php_xdebug.dll
    ```

---

## 3. Konfigurasi `php.ini`

Cari tahu lokasi `php.ini` dengan:

```bash
php --ini
```

Kemudian tambahkan baris berikut:

```ini
zend_extension=php_xdebug
xdebug.mode=coverage
```

> Catatan: karena file sudah dinamai `php_xdebug.dll` dan diletakkan di folder `ext` milik PHP, Anda tidak perlu menuliskan path lengkap. PHP otomatis mengenali file tersebut di direktori `ext`.

---

## 4. Verifikasi Instalasi

Cek dengan:

```bash
php -v
```

Jika berhasil, akan muncul seperti berikut tanpa warning:

```
PHP 8.4.x (cli) ...
with Xdebug v3.4.5, Copyright (c) 2002-2025, by Derick Rethans
```

---

## 5. Jalankan Test Coverage

Gunakan perintah berikut untuk menjalankan testing dengan coverage:

```bash
vendor/bin/pest --coverage
```

Jika berhasil, laporan coverage akan muncul tanpa error `No code coverage driver is available`.

---

✅ Selesai! Sekarang project ini sudah mendukung **code coverage di Windows dengan Herd + Xdebug**.
