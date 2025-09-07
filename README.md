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

## 🎨 Code Style

Project ini menggunakan [Laravel Pint](https://laravel.com/docs/pint) untuk menjaga konsistensi kode PHP.  
Semua aturan custom Pint dan panduan format dapat dilihat di [docs/code-style.md](docs/code-style.md).

Linting dapat dijalankan dengan:

```bash
composer lint
```

> Tips: Untuk pengguna Visual Studio Code, gunakan ekstensi [Laravel Pint](https://marketplace.visualstudio.com/items?itemName=open-southeners.laravel-pint) sebagai default formatter.

Oke Kaesa, kita buat versi **ringkas di README.md** yang hanya menyinggung Rector dan link ke `docs/rector.md` supaya contributor tahu di mana melihat panduan lengkap. Contohnya:

## 🛠 Code Refactoring with Rector

Project ini menggunakan [Rector](https://github.com/rectorphp/rector) untuk otomatis melakukan refactoring, meningkatkan kualitas kode, dan menambahkan type declarations.  
Kami juga menggunakan package [driftingly/rector-laravel](https://github.com/driftingly/rector-laravel) untuk aturan khusus Laravel.

### Menjalankan Rector

-   **Dry-run (cek tanpa perubahan)**:

```bash
composer test:refactor
```

-   **Apply fixes**:

```bash
composer refactor
```

> ⚠ Selalu commit perubahan sebelum menjalankan Rector untuk memudahkan rollback.

### Dokumentasi Lengkap

Lihat panduan lengkap konfigurasi, preset, rules, dan cara remove driftingly/rector-laravel di: [docs/rector.md](docs/rector.md)

## 📖 Dokumentasi Lanjutan

[TODO]

---

## 🤝 Kontribusi

[TODO]

---

## 📜 Lisensi

MIT License
