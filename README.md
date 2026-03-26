# Filament Starter Kit

Starter kit untuk membangun admin panel dengan [Laravel 12](https://laravel.com/) dan [Filament v5](https://filamentphp.com/).

Fokus starter kit ini adalah struktur yang rapi, strict typing, dan pola kode yang enak dirawat untuk project jangka panjang. Cocok untuk developer yang suka pendekatan ketat seperti saat memakai TypeScript, tetapi di ekosistem Laravel.

## Highlight

- Filament v5 ready
- Laravel 12 + Livewire 4
- Action Pattern dengan `handle()`
- Strict type friendly
- API ready dengan Laravel Sanctum
- RBAC dengan Filament Shield
- UUID-first untuk tabel baru
- Laravel Boost ready
- Pest, Pint, Larastan, dan Rector sudah siap

## Kenapa Pakai Starter Kit Ini

- Business logic tidak menumpuk di controller atau Filament page
- Cocok untuk developer yang suka kode lebih strict dan lebih terstruktur
- Lebih nyaman untuk scaling fitur daripada setup CRUD cepat yang serba campur
- Sudah ada pondasi untuk testing, static analysis, dan refactor

## Cocok Untuk

- developer Laravel yang suka strict type
- developer yang terbiasa dengan pola pikir TypeScript
- admin panel internal
- dashboard operasional
- project yang ingin mulai rapi dari awal

## Quick Start

### Install dengan Laravel Installer

```bash
laravel new my-app --using=lyrihkaesa/filament-starter-kit
cd my-app
composer install
npm install
npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
composer dev
```

### Login Default

- Email: `admin@example.com`
- Password: `password`

## Stack

- Laravel 12
- Filament 5
- Livewire 4
- Sanctum
- Filament Shield
- Pest 4
- Pint
- Larastan
- Rector
- Laravel Boost

## Pendekatan Utama

- **Action Pattern** untuk business logic
- **Sanctum** sebagai default API auth, bukan JWT
- **UUID** sebagai rekomendasi default untuk tabel baru
- **Strict typing** sebagai arah utama codebase

## Dokumentasi

Dokumentasi lengkap ada di folder [`docs`](./docs) dan otomatis ter-publish ke:

- [Dokumentasi Kaesa Filament Starter Kit](https://kaesa.charapon.my.id/filament-starter-kit)

Dokumen yang paling penting untuk mulai:

- [`00-intro.md`](./docs/00-intro.md)
- [`02-action-pattern.md`](./docs/02-action-pattern.md)
- [`07-api.md`](./docs/07-api.md)
- [`08-user-resource.md`](./docs/08-user-resource.md)
- [`18-uuid-primary-keys.md`](./docs/18-uuid-primary-keys.md)

## Quality Tools

- Test: `php artisan test`
- Lint: `composer lint`
- Static analysis: `composer test:types`
- Refactor: `composer refactor`

## Lisensi

[MIT License](LICENSE)
