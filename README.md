<p align="center">
    <img src="public/images/logo-128x128.png" width="128" height="128" alt="Filament Starter Kit Logo">
</p>

# Filament Starter Kit

[![Latest Version on Packagist](https://img.shields.io/packagist/v/lyrihkaesa/filament-starter-kit.svg?style=flat-square)](https://packagist.org/packages/lyrihkaesa/filament-starter-kit)
[![Total Downloads](https://img.shields.io/packagist/dt/lyrihkaesa/filament-starter-kit.svg?style=flat-square)](https://packagist.org/packages/lyrihkaesa/filament-starter-kit)
[![PHP Version](https://img.shields.io/badge/php-%5E8.3-blue.svg?style=flat-square)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/laravel-13.x-red.svg?style=flat-square)](https://laravel.com)
[![License](https://img.shields.io/badge/license-MIT-green.svg?style=flat-square)](LICENSE)

Starter kit untuk membangun admin panel dengan **Laravel 13**, **Filament 5**, **Livewire 4**, dan **Pest 5**.

Fokus utamanya adalah struktur code yang rapi, maintainable, dan nyaman untuk development jangka panjang.

## Kenapa Pakai Starter Kit Ini

- Arsitektur jelas: mutation lewat `Action Pattern` (`handle()`), query lewat scope/custom builder.
- API siap pakai dengan Sanctum dan struktur endpoint `api/v1`.
- Role & permission sudah siap via Filament Shield.
- UUID-first untuk tabel baru.
- Tooling kualitas code sudah terpasang: Pest, Pint, Larastan, Rector.
- Cocok untuk workflow AI-assisted coding karena aturan project dan docs sudah terstruktur.

## Quick Start

### 1) Buat project

```bash
laravel new my-app --using=lyrihkaesa/filament-starter-kit
cd my-app
```

### 2) Install dependency dan setup awal

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
```

### 3) Jalankan development server

```bash
composer run dev
```

### Akun default

- Email: `superadmin@example.com`
- Password: `password`

## Command Harian

### Development

```bash
composer run dev
php artisan serve
npm run dev
```

### Testing & Quality

```bash
composer test
composer test-full
composer test:types
composer test:lint
composer test:refactor
```

## Prinsip Arsitektur

- **Mutations (create/update/delete):** wajib di `app/Actions`.
- **Queries (read):**
  - mulai dari local scope `#[Scope]` di Model,
  - pindah ke `app/Models/Builders/*Builder.php` jika scope sudah banyak atau query kompleks.
- **External integrations:** di `app/Services`.
- **UI layer (Filament/Livewire/Controller):** tipis, hanya orkestrasi.

## Dokumentasi

Dokumentasi lengkap ada di folder [`docs`](./docs) dan versi online:

- [Dokumentasi Online](https://kaesa.charapon.my.id/filament-starter-kit)

### Mulai dari sini

- [00 - Intro](./docs/00-intro.md)
- [01 - Architecture Overview](./docs/01-architecture-overview.md)
- [02 - Action Pattern](./docs/02-action-pattern.md)
- [03 - Query Pattern](./docs/03-query-pattern.md)
- [04 - Policy and Action Integration](./docs/04-policy-and-action-integration.md)

### Referensi per topik

- [05 - User Resource](./docs/05-user-resource.md)
- [06 - UUID Primary Keys](./docs/06-uuid-primary-keys.md)
- [07 - Make Starter Resource](./docs/07-make-starter-resource.md)
- [08 - Guards and Sanctum Flow](./docs/08-guards-and-sanctum-flow.md)
- [09 - Roles Permissions Shield](./docs/09-roles-permissions-shield.md)
- [10 - User Deletion and Anonymization](./docs/10-user-deletion-and-anonymization.md)
- [11 - File Upload Strategy](./docs/11-file-upload-strategy.md)
- [12 - Filament Curator](./docs/12-filament-curator.md)
- [13 - Curator Privacy and Tracking](./docs/13-curator-privacy-and-tracking.md)
- [14 - API](./docs/14-api.md)
- [15 - Mobile File Upload API](./docs/15-mobile-file-upload-api.md)
- [16 - Notifications](./docs/16-notifications.md)
- [17 - Code Quality Toolchain](./docs/17-code-quality-toolchain.md)
- [18 - App Service Provider](./docs/18-app-service-provider.md)
- [19 - Testing Setup](./docs/19-testing-setup.md)
- [20 - Test Pest Coverage](./docs/20-test-pest-coverage.md)
- [21 - Coverage Ignores Analysis](./docs/21-coverage-ignores-analysis.md)
- [22 - Laravel Debugbar](./docs/22-laravel-debugbar.md)
- [23 - Laravel Backup](./docs/23-laravel-backup.md)
- [24 - Laravel Boost AI Coding Guidelines](./docs/24-laravel-boost-ai-coding-guidelines.md)
- [25 - Creating New Module](./docs/25-creating-new-module.md)
- [26 - Production Deployment](./docs/26-production-deployment.md)

## License

Project ini menggunakan lisensi [MIT](LICENSE).
