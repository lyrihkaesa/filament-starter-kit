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

1. `docs/02-action-pattern.md`
2. `docs/08-user-resource.md`
3. `docs/16-policy-and-action-integration.md`
4. `docs/14-filament-shield.md`
5. `docs/07-api.md`

Dengan urutan itu, Anda akan lebih mudah memahami "alur berpikir" starter kit ini, bukan hanya daftar fiturnya.
