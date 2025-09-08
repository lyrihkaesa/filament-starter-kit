---
sidebar_position: 0
---

# Main

Starter Kit untuk membangun aplikasi berbasis [Laravel](https://laravel.com/) + [Filament](https://filamentphp.com/).  
Tujuannya adalah menyediakan pondasi siap pakai untuk **admin panel modern** dengan praktik terbaik (Action Pattern, custom resource, dsb).

## Install Baru dengan Laravel Installer

1. Pastikan **Laravel Installer** sudah ada (Apps ini include di Apps Laravel Herd):

    ```bash
    laravel --version
    ```

2. Buat project baru langsung dari starter kit:

    ```bash
    laravel new my-app --using=lyrihkaesa/filament-starter-kit
    cd my-app
    ```

    atau anda bisa sesuai versi

    ```bash
    laravel new my-app --using=lyrihkaesa/filament-starter-kit:^1.0
    cd my-app
    ```

3. Jalankan migrasi & seeder (jika gagal migrasi dan seeding):

    ```bash
    php artisan migrate:fresh --seed
    ```

4. Jalankan server:

    ```bash
    php artisan serve
    ```

5. Login default sama seperti cara manual:

    - Email: `admin@example.com`
    - Password: `password`
