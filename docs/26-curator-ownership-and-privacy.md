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
- **Visibility Sync:** Menggunakan event `saving` untuk sinkronisasi dua arah otomatis antara `privacy` dan `visibility` (physical storage):
    - Jika `privacy` diubah, `visibility` akan menyesuaikan (PUBLIC -> public, lainnya -> private).
    - Jika `visibility` diubah manual (misal via code), `privacy` akan menyesuaikan untuk menjaga integritas.
    - Menjamin status logical (`privacy`) selalu sinkron dengan status physical (`visibility`).

```php
// app/Models/CuratorMedia.php

protected static function booted(): void
{
    self::saving(function (self $media): void {
        // Priority 1: If privacy is changed, visibility must follow.
        if ($media->isDirty('privacy')) {
            $media->visibility = $media->privacy === Privacy::PUBLIC ? 'public' : 'private';
        }
        // Priority 2: If visibility is changed, privacy must follow (to stay in sync).
        elseif ($media->isDirty('visibility')) {
            $media->privacy = $media->visibility === 'public' ? Privacy::PUBLIC : Privacy::PRIVATE;
        }
        // Priority 3: Always ensure they match based on privacy source of truth.
        else {
            $media->visibility = $media->privacy === Privacy::PUBLIC ? 'public' : 'private';
        }
    });
}
```

## Enum Privacy

Enum `App\Enums\Privacy` mendefinisikan tiga level akses:

1.  **PRIVATE (Default):** Hanya dapat dilihat oleh `creator`, `admin`, dan `super_admin`. Physical visibility di storage adalah `private`.
2.  **MEMBER:** Dapat dilihat oleh semua user yang sudah login. Physical visibility di storage adalah `private`.
3.  **PUBLIC:** Dapat dilihat oleh siapa saja. Physical visibility di storage akan otomatis menjadi `public` (memungkinkan akses langsung tanpa signed URL pada S3).

## Security Policy (Permission-Only)

`App\Policies\CuratorMediaPolicy` menangani otorisasi menggunakan sistem **Permission** secara ketat (Best Practice Spatie & Filament Shield). Policy mengikuti konvensi penamaan `{Action}:{Model}` yang digunakan di proyek ini.

### Logika Akses
1.  **View:** 
    - Public/Member privacy diizinkan otomatis.
    - Private diizinkan jika punya permission `View:CuratorMedia` atau (pemilik dan punya `ViewOwn:CuratorMedia`).
2.  **Update/Delete/Restore:** 
    - Diizinkan jika punya permission `{Action}:CuratorMedia` (Global).
    - Diizinkan jika pemilik (`created_by`) dan punya permission `{Action}Own:CuratorMedia` (Ownership).

Hal ini memungkinkan Admin untuk memiliki semua permission (misal: `Update:CuratorMedia`), sedangkan Member hanya diberikan permission `*Own` (misal: `UpdateOwn:CuratorMedia`) saja.

### Proteksi Integritas
Aksi **Delete** dan **Force Delete** akan selalu diblokir jika media tersebut masih tercatat digunakan oleh model lain, meskipun user memiliki permission lengkap. Hal ini dijamin melalui `CheckMediaUsageAction`.

Untuk mendukung pemilihan privasi saat mengunggah atau mengedit media, starter kit menggunakan `CustomMediaForm` di `app/Filament/Curator/CustomMediaForm.php`.

Form ini menambahkan field `Select` untuk privasi:

```php
Select::make('privacy')
    ->label('Privacy')
    ->options(Privacy::class)
    ->default(Privacy::PUBLIC)
    ->required()
```

## Integritas & Pelacakan Penggunaan

Selain privasi, media juga dilacak penggunaannya untuk mencegah penghapusan aset yang masih aktif digunakan. Detail mengenai fitur ini dapat dilihat di [docs/27-media-usage-tracking.md](27-media-usage-tracking.md).

## Pengujian (Testing)

Fitur ini telah dilindungi dengan unit & feature tests menggunakan Pest di `tests/Feature/CuratorMediaTest.php`. Pengujian mencakup:

- Otomatisasi pengisian `created_by`.
- Verifikasi logika akses untuk setiap level privasi.
- Verifikasi pembatasan hak edit dan hapus.

Untuk menjalankan tes:
```bash
php artisan test --filter=CuratorMediaTest
```
