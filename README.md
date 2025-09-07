# Filament Starter Kit

Starter Kit untuk membangun aplikasi berbasis [Laravel](https://laravel.com/) + [Filament](https://filamentphp.com/).  
Tujuannya adalah menyediakan pondasi siap pakai untuk **admin panel modern** dengan praktik terbaik (Action Pattern, custom resource, dsb).

---

## 🚀 Quick Start

1. Clone repository ini:

    ```bash
    git clone https://github.com/username/filament-starter-kit.git
    cd filament-starter-kit
    ```

2. Install dependencies:

    ```bash
    composer install
    npm install && npm run build
    ```

3. Salin file `.env`:

    ```bash
    cp .env.example .env
    ```

4. Generate app key:

    ```bash
    php artisan key:generate
    ```

5. Migrasi database & jalankan seeder:

    ```bash
    php artisan migrate --seed
    ```

6. Jalankan server:

    ```bash
    php artisan serve
    ```

> Buka [http://localhost:8000/app](http://localhost:8000/app) untuk mengakses admin panel.  
> Karena saya pakai **Laravel Herd**, maka pada `.env` saya buat `APP_URL=https://filament-starter-kit.test` anda bisa mengubahnya ke `APP_URL=https://localhost:8000` jika anda menjalankan dengan `php artisan serve`.

7. Login dengan

-   Email: `admin@example.com`
-   Password: `password`

---

## ✨ Fitur yang Tersedia

-   ✅ Filament panel dengan ID `app` (bukan `admin`)
-   ✅ User Resource (CRUD user)
-   ✅ Action Pattern (`php artisan make:action`)
-   ✅ User Seeder (default admin user)
-   ✅ Custom Locale (APP_LOCALE `id`, APP_FAKER_LOCALE `id_ID`)
-   ✅ Redirect `/` dan `/login` ke `/app`

## 🧪 Testing

Project ini menggunakan [Pest v4](https://pestphp.com/) sebagai testing framework.

**Menjalankan Seluruh Test**

```bash
php artisan test
```

**atau langsung dengan binary Pest:**

```bash
./vendor/bin/pest
```

**Struktur Test**

-   `tests/Feature` → untuk menguji route, response, dan integrasi antar komponen.
-   `tests/Unit` → untuk menguji business logic kecil (misalnya `Action`).

## 📖 Dokumentasi Lanjutan

[TODO]

---

## 🤝 Kontribusi

[TODO]

---

## 📜 Lisensi

MIT License
