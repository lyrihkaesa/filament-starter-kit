# Filament Starter Kit

Starter Kit untuk membangun aplikasi berbasis [Laravel](https://laravel.com/) + [Filament](https://filamentphp.com/).  
Tujuannya adalah menyediakan pondasi siap pakai untuk **admin panel modern** dengan praktik terbaik (Action Pattern, custom resource, dsb).

---

## ✨ Fitur yang Tersedia

-   Filament panel dengan ID `app` (bukan default `admin`)
-   User Resource (CRUD user) with Action Pattern
-   Action Pattern (`php artisan make:action`)
-   User, Role & Permision Seeder (default admin user)
-   Custom Locale (APP_LOCALE `id`, APP_FAKER_LOCALE `id_ID`)
-   RBAC or ABAC (Role & Permission) using `bezhansalleh/filament-shield`
-   Impersonating User using `stechstudio/filament-impersonate`
-   API using `laravel/sanctum`

## 🚀 Quick Start

### **Opsi 1: Install Baru dengan Laravel Installer**

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

    Jika menggunakan `Laravel Herd` langsung saja dibrowser [http://filament-starter-kit.test]([http://filament-starter-kit.test)

5.  Login default (automatis input jika `APP_DEBUG=true`):
    -   Email: `admin@example.com`
    -   Password: `password`

> Jika menggunakan **Laravel Herd**, atur `APP_URL=http://filament-starter-kit.test`.  
> Jika pakai `composer dev`, gunakan `APP_URL=http://localhost:8000`.

### **Opsi 2: Manual (Clone Repository)**

1. Clone repository:

    ```bash
    git clone https://github.com/username/filament-starter-kit.git
    cd filament-starter-kit
    ```

2. Install dependencies:

    ```bash
    composer install
    npm install
    npm run build
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
    composer dev
    ```

7. Login default:

    - Email: `admin@example.com`
    - Password: `password`

---

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

---

## 🔍 Static Analysis with Larastan

Project ini menggunakan [Larastan](https://github.com/nunomaduro/larastan) untuk melakukan **static analysis** dan **type checking** di Laravel.

-   **Run Larastan**:

```bash
composer test:types
```

-   **Configuration file**: `phpstan.neon`
-   **Documentation**: lihat [docs/larastan.md](docs/larastan.md)

`phpstan.neon`

```neon
includes:
    - vendor/larastan/larastan/extension.neon
    - vendor/nesbot/carbon/extension.neon

parameters:
    paths:
        - app/
    level: max
```

## 📖 Dokumentasi Lanjutan

[TODO]

---

## 🤝 Kontribusi

[TODO]

---

## 📜 Lisensi

[MIT License](LICENSE)
