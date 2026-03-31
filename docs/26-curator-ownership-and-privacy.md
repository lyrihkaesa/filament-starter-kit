# Curator Ownership & Privacy (Blameable)

Starter kit ini mengimplementasikan sistem kepemilikan (ownership) dan privasi (privacy) pada Curator Media Manager untuk meningkatkan keamanan dan akuntabilitas data.

## Fitur Utama

- **Explicit Ownership:** Setiap record media mencatat siapa yang mengunggahnya (`created_by`).
- **Granular Privacy:** Media dapat diatur menjadi `PRIVATE`, `MEMBER`, atau `PUBLIC`.
- **Automatic Visibility Sync:** Sistem secara otomatis mensinkronkan status `privacy` (logical) dengan `visibility` (physical) di storage (misal: S3).
- **Policy Enforcement:** Akses untuk mengedit dan menghapus media dibatasi hanya untuk pemilik, admin, atau super admin.

## Arsitektur Database

Kolom-kolom berikut telah ditambahkan ke tabel `curator` (via migration):

- `created_by` (UUID): Foreign key ke tabel `users`.
- `privacy` (String): Menyimpan status privasi media (Default: `private`).

## Model CuratorMedia

Model `App\Models\CuratorMedia` telah diperbarui dengan:

- **Relationship:** Method `creator()` yang merujuk ke model `User`.
- **Casts:** Properti `privacy` di-cast ke enum `App\Enums\Privacy`.
- **Auto-blame:** Menggunakan event `creating` untuk otomatis mengisi `created_by` dengan ID user yang sedang login.
- **Visibility Sync:** Menggunakan event `saving` untuk sinkronisasi otomatis:
    - Jika `privacy === PUBLIC`, maka `visibility = 'public'`.
    - Selain itu, `visibility = 'private'`.

```php
// app/Models/CuratorMedia.php

protected static function booted(): void
{
    self::saving(function (self $media): void {
        if ($media->privacy === Privacy::PUBLIC) {
            $media->visibility = 'public';
        } else {
            $media->visibility = 'private';
        }
    });
}
```

## Enum Privacy

Enum `App\Enums\Privacy` mendefinisikan tiga level akses:

1.  **PRIVATE (Default):** Hanya dapat dilihat oleh `creator`, `admin`, dan `super_admin`. Physical visibility di storage adalah `private`.
2.  **MEMBER:** Dapat dilihat oleh semua user yang sudah login. Physical visibility di storage adalah `private`.
3.  **PUBLIC:** Dapat dilihat oleh siapa saja. Physical visibility di storage akan otomatis menjadi `public` (memungkinkan akses langsung tanpa signed URL pada S3).

## Security Policy

`App\Policies\CuratorMediaPolicy` menangani otorisasi untuk semua tindakan media:

- **View:** Mengikuti aturan privasi yang dijelaskan di atas.
- **Update/Delete:** Hanya diizinkan jika user adalah pembuat (`created_by`), memiliki role `admin`, atau `super_admin`.

## Integrasi UI Filament

Untuk mendukung pemilihan privasi saat mengunggah atau mengedit media, starter kit menggunakan `CustomMediaForm` di `app/Filament/Curator/CustomMediaForm.php`.

Form ini menambahkan field `Select` untuk privasi:

```php
Select::make('privacy')
    ->label('Privacy')
    ->options(Privacy::class)
    ->default(Privacy::PUBLIC)
    ->required()
```

## Pengujian (Testing)

Fitur ini telah dilindungi dengan unit & feature tests menggunakan Pest di `tests/Feature/CuratorMediaTest.php`. Pengujian mencakup:

- Otomatisasi pengisian `created_by`.
- Verifikasi logika akses untuk setiap level privasi.
- Verifikasi pembatasan hak edit dan hapus.

Untuk menjalankan tes:
```bash
php artisan test --filter=CuratorMediaTest
```
