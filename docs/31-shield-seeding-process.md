# Filament Shield Seeding Process

Dokumen ini menjelaskan cara mengelola Role dan Permission menggunakan Filament Shield, termasuk strategi seeding yang aman untuk environment testing.

## 1. Konsep Utama
Filament Shield menghasilkan permission berdasarkan Resource yang terdaftar. Jika Anda menambah Resource baru (misal: `PostResource`), Shield perlu dijalankan ulang agar permission resource tersebut ikut dibuat.

## 2. Cara Update Permission (Sync)
Setiap kali menambah Resource, mengubah Policy, atau mengubah konfigurasi `filament-shield.php`, jalankan:

```bash
php artisan shield:generate --all
```

Perintah ini memperbarui `database/seeders/ShieldSeeder.php` sebagai output generated.

## 3. Aturan Penting: Jangan Edit `ShieldSeeder.php` Manual
`ShieldSeeder.php` diperlakukan sebagai **generated artifact**. Jika Anda edit manual, perubahan akan hilang ketika command Shield dijalankan ulang.

Praktik di project ini:

- `ShieldSeeder.php` dipakai untuk non-testing (local/staging/production-like seeding).
- Kebutuhan test-only dipindah ke `database/seeders/TestingShieldSeeder.php`.
- Pemilihan seeder dilakukan di `DatabaseSeeder` berdasarkan environment.

## 4. Alur Seeding di `DatabaseSeeder`
Pola yang dipakai saat ini:

```php
public function run(): void
{
    if (app()->environment('testing')) {
        $this->call([
            TestingShieldSeeder::class,
        ]);

        return;
    }

    $this->call([
        ShieldSeeder::class,
        PostSeeder::class,
    ]);
}
```

Tujuannya:

- Jalur testing stabil dan tidak tergantung file generated yang bisa berubah.
- Jalur non-testing tetap mengikuti sumber permission resmi dari Shield.

## 5. Kustomisasi Role/Permission dengan Aman
Jika perlu menambah behavior:

1. Gunakan `shield:generate --all` untuk update struktur permission utama.
2. Jangan edit `ShieldSeeder.php` secara manual untuk kebutuhan test.
3. Letakkan kebutuhan khusus test di `TestingShieldSeeder.php`.
4. Untuk user test, gunakan password plain agar cast `hashed` mengikuti konfigurasi hash environment testing.

## 6. Troubleshooting
Jika role/permission tidak sesuai:

- Jalankan `php artisan permission:cache-reset`.
- Verifikasi resource sudah terdaftar di `config/filament-shield.php`.
- Pastikan test dijalankan dengan environment testing yang benar (`APP_ENV=testing`).
