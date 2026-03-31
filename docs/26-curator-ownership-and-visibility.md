# Curator Ownership & Visibility (Blameable)

Starter kit ini mengimplementasikan sistem kepemilikan (ownership) dan visibilitas (visibility) pada Curator Media Manager untuk meningkatkan keamanan dan akuntabilitas data.

## Fitur Utama

- **Explicit Ownership:** Setiap record media mencatat siapa yang mengunggahnya (`created_by`).
- **Granular Visibility:** Media dapat diatur menjadi `PRIVATE`, `MEMBER`, atau `PUBLIC`.
- **Policy Enforcement:** Akses untuk mengedit dan menghapus media dibatasi hanya untuk pemilik, admin, atau super admin.

## Arsitektur Database

Kolom-kolom berikut telah ditambahkan ke tabel `curator` (via migration):

- `created_by` (UUID): Foreign key ke tabel `users`.
- `view_visibility` (String): Menyimpan status visibilitas media.

## Model CuratorMedia

Model `App\Models\CuratorMedia` telah diperbarui dengan:

- **Relationship:** Method `creator()` yang merujuk ke model `User`.
- **Casts:** Properti `view_visibility` di-cast ke enum `App\Enums\ViewVisibility`.
- **Auto-blame:** Menggunakan event `creating` untuk otomatis mengisi `created_by` dengan ID user yang sedang login.

```php
// app/Models/CuratorMedia.php

public function creator(): BelongsTo
{
    return $this->belongsTo(User::class, 'created_by');
}
```

## Enum ViewVisibility

Enum `App\Enums\ViewVisibility` mendefinisikan tiga level akses:

1.  **PRIVATE:** Hanya dapat dilihat oleh `creator`, `admin`, dan `super_admin`.
2.  **MEMBER:** Dapat dilihat oleh semua user yang sudah login.
3.  **PUBLIC:** Dapat dilihat oleh siapa saja, termasuk guest (unauthenticated).

## Security Policy

`App\Policies\CuratorMediaPolicy` menangani otorisasi untuk semua tindakan media:

- **View:** Mengikuti aturan `view_visibility` yang dijelaskan di atas.
- **Update/Delete:** Hanya diizinkan jika user adalah pemilik (`created_by`), memiliki role `admin`, atau `super_admin`.

## Integrasi UI Filament

Untuk mendukung pemilihan visibilitas saat mengunggah atau mengedit media, starter kit menggunakan `CustomMediaForm` di `app/Filament/Curator/CustomMediaForm.php`.

Form ini menambahkan field `Select` untuk visibilitas:

```php
Select::make('view_visibility')
    ->label('Visibility')
    ->options(ViewVisibility::class)
    ->default(ViewVisibility::PUBLIC)
    ->required()
```

## Pengujian (Testing)

Fitur ini telah dilindungi dengan unit & feature tests menggunakan Pest di `tests/Feature/CuratorMediaTest.php`. Pengujian mencakup:

- Otomatisasi pengisian `created_by`.
- Verifikasi logika akses untuk setiap level visibilitas.
- Verifikasi pembatasan hak edit dan hapus.

Untuk menjalankan tes:
```bash
php artisan test --filter=CuratorMediaTest
```
