# 🔑 Peran, Izin, & Seeder

Mengelola hak akses di banyak lingkungan (lokal, staging, produksi) membutuhkan strategi yang rapi agar data tidak berantakan saat database diatur ulang.

## 👑 Hak Istimewa Super Admin

"Super Admin" adalah peran yang punya kunci ke semua pintu. Kita tidak perlu memberikan Izin (Permission) satu per satu padanya.

### 1. Pengaturan Gate::before
Gunakan `Gate::before` di `AppServiceProvider` Anda agar Super Admin otomatis lolos di semua pengecekan.

```php
// app/Providers/AppServiceProvider.php
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    // Jika user punya role 'super_admin', berikan akses langsung (true)
    Gate::before(function ($user, $ability) {
        return $user->hasRole('super_admin') ? true : null;
    });
}
```

> [!IMPORTANT]
> Jangan kembalikan `false` di sini, karena itu akan memblokir semua user lain! Gunakan `null` agar Laravel lanjut mengecek Izin lainnya jika user tersebut bukan Super Admin.

## 🔄 Strategi Seeding yang Efektif

Kapan pun Anda menjalankan `php artisan migrate:fresh --seed`, Anda ingin Role dan Permission Anda kembali sedia kala.

### 1. Sinkronisasi Otomatis
Gunakan perintah ini untuk memastikan semua Resource terbaru Anda masuk ke dalam database:
```bash
php artisan shield:generate --all
```

### 2. Seeder yang Profesional
Buatlah seeder yang menggabungkan pembuatan peran dan pemberian akun ke user.

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

## 🚀 Cara Generate Seeder Shield (Modern)

Jika Anda sudah mengatur Role, Permission, dan akun User melalui dashboard Filament dan ingin menyimpannya ke dalam file seeder secara otomatis, gunakan perintah berikut:

```bash
php artisan shield:seeder --generate --with-users --all -F
```

### Penjelasan Opsi:
- `--generate`: Otomatis membuatkan Permission untuk semua Resource, Page, dan Widget yang terdaftar.
- `--with-users`: Menyertakan data User yang sudah memiliki Role/Permission ke dalam file seeder.
- `--all`: Mengekspor **semua** user dari database, bukan hanya yang memiliki role.
- `-F` atau `--force`: Menimpa (overwrite) file `ShieldSeeder.php` yang sudah ada sebelumnya.

### Langkah-langkah Interaktif:
Saat menjalankan perintah di atas, Anda akan diminta memilih beberapa hal:
1. **Pilih Panel**: Ketik `0` (untuk panel `app`).
2. **What to generate?**: Pilih `Policies & Permissions` (default) untuk keamanan lengkap.
3. **Handle user passwords?**: Pilih `include` jika ingin menyertakan password yang sudah ter-hash dari database, agar Anda bisa login kembali dengan password yang sama setelah migrate fresh.

> [!TIP]
> Dengan cara ini, Anda tidak perlu lagi melakukan copy-paste manual dari Tinker. Semua Role, Permission, dan User akan tersimpan dalam bentuk JSON di dalam `ShieldSeeder.php`.
