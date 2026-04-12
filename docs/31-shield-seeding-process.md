# Filament Shield Seeding Process

Dokumen ini menjelaskan cara mengelola Role dan Permission menggunakan Filament Shield, terutama saat terjadi perubahan struktur database atau penambahan Resource baru.

## 1. Konsep Utama
Filament Shield menghasilkan permission berdasarkan Resource yang terdaftar. Jika Anda menambah Resource baru (misal: `PostResource`), Shield perlu diberitahu untuk membuat permission untuk resource tersebut.

## 2. Cara Update Permission (Sync)
Setiap kali Anda menambah Resource, mengubah Policy, atau mengubah konfigurasi `filament-shield.php`, jalankan perintah ini:

```bash
php artisan shield:generate --all
```
Perintah ini akan memperbarui `ShieldSeeder.php` dengan daftar permission terbaru berdasarkan Resource yang ada.

## 3. Alur Seeding yang Benar
Untuk memastikan User mendapatkan Role yang benar, urutan seeding harus seperti ini:

1. **ShieldSeeder**: Membuat Permission & Role (Admin, dsb).
2. **UserSeeder/PostSeeder**: Membuat User dan memberikan Role yang sudah dibuat oleh Shield.

### Contoh Implementasi di `DatabaseSeeder.php`:

```php
public function run(): void
{
    // 1. Generate Permission & Roles dulu
    $this->call(ShieldSeeder::class);

    // 2. Buat User Admin (Jika belum ada)
    $admin = User::factory()->create([
        'email' => 'admin@example.com',
    ]);
    $admin->assignRole('admin');

    // 3. Jalankan Seeder Resource lainnya
    $this->call(PostSeeder::class);
}
```

## 4. Kustomisasi Role Member
Jika Anda ingin Role `member` memiliki permission tertentu secara otomatis saat seeding:
1. Jalankan `php artisan shield:generate --all`.
2. Buka `database/seeders/ShieldSeeder.php`.
3. Cari bagian konfigurasi role (biasanya dalam array) dan sesuaikan permission-nya di sana sebelum menjalankan `db:seed`.

## 5. Troubleshooting
Jika Role tidak muncul atau Permission error:
- Jalankan `php artisan permission:cache-reset` untuk membersihkan cache Spatie Permission.
- Pastikan Resource sudah terdaftar di `config/filament-shield.php` pada bagian `resource_permission_prefixes`.
