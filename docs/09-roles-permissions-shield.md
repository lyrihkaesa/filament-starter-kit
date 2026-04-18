# Roles, Permission & Filament Shield

Dokumen ini menjelaskan cara kerja sistem otorisasi berbasis role dan permission di starter kit ini, mencakup:

- Konsep dasar Role & Permission
- Konfigurasi Filament Shield
- Hak istimewa Super Admin
- Strategi seeder yang profesional

---

## Konsep Dasar untuk Pemula

Sebelum masuk ke teknis, pahami dua istilah ini:

1. **Role**: peran atau jabatan seseorang, misalnya `Admin`, `Editor`, `Member`
2. **Permission**: aksi yang boleh dilakukan oleh role tersebut, misalnya `view user`, `delete post`, atau `update order`

### Cara Membayangkannya

- **Role** = siapa dia
- **Permission** = apa yang boleh dia lakukan

Kalau Anda paham dua hal ini, sisanya akan jauh lebih mudah.

---

## Apa Itu Filament Shield

Filament Shield adalah package yang memudahkan kita mengelola **Role** dan **Permission** di aplikasi Filament. Package ini dibangun di atas [Spatie Permission](https://spatie.be/docs/laravel-permission).

Di starter kit ini, Filament Shield berperan penting karena saya ingin sistem admin panel tidak berhenti hanya di level "bisa login", tetapi juga jelas siapa boleh melakukan apa.

### Kenapa Saya Memakai Filament Shield

Karena saya tidak ingin mengelola permission secara manual satu per satu untuk setiap resource baru.

Begitu aplikasi mulai punya:

- banyak resource
- banyak halaman custom
- beberapa widget
- beberapa role

mengelola authorization secara manual akan cepat melelahkan. Filament Shield membantu mengotomatisasi bagian yang repetitif.

---

## Setup Dasar

### 1. HasRoles Trait

Pastikan model `User` memiliki kemampuan mengelola role:

```php
// app/Models/User.php
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles; // Wajib ditambahkan!
}
```

Kalau trait ini tidak ada, sistem role dan permission tidak akan bekerja sebagaimana mestinya.

### 2. Deteksi Otomatis Permission

Setiap kali Anda membuat Resource baru, misalnya `ProductResource`, Shield dapat menyiapkan izin standar seperti:

- `view_any`
- `create`
- `update`
- `delete`

Ini membantu Anda tidak mulai dari nol setiap kali ada modul baru.

> Jalankan perintah `php artisan shield:generate --all` untuk menyinkronkan Resource baru ke dalam database agar permission-nya muncul di halaman kelola role.

---

## Pengaturan di `filament-shield.php`

Beberapa pengaturan penting yang perlu Anda tahu:

- `super_admin`: peran tertinggi yang punya akses ke mana saja
- `panel_user`: peran dasar bagi siapa saja yang boleh masuk ke dashboard Filament
- `exclude`: daftar Resource atau Page yang tidak ingin Anda beri pembatasan akses

Secara default, aplikasi ini menggunakan gaya penulisan **PascalCase** untuk permission, misalnya `ViewAnyUser`, tetapi ini bisa diubah di file konfigurasi jika Anda memang punya konvensi lain.

---

## Hak Istimewa Super Admin

"Super Admin" adalah peran yang punya kunci ke semua pintu. Dalam banyak project, kita tidak perlu memberikan permission satu per satu padanya.

### Pengaturan `Gate::before`

Gunakan `Gate::before` di `AppServiceProvider` agar Super Admin otomatis lolos di semua pengecekan authorization:

```php
// app/Providers/AppServiceProvider.php
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    // Jika user punya role 'super_admin', berikan akses langsung
    Gate::before(function ($user, $ability) {
        return $user->hasRole('super_admin') ? true : null;
    });
}
```

### Kenapa Mengembalikan `null` (Bukan `false`)

Ini penting untuk developer pemula:

- `true` berarti "izinkan"
- `false` berarti "tolak"
- `null` berarti "lanjutkan pengecekan normal"

Jangan kembalikan `false` untuk user non-super-admin, karena itu justru akan memblokir user lain sebelum policy normal dijalankan.

---

## Strategi Seeder yang Efektif

Kapan pun Anda menjalankan `php artisan migrate:fresh --seed`, Anda ingin Role dan Permission kembali tersedia seperti semula.

Kalau role dan permission hanya dibuat manual lewat dashboard tanpa seeder, maka:

- environment baru sulit disamakan
- onboarding developer baru lebih lambat
- setup ulang database menjadi merepotkan

### Sinkronisasi Otomatis

Gunakan perintah ini untuk memastikan Resource terbaru masuk ke database permission:

```bash
php artisan shield:generate --all
```

### Seeder yang Profesional

Buat seeder yang menangani:

1. membersihkan cache permission
2. generate permission terbaru
3. membuat role dasar
4. memberikan role ke user awal

**Catatan Khusus UUID v7:** Starter kit ini menggunakan `App\Models\Role` dan `App\Models\Permission` (bukan bawaan Spatie) agar mendukung UUID v7 secara otomatis.

Contoh:

```php
// database/seeders/ShieldSeeder.php
use App\Models\Role; // Menggunakan model kustom App\Models
use Spatie\Permission\PermissionRegistrar;

public function run(): void
{
    // ...
}
```

---

## Studi Kasus

### Kasus 1: Resource Baru

Bayangkan Anda menambah `ProductResource`.

Tanpa Shield, Anda harus:

- menentukan permission secara manual
- menyimpan semuanya ke database
- memastikan role tertentu mendapat permission yang benar

Dengan Shield, Anda cukup generate ulang permission, lalu atur pembagiannya melalui panel atau seeder:

```bash
php artisan shield:generate --all
```

### Kasus 2: Onboarding Developer Baru

Bayangkan developer baru clone project dan menjalankan:

```bash
php artisan migrate:fresh --seed
```

Kalau seeder Anda rapi, environment itu akan langsung mendapatkan:

- role yang benar
- permission terbaru
- user awal yang tepat

Tanpa perlu setup manual lewat dashboard.

---

## Generate Seeder Shield Secara Otomatis

Jika Anda sudah mengatur Role, Permission, dan akun User melalui dashboard Filament lalu ingin menyimpannya ke dalam file seeder, gunakan:

```bash
php artisan shield:seeder --generate --with-users --all -F
```

### Penjelasan Opsi

- `--generate`: membuat permission untuk semua Resource, Page, dan Widget yang terdaftar
- `--with-users`: menyertakan data user yang sudah memiliki role atau permission
- `--all`: mengekspor semua user dari database
- `-F` atau `--force`: menimpa file `ShieldSeeder.php` yang sudah ada

### Langkah-langkah Interaktif

Saat menjalankan command di atas, biasanya Anda akan diminta memilih:

1. panel yang digunakan
2. jenis data yang ingin di-generate
3. cara menangani password user

Jika ingin login kembali dengan password hash yang sudah ada setelah `migrate:fresh`, pilih opsi yang menyertakan password hasil hash tersebut.

---

## Cara Pikir Saya Soal Authorization

Saya lebih suka authorization yang:

- jelas secara struktur
- tidak tersebar tanpa pola
- bisa di-regenerate
- mudah dipulihkan lewat seeder
- dapat dipelihara tanpa bergantung hanya pada database production

Artinya, role dan permission dianggap sebagai bagian dari **infrastruktur aplikasi**, bukan data manual sesaat.

---

## Rekomendasi

- Simpan role penting di seeder
- Gunakan naming convention yang konsisten
- Uji hasil seeder dengan `migrate:fresh --seed`
- Perlakukan authorization setup sebagai bagian dari source of truth project
- Gunakan `Gate::before` untuk super_admin agar tidak perlu assign permission satu per satu
