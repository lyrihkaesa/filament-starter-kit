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

## ⚙️ Development Tools

| Keterangan                                      | Package                                                                                                                   | Command                                                                          |
| ----------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------- |
| [🧪 **Testing**](docs/03-test-pest-coverage.md) | [Pest v4](https://pestphp.com/)                                                                                           | `php artisan test` / `./vendor/bin/pest`                                         |
| [🎨 **Code Style**](docs/04-pint-code-style.md) | [Laravel Pint](https://laravel.com/docs/pint)                                                                             | `composer lint` / `./vendor/bin/pint`                                            |
| [🛠 **Refactoring**](docs/06-rector.md)          | [Rector](https://github.com/rectorphp/rector) + [driftingly/rector-laravel](https://github.com/driftingly/rector-laravel) | `composer test:refactor` (dry-run) / `composer refactor` / `./vendor/bin/rector` |
| [🔍 **Static Analysis**](docs/05-larastan.md)   | [Larastan](https://github.com/nunomaduro/larastan)                                                                        | `composer test:types` / `./vendor/bin/phpstan`                                   |

---

## 🤝 Kontribusi

[TODO]

---

## 📜 Lisensi

[MIT License](LICENSE)
