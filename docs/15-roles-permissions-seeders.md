# Peran, Izin, & Seeder

Mengelola hak akses di banyak environment seperti lokal, staging, dan production membutuhkan strategi yang rapi agar data role dan permission tidak berantakan saat database diatur ulang.

Dokumen ini menjelaskan cara pikir saya dalam mengelola role, permission, dan seeder supaya project lebih mudah dipulihkan dan lebih mudah di-onboard ke developer baru.

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

### Kenapa mengembalikan `null`

Ini penting untuk developer pemula:

- `true` berarti "izinkan"
- `false` berarti "tolak"
- `null` berarti "lanjutkan pengecekan normal"

Jangan kembalikan `false` untuk user non-super-admin, karena itu justru akan memblokir user lain sebelum policy normal dijalankan.

## Strategi Seeding yang Efektif

Kapan pun Anda menjalankan `php artisan migrate:fresh --seed`, Anda ingin Role dan Permission kembali tersedia seperti semula.

Kalau role dan permission hanya dibuat manual lewat dashboard tanpa seeder, maka:

- environment baru sulit disamakan
- onboarding developer baru lebih lambat
- setup ulang database menjadi merepotkan

## Sinkronisasi Otomatis

Gunakan perintah ini untuk memastikan Resource terbaru masuk ke database permission:

```bash
php artisan shield:generate --all
```

## Seeder yang Profesional

Buat seeder yang menangani:

1. membersihkan cache permission
2. generate permission terbaru
3. membuat role dasar
4. memberikan role ke user awal

Contoh:

```php
// database/seeders/ShieldSeeder.php
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

public function run(): void
{
    // 1. Bersihkan cache permission agar tidak bentrok
    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    // 2. Jalankan perintah shield untuk isi data permission
    $this->command->call('shield:generate', ['--all' => true]);

    // 3. Buat Peran (Role) jika belum ada
    $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $panelUser = Role::firstOrCreate(['name' => 'panel_user', 'guard_name' => 'web']);

    // 4. Berikan Peran ke User Utama
    $user = \App\Models\User::firstWhere('email', 'admin@example.com');
    $user?->assignRole($superAdmin);
}
```

## Studi Kasus

Bayangkan Anda baru menambah `OrderResource` dan beberapa permission custom. Lalu seorang developer baru clone project dan menjalankan:

```bash
php artisan migrate:fresh --seed
```

Kalau seeder Anda rapi, environment itu akan langsung mendapatkan:

- role yang benar
- permission terbaru
- user awal yang tepat

Tanpa perlu setup manual lewat dashboard.

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

## Cara Pikir Saya Soal Seeder Authorization

Saya lebih suka role dan permission dianggap sebagai bagian dari infrastruktur aplikasi, bukan data manual sesaat.

Artinya:

- setup project baru harus bisa diulang
- database reset tidak boleh membuat akses sistem berantakan
- role utama jangan hanya hidup di database production tanpa jejak di codebase

## Rekomendasi

- simpan role penting di seeder
- gunakan naming convention yang konsisten
- uji hasil seeder dengan `migrate:fresh --seed`
- perlakukan authorization setup sebagai bagian dari source of truth project
