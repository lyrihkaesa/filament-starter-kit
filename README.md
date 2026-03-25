# Filament Starter Kit

Starter Kit untuk membangun aplikasi berbasis [Laravel 12](https://laravel.com/) + [Filament v5](https://filamentphp.com/).  
Tujuannya adalah menyediakan pondasi siap pakai untuk **admin panel modern** dengan praktik terbaik (Action Pattern, custom resource, dsb).

---

## ✨ Fitur yang Tersedia

- **Filament v5 Ready**: Dukungan penuh untuk Filament v5 dengan pola `HasSchemas` dan `InteractsWithSchemas`.
- **Panel App**: Filament panel dengan ID `app` (bukan default `admin`).
- **User & Post Resource**: CRUD lengkap untuk User dan Post dengan praktik terbaik.
- **Action Pattern**: Logika bisnis yang terpisah menggunakan Action (`php artisan make:action`).
- **Smoke Testing**: Pengujian otomatis untuk memastikan semua halaman publik dan terautentikasi dapat diakses (`tests/Feature/SmokeTest.php`).
- **RBAC using Filament Shield**: Manajemen Role & Permission yang matang menggunakan `bezhansalleh/filament-shield`.
- **Impersonation**: Fitur untuk masuk sebagai user lain menggunakan `stechstudio/filament-impersonate`.
- **Custom Locale**: Konfigurasi Bahasa Indonesia (`id`) untuk aplikasi dan Faker.
- **API Ready**: Integrasi API menggunakan `laravel/sanctum`.
- **Global Unguard (Local Only)**: Menggunakan `Model::unguard()` saat development (`isLocal()`) demi fleksibilitas, dengan keamanan yang tetap terjaga melalui **Action Pattern**, **Strict Types**, dan **PHPDoc**.

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

    Jika menggunakan `Laravel Herd` langsung saja dibrowser [http://filament-starter-kit.test](http://filament-starter-kit.test)

5.  Login default (automatis input jika `APP_DEBUG=true`):
    - Email: `admin@example.com`
    - Password: `password`

### **Opsi 2: Manual (Clone Repository)**

1. Clone repository:

    ```bash
    git clone https://github.com/lyrihkaesa/filament-starter-kit.git
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

---

## 🛠 Quality & Development Tools

Proyek ini dilengkapi dengan alat penjaminan kualitas untuk menjaga kode tetap bersih dan aman:

| Alat                   | Kegunaan                                                                       | Perintah              |
| :--------------------- | :----------------------------------------------------------------------------- | :-------------------- |
| **🧪 Testing**         | Smoke & Feature Testing dengan [Pest v4](https://pestphp.com/)                 | `php artisan test`    |
| **🔍 Static Analysis** | Analisis tipe statis dengan [Larastan](https://github.com/larastan/larastan)   | `composer test:types` |
| **🎨 Code Style**      | Pemformatan kode otomatis dengan [Laravel Pint](https://laravel.com/docs/pint) | `composer lint`       |
| **🛠 Refactoring**     | Modernisasi kode otomatis dengan [Rector](https://github.com/rectorphp/rector) | `composer refactor`   |

---

## 📖 Dokumentasi Detail

- [00 - Intro](docs/00-intro.md)
- [01 - App Service Provider](docs/01-app-service-provider.md)
- [02 - Action Pattern](docs/02-action-pattern.md)
- [03 - Test Pest Coverage](docs/03-test-pest-coverage.md)
- [04 - Pint Code Style](docs/04-pint-code-style.md)
- [05 - Larastan (Static Analysis)](docs/05-larastan.md)
- [06 - Rector (Refactoring)](docs/06-rector.md)
- [08 - User Resource](docs/08-user-resource.md)
- [10 - AI Coding Guidelines](docs/10-AI-Coding-Guidelines.md)
- [11 - Make Starter Resource](docs/11-make-starter-resource.md)
- [12 - Upload Avatar & S3 Storage](docs/12-upload-avatar.md)
- [13 - Notifications & Background Jobs](docs/13-notifications.md)

---

## 🔔 Background Jobs & Notifications

Beberapa fitur seperti **Notifikasi Logout** menggunakan sistem antrean (Queue) Laravel.

- Secara default di lokal (`.env`), `QUEUE_CONNECTION` diatur ke `database`.
- Agar notifikasi muncul, Anda harus menjalankan worker:
  ```bash
  php artisan queue:work
  ```
- Untuk pengujian cepat tanpa worker, Anda bisa mengubah `.env` menjadi `QUEUE_CONNECTION=sync`.

---

## 📜 Lisensi

[MIT License](LICENSE)
