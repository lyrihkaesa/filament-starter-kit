# Panduan Filament Shield

Filament Shield adalah package yang memudahkan kita mengelola **Role** dan **Permission** di aplikasi Filament. Package ini dibangun di atas [Spatie Permission](https://spatie.be/docs/laravel-permission).

Di starter kit ini, Filament Shield berperan penting karena saya ingin sistem admin panel tidak berhenti hanya di level "bisa login", tetapi juga jelas siapa boleh melakukan apa.

## Konsep Dasar untuk Pemula

Sebelum masuk ke teknis, pahami dua istilah ini:

1. **Role**: peran atau jabatan seseorang, misalnya `Admin`, `Editor`, `Member`
2. **Permission**: aksi yang boleh dilakukan oleh role tersebut, misalnya `view user`, `delete post`, atau `update order`

### Cara membayangkannya

- Role = siapa dia
- Permission = apa yang boleh dia lakukan

Kalau Anda paham dua hal ini, sisanya akan jauh lebih mudah.

## Kenapa Saya Memakai Filament Shield

Karena saya tidak ingin mengelola permission secara manual satu per satu untuk setiap resource baru.

Begitu aplikasi mulai punya:

- banyak resource
- banyak halaman custom
- beberapa widget
- beberapa role

mengelola authorization secara manual akan cepat melelahkan.

Filament Shield membantu mengotomatisasi bagian yang repetitif.

## Cara Kerja Otomatis

Filament Shield bisa mendeteksi Resource, Page, dan Widget Anda lalu menyiapkan permission yang sesuai.

### 1. HasRoles Trait

Pastikan model `User` Anda memiliki kemampuan mengelola role:

```php
// app/Models/User.php
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles; // Wajib ditambahkan!
}
```

Kalau trait ini tidak ada, sistem role dan permission tidak akan bekerja sebagaimana mestinya.

### 2. Deteksi Otomatis

Setiap kali Anda membuat Resource baru, misalnya `ProductResource`, Shield dapat menyiapkan izin standar seperti:

- `view_any`
- `create`
- `update`
- `delete`

Ini membantu Anda tidak mulai dari nol setiap kali ada modul baru.

> Jalankan perintah `php artisan shield:generate --all` untuk menyinkronkan Resource baru ke dalam database agar permission-nya muncul di halaman kelola role.

## Pengaturan di `filament-shield.php`

Beberapa pengaturan penting yang perlu Anda tahu:

- `super_admin`: peran tertinggi yang punya akses ke mana saja
- `panel_user`: peran dasar bagi siapa saja yang boleh masuk ke dashboard Filament
- `exclude`: daftar Resource atau Page yang tidak ingin Anda beri pembatasan akses

Secara default, aplikasi ini menggunakan gaya penulisan **PascalCase** untuk permission, misalnya `ViewAnyUser`, tetapi ini bisa diubah di file konfigurasi jika Anda memang punya konvensi lain.

## Studi Kasus

Bayangkan Anda menambah `ProductResource`.

Tanpa Shield, Anda harus:

- menentukan permission secara manual
- menyimpan semuanya ke database
- memastikan role tertentu mendapat permission yang benar

Dengan Shield, Anda cukup generate ulang permission, lalu atur pembagiannya melalui panel atau seeder.

## Cara Pikir Saya Soal Authorization

Saya lebih suka authorization yang:

- jelas secara struktur
- tidak tersebar tanpa pola
- bisa di-regenerate
- mudah dipulihkan lewat seeder

Filament Shield membantu bagian ini dengan baik, terutama untuk aplikasi admin yang memang bertumpu pada resource.

## Ringkasan

- Shield membantu mengelola role dan permission di Filament
- `User` harus memakai trait `HasRoles`
- setelah menambah resource baru, sinkronkan permission dengan `shield:generate --all`
- gunakan bersama policy dan seeder agar sistem authorization tetap rapi dan konsisten
