---
sidebar_position: 0
---

# Main

Starter Kit untuk membangun aplikasi berbasis [Laravel](https://laravel.com/) + [Filament](https://filamentphp.com/).  
Tujuannya adalah menyediakan pondasi siap pakai untuk **admin panel modern** dengan praktik terbaik (Action Pattern, custom resource, dsb).

## Install Baru dengan Laravel Installer

1.  Pastikan [Laravel Installer](https://laravel.com/docs/12.x/installation#installing-php), Jika Anda menggunakan **Laravel Herd** otomatis Anda sudah install `Laravel Installer`:

    Check `Laravel Installer` terpasang:

    ```bash
    laravel --version
    ```

    <details>
      <summary><strong>Panduan menginstal Laravel Installer</strong></summary>
      Jika Anda sudah menginstal `PHP` dan `Composer`, Anda dapat menginstal `Laravel Installer` melalui Composer:

    ```bash
    composer global require laravel/installer
    ```

    </details>

2.  Buat project baru langsung dari starter kit:

    ```bash
    laravel new my-app --using=lyrihkaesa/filament-starter-kit
    cd my-app
    ```

3.  Jalankan perintah dibawah ini jika ada script saat create project ada yang gagal dimuat:

    ```bash
    composer install
    npm install
    npm run build
    cp .env.example .env
    php artisan migrate --seed
    php artisan key:generate
    ```

4.  Jalankan server:

    ```bash
    composer dev
    ```

    Jika menggunakan `Laravel Herd` langsung saja dibrowser [http://filament-starter-kit.test](http://filament-starter-kit.test)

5.  Login default (automatis input jika `APP_DEBUG=true`):
    -   Email: `admin@example.com`
    -   Password: `password`

> Jika menggunakan **Laravel Herd**, atur `APP_URL=http://filament-starter-kit.test`.  
> Jika pakai `composer dev`, gunakan `APP_URL=http://localhost:8000`.
