<p align="center">
    <img src="public/images/logo-128x128.png" width="128" height="128" alt="Filament Starter Kit Logo">
</p>

# Filament Starter Kit

[![Latest Version on Packagist](https://img.shields.io/packagist/v/lyrihkaesa/filament-starter-kit.svg?style=flat-square)](https://packagist.org/packages/lyrihkaesa/filament-starter-kit)
[![Total Downloads](https://img.shields.io/packagist/dt/lyrihkaesa/filament-starter-kit.svg?style=flat-square)](https://packagist.org/packages/lyrihkaesa/filament-starter-kit)
[![PHP Version](https://img.shields.io/badge/php-8.4-blue.svg?style=flat-square)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/laravel-12.x-red.svg?style=flat-square)](https://laravel.com)
[![License](https://img.shields.io/badge/license-MIT-green.svg?style=flat-square)](LICENSE)

Starter kit modern untuk membangun admin panel tangguh menggunakan **Laravel 12** dan **Filament v5**.

Fokus utama kit ini adalah **Developer Experience (DX)** dengan struktur yang sangat rapi, _strict typing_, dan pola kode yang _maintainable_ untuk project jangka panjang. Cocok untuk developer yang menginginkan standar kualitas tinggi seperti ekosistem TypeScript di dalam Laravel.

## ✨ Highlight Fitur

- **Modern Stack**: Laravel 12, Filament v5, Livewire 4, dan Tailwind CSS v4.
- **Architectural Excellence**: Menggunakan **Action Pattern** (`handle()`) untuk memisahkan business logic dari Controller/Page.
- **Strict Typing**: Codebase yang bersahabat dengan _strict types_ untuk keamanan kode yang lebih baik.
- **API Ready**: Integrasi **Laravel Sanctum** yang siap digunakan untuk aplikasi mobile atau frontend terpisah.
- **Security & RBAC**: Manajemen akses canggih menggunakan **Filament Shield**.
- **Privacy Focused**: Sistem **Anonymization** otomatis untuk user yang dihapus (GDPR-friendly).
- **UUID First**: Standar penggunaan UUID untuk tabel baru guna skalabilitas dan keamanan.
- **Quality Assurance**: Terintegrasi penuh dengan **Pest 4** (**100% Test Coverage**), **Pint**, **Larastan**, dan **Rector**.
- **Storage & Database**: Support _PostgreSQL_ untuk production dan _SQLite_ in-memory untuk testing cepat. Dilengkapi native support `local` dan `s3` storage adapter yang siap pakai.
- **AI-Friendly**: Terdesain efisien untuk AI Agents. Penggunaan komponen dioptimalkan melalui package **PAO** untuk mengurangi konsumsi token (context length) drastis.

## 🚀 Quick Start

### Install via Laravel Installer

```bash
laravel new my-app --using=lyrihkaesa/filament-starter-kit
cd my-app
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
composer dev
```

### Akun Admin Default

- **Email**: `superadmin@example.com`
- **Password**: `password`

## 🛠️ Tech Stack & Tools

| Kategori         | Teknologi                                                        |
| ---------------- | ---------------------------------------------------------------- |
| **Framework**    | Laravel 12, Filament 5, Livewire 4                               |
| **Auth**         | Session (WEB), Sanctum (API), Shield (RBAC)                      |
| **Styling**      | Tailwind CSS 4                                                   |
| **Testing**      | Pest 4                                                           |
| **Code Quality** | Pint (Linting), Larastan (Static Analysis), Rector (Refactoring) |
| **Utilities**    | Laravel Boost, Matomo Device Detector, PAO                       |

## 🤖 AI-Friendly Architecture (Hemat Token)

Salah satu keunggulan utama dari Starter kit ini adalah desainnya yang sangat **AI-Friendly**. Mengingat penggunaan AI Agent seperti GitHub Copilot, Cursor, atau Gemini dalam development modern, starter kit ini dirancang agar _Context Window/Length_ tetap ramping. Kami menggunakan package **PAO** (Pattern Action Object atau sejenisnya/Spatie) sehingga boilerplate logic tidak memenuhi token space Anda.
Manfaatnya:

- **Biaya AI lebih murah** karena sedikit token yang terpakai untuk setiap context.
- **Respon AI lebih cepat dan akurat** karena tidak perlu menganalisis ratusan baris kode _noisy_.
- **Konsep Clear/Strict** memudahkan AI merekomendasikan kode (`Action Pattern`, `Strict Types`).

## 🗄️ Database & Storage Support

Aplikasi ini siap tempur dengan multi-database dan multi-storage:

1. **Database:** Mendukung penuh **PostgreSQL** untuk production grade, namun Anda tetap dapat memanfaatkan **SQLite** untuk keperluan _fast testing_ atau local dev.
2. **Storage:** Mendukung langsung system **Local Filesystem** untuk kesederhanaan, serta **S3-Compatible Object Storage** (AWS, MinIO, R2, dll) out-of-the-box guna mendukung skalabilitas aplikasi atau integrasi API mobile yang seamless.

## 📖 Prinsip Pengembangan

1.  **Action Pattern**: Logic bisnis harus berada di kelas Action, bukan di Controller atau Filament Page.
2.  **API Versioning**: Endpoint API terstruktur di bawah `/api/v1` dengan _Eloquent Resources_.
3.  **Soft Deletes & Anonymize**: User yang dihapus akan di-anonymize datanya sebelum benar-benar dihapus permanen.
4.  **No N+1 Queries**: Selalu memprioritaskan _eager loading_ untuk performa database.
5.  **100% Code Coverage**: Code base ini wajib lulus strict Architecture Test dan 100% Test Coverage menggunakan Pest sebelum deployment.

## 📚 Dokumentasi Lengkap

Dokumentasi detail dapat ditemukan di folder [`docs`](./docs) atau melalui:

👉 **[Dokumentasi Online Filament Starter Kit](https://kaesa.charapon.my.id/filament-starter-kit)**

### 🚦 Mulai dari Sini

- [00 - Intro & Filosofi](./docs/00-intro.md) — Tujuan starter kit, pilihan database, storage, dan AI support
- [32 - Architecture Overview](./docs/01-architecture-overview.md) — **Baca ini dulu sebelum membuat fitur baru**

### 🏛️ Arsitektur & Pattern

- [02 - Action Pattern](./docs/02-action-pattern.md) — Mutations (Create/Update/Delete) via Action class
- [23 - Query Builders](./docs/03-query-builders.md) — Scopes vs Custom Eloquent Builders
- [09 - Kenapa Tidak Repository Pattern](./docs/04-repository-pattern.md) — Penjelasan lengkap alasannya
- [16 - Policy & Action Integration](./docs/05-policy-and-action-integration.md) — Cara authorization bekerja bersama Action

### 🔐 Auth & Security

- [17 - Guards & Sanctum Flow](./docs/09-guards-and-sanctum-flow.md)
- [14 - Manajemen Role & Permission](./docs/10-filament-shield.md)
- [15 - Roles & Permissions Seeders](./docs/11-roles-permissions-seeders.md)
- [24 - User Deletion & Anonymization](./docs/12-user-deletion-and-anonymization.md)

### 🌐 API

- [07 - Integrasi API & Sanctum](./docs/18-api.md)
- [21 - Mobile File Upload API](./docs/19-mobile-file-upload-api.md)

### 🗂️ File & Media

- [19 - File Upload Strategy (S3 & Local)](./docs/14-file-upload-strategy.md)
- [20 - Filament Curator](./docs/15-filament-curator.md)
- [26 - Curator Ownership & Privacy](./docs/16-curator-ownership-and-privacy.md)
- [27 - Media Usage Tracking](./docs/17-media-usage-tracking.md)

### 🧪 Testing & QA

- [25 - Database Testing Options](./docs/25-testing-database-options.md) — SQLite vs PostgreSQL untuk testing
- [03 - Test Coverage Setup (Xdebug)](./docs/26-test-pest-coverage.md)
- [22 - Architecture Tests](./docs/27-architecture-tests.md)
- [28 - Coverage Ignores Analysis](./docs/29-coverage-ignores-analysis.md)

### 🛠️ Tools & Filament

- [08 - User Resource](./docs/06-user-resource.md)
- [11 - Make Starter Resource](./docs/08-make-starter-resource.md)
- [18 - Implementasi UUID](./docs/07-uuid-primary-keys.md)
- [31 - Laravel Backup](./docs/31-laravel-backup.md)


## ✅ Quality Control

Jalankan perintah berikut untuk menjaga kualitas codebase:

- **Semua Tes**: `composer test-full`
- **Unit & Feature Test**: `php artisan test`
- **API Testing**: `bru run api-tests/bruno --env local`
- **Auto Format**: `composer lint`
- **Static Analysis**: `composer test:types`
- **Auto Refactor**: `composer refactor`

## 📄 Lisensi

Proyek ini menggunakan lisensi [MIT](LICENSE).
