---
slug: /
sidebar_position: 0
---

# Introduction

Starter kit ini saya buat untuk developer yang ingin membangun aplikasi admin panel berbasis [Laravel](https://laravel.com/) dan [Filament](https://filamentphp.com/) dengan pondasi yang sudah rapi sejak awal. Fokusnya bukan hanya "cepat jadi", tetapi juga supaya struktur project tetap enak dibaca ketika aplikasi mulai membesar.

Dokumentasi ini ditulis untuk developer pemula sampai menengah. Jadi, selain menjelaskan apa yang dipakai di starter kit, saya juga jelaskan cara pikir di balik keputusan-keputusan tersebut.

## Tujuan Starter Kit Ini

Starter kit ini saya siapkan supaya Anda tidak memulai project dari nol setiap kali membuat admin panel. Beberapa hal yang ingin saya dapatkan dari starter kit ini:

- struktur project yang konsisten
- pemisahan logika bisnis dari UI
- tooling kualitas kode yang sudah siap pakai
- pengalaman development yang nyaman untuk project jangka panjang

### AI-Friendly (Hemat Token)

Mengingat saat ini pengembangan sering dibantu oleh AI Agent (seperti Cursor, Copilot, Gemini dsb), starter kit ini didesain supaya ramah AI. Kami menggunakan package **PAO (Pattern Action Object)** sehingga kode-kode core, boilerplate, atau logic berulang tidak memakan space yang besar di context window AI.
Hasilnya:
- **Penggunaan token berkurang drastis**
- **AI lebih cepat merespon dan lebih akurat** karena tidak ter-distract oleh kode *noisy* atau berulang. Segala sesuatunya mudah dipahami oleh struktur logic-nya.

## Cara Pikir yang Dipakai

Ada beberapa prinsip utama yang saya pakai saat menyusun starter kit ini:

### 1. UI jangan terlalu pintar

Saya tidak suka ketika semua logika bisnis ditaruh di Filament Resource, Livewire component, atau controller. UI sebaiknya fokus menerima input, menampilkan data, lalu memanggil kelas yang memang bertugas mengerjakan proses bisnis.

Karena itu, starter kit ini banyak memakai **Action Pattern**.

### 2. Kode harus enak dirawat setelah 3-6 bulan

Saat project masih kecil, banyak pendekatan terasa "cepat". Masalahnya baru terasa ketika resource mulai banyak, role mulai kompleks, dan kebutuhan bisnis makin bercabang.

Starter kit ini mencoba mengurangi masalah itu dari awal:

- action untuk mutasi data
- policy untuk otorisasi
- Filament untuk admin UI
- test dan static analysis untuk menjaga kualitas

### 3. Boilerplate boleh ada, asal menghemat beban berpikir

Saya tidak anti boilerplate. Yang saya hindari adalah boilerplate yang tidak memberi manfaat.

Contohnya:

- saya **membuat command** untuk generate action karena itu membantu konsistensi
- saya **menghindari repository pattern** untuk kasus biasa karena itu sering hanya menambah file tanpa nilai tambah

### 4. Semua Alur Wajib Dites (100% Test Coverage)

Di fase production, menebak-nebak apakah *update* baru akan memecahkan sistem lama adalah mimpi buruk. Kode ini dirancang dengan **100% Test Coverage** melalui Pest (sudah termasuk Architecture test). Setiap logic, permission, dan endpoint sudah divalidasi. Ini teramat penting bagi *maintenance*: jika AI atau dev (Anda) merefaktor code di kemudian hari, kita langsung tahu kalau ada *regression bug* dari hasil testing yang error.

## Isi Starter Kit Ini

Beberapa komponen utama yang dipakai:

- Laravel 12
- Filament
- Livewire
- Laravel Sanctum
- Filament Shield
- Pest
- Laravel Pint
- Larastan
- Rector

Semua itu dipakai bukan karena ingin terlihat "lengkap", tetapi karena memang membantu workflow yang saya inginkan.

## Pilihan Database: PostgreSQL vs SQLite

Starter kit ini dirancang "ramah pemula" tetapi tetap *enterprise-ready* untuk level production.

- **SQLite (Default & Testing)**: Sangat mudah bagi pemula dan sangat cepat untuk testing. Nyaris nol konfigurasi. Cukup `php artisan migrate`, file database `.sqlite` akan otomatis dibuat dan siap digunakan tanpa perlu *install* database engine apa pun. **Kelemahan:** Tidak didesain untuk concurrency tinggi dan tidak punya penanganan tipe data Array/JSON yang secanggih PostgreSQL.
- **PostgreSQL (Production)**: Inilah opsi tangguh dan *robust*. Ketika sistem membesar, relasi tabel semakin banyak, atau butuh query pengolahan Array/JSON di level database secara native, Postgres adalah pilihan mutlak. Struktur kode starter kit ini memudahkan penggantian engine cukup via file konfigurasi `.env`.

## Pilihan Storage: Local vs S3

Kami menyediakan implementasi *Storage adapter* yang agnostik, kode upload dsb tidak perlu dimodifikasi berat saat environment berubah.

- **Local Filesystem**: Paling gampang dicoba oleh pemula. Upload, lalu file disimpan ke subfolder aplikasi dev kita (misal `storage/app/public/...`). **Kelemahan:** Memicu resiko saat disk VPS server penuh atau error, susah jika disetup pada banyak instance load balancer (files akan terpisah-pisah di beda server), dan rawan hilang jika berganti container server secara paksa.
- **S3-Compatible Object Storage**: Inilah *Best Practice* deployment modern berskala medium-high. Anda memisah storage file dari storage code (VPS) dengan service *spesialis object storage* seperti Amazon S3, MinIO, Cloudflare R2 dsb. **Kelebihan:** Sangat scalable, file langsung dapat diakses oleh HTTP request frontend / *API client* (Mobile Apps) dengan *Pre-Signed URL* tanpa membebani bandwidth dan PHP thread dari VPS utama Anda. Tingkat keawetannya (durability) juga jauh lebih baik saat ada kegagalan server.

## Studi Kasus Sederhana

Bayangkan Anda ingin membuat modul `User`, `Product`, dan `Order`.

Di banyak project, biasanya hal ini terjadi:

- logika create/update ditaruh di controller atau Filament page
- validasi bercampur dengan mutasi data
- delete logic tersebar di banyak tempat
- policy terlupa di beberapa endpoint

Di starter kit ini, saya ingin alurnya lebih jelas:

1. User submit form dari Filament atau API.
2. Request atau page memvalidasi input dan melakukan authorization.
3. Action class menjalankan logika bisnis.
4. Resource hanya menjadi lapisan presentasi dan orkestrasi.

Hasilnya: ketika aturan bisnis berubah, Anda tahu harus membuka file yang mana.

## Install Baru dengan Laravel Installer

1. Pastikan [Laravel Installer](https://laravel.com/docs/12.x/installation#installing-php) sudah tersedia.

Jika Anda memakai **Laravel Herd**, biasanya `Laravel Installer` sudah ikut tersedia. Untuk mengeceknya:

```bash
laravel --version
```

Jika belum ada, Anda bisa menginstalnya dengan Composer:

```bash
composer global require laravel/installer
```

2. Buat project baru langsung dari starter kit:

```bash
laravel new my-app --using=lyrihkaesa/filament-starter-kit
cd my-app
```

3. Jika ada script saat create project yang gagal atau belum lengkap, jalankan ulang setup dasarnya:

```bash
composer install
npm install
npm run build
cp .env.example .env
php artisan migrate --seed
php artisan key:generate
```

4. Jalankan server development:

```bash
composer dev
```

Jika menggunakan `Laravel Herd`, biasanya aplikasi dapat diakses melalui domain lokal seperti [http://filament-starter-kit.test](http://filament-starter-kit.test).

## Login Default

Starter kit menyediakan akun default untuk mempermudah eksplorasi awal:

- Email: `superadmin@example.com` or `admin@example.com`
- Password: `password`

Jika `APP_DEBUG=true`, pada beberapa flow development form login juga bisa terisi otomatis agar onboarding lebih cepat.

## Konfigurasi `APP_URL`

Sesuaikan `APP_URL` dengan cara Anda menjalankan aplikasi:

- Jika menggunakan **Laravel Herd**, atur `APP_URL=http://filament-starter-kit.test`
- Jika menggunakan `composer dev`, gunakan `APP_URL=http://localhost:8000`

## Saran untuk Developer Pemula

Kalau ini pertama kali Anda memakai starter kit ini, saya sarankan urutan membacanya:

1. `docs/01-architecture-overview.md` — gambaran besar arsitektur
2. `docs/02-action-pattern.md` — pola utama yang dipakai di seluruh project
3. `docs/03-query-pattern.md` — cara mengambil data & kenapa bukan Repository Pattern
4. `docs/04-policy-and-action-integration.md` — pemisahan tanggung jawab: Policy, FormRequest, Action
5. `docs/05-user-resource.md` — contoh nyata modul User dari A ke Z
6. `docs/09-roles-permissions-shield.md` — manajemen role & permission
7. `docs/14-api.md` — API contract dan alur mobile
8. `docs/25-creating-new-module.md` — Tutorial: Membuat Modul Baru dari Nol ⭐

Setelah memahami delapan dokumen di atas, Anda sudah bisa memahami "cara pikir" starter kit ini. Sisanya bisa dibaca sesuai kebutuhan.

### Daftar Lengkap Dokumentasi

| No | File | Topik |
|----|------|-------|
| 00 | `00-intro.md` | Pengenalan & cara install |
| 01 | `01-architecture-overview.md` | Gambaran arsitektur tiga layer |
| 02 | `02-action-pattern.md` | Action Pattern (CQRS-style) |
| 03 | `03-query-pattern.md` | Query: Scopes, Builders, kenapa bukan Repository |
| 04 | `04-policy-and-action-integration.md` | Policy, FormRequest & Action |
| 05 | `05-user-resource.md` | Studi kasus modul User + Filament Resource |
| 06 | `06-uuid-primary-keys.md` | Penggunaan UUID sebagai primary key |
| 07 | `07-make-starter-resource.md` | Command `make:starter-resource` |
| 08 | `08-guards-and-sanctum-flow.md` | Guard web vs API, alur Sanctum |
| 09 | `09-roles-permissions-shield.md` | Filament Shield, Role, Permission & Seeder |
| 10 | `10-user-deletion-and-anonymization.md` | Soft delete & anonimisasi user |
| 11 | `11-file-upload-strategy.md` | Strategi upload file (Filament, API, S3) |
| 12 | `12-filament-curator.md` | Filament Curator sebagai media library |
| 13 | `13-curator-privacy-and-tracking.md` | Ownership, privacy & usage tracking media |
| 14 | `14-api.md` | API contract, format response, auth |
| 15 | `15-mobile-file-upload-api.md` | Upload file via API mobile |
| 16 | `16-notifications.md` | Notifikasi & background jobs (Queue) |
| 17 | `17-code-quality-toolchain.md` | Pint + Larastan + Rector |
| 18 | `18-app-service-provider.md` | Konfigurasi global di AppServiceProvider |
| 19 | `19-testing-setup.md` | Setup testing: DB options, arch tests, browser |
| 20 | `20-test-pest-coverage.md` | Setup Xdebug & code coverage (Windows) |
| 21 | `21-coverage-ignores-analysis.md` | Analisis `@codeCoverageIgnore` |
| 22 | `22-laravel-debugbar.md` | Laravel Debugbar & Tutorial N+1 Query |
| 23 | `23-laravel-backup.md` | Backup otomatis dengan Spatie |
| 24 | `24-laravel-boost-ai-coding-guidelines.md` | Laravel Boost & panduan AI coding |
| 25 | `25-creating-new-module.md` | Tutorial: Membuat Modul Baru dari Nol ⭐ |
| 26 | `26-production-deployment.md` | Panduan Production Deployment ⭐ |
