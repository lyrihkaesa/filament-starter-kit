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

## 📦 Mengambil Data Role dari Database

Jika Anda sudah capek-capek mengatur Role dan Izin lewat dashboard Filament dan ingin menyimpannya ke dalam kode seeder, gunakan cara ini di **Tinker**:

```php
// Jalankan di 'php artisan tinker'
$roles = \Spatie\Permission\Models\Role::with('permissions')->get();

foreach ($roles as $role) {
    echo "Role::firstOrCreate(['name' => '{$role->name}'])"
         . "->syncPermissions([" . $role->permissions->pluck('name')->implode("', '") . "]);\n";
}
```

> [!TIP]
> Hasil dari kode di atas tinggal Anda **Copy-Paste** saja ke dalam file Seeder Anda! Sangat praktis.
