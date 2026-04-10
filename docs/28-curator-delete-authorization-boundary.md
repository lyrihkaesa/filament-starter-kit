# Curator Delete Guard & Authorization Boundary

Dokumen ini mencatat implementasi pada **10 April 2026** untuk:

1. Proteksi delete media Curator yang masih dipakai (in-use).
2. Pemisahan boundary authorization agar `app/Actions/**` tetap netral permission.

## Ringkasan Keputusan

- Business Action di `app/Actions/**` tidak boleh cek permission.
- Permission check hanya dilakukan di:
  - Policy
  - Controller / API layer
  - Filament Action
- Delete media in-use default-nya diblok.
- Delete media in-use bisa di-override dengan permission khusus.

## Permission yang Dipakai

- `Delete:CuratorMedia` (global)
- `DeleteOwn:CuratorMedia` (owner)
- `DeleteUsed:CuratorMedia` (override delete in-use)
- `ForceDelete:CuratorMedia` (global force delete)
- `ForceDeleteOwn:CuratorMedia` (owner force delete)
- `ForceDeleteUsed:CuratorMedia` (override force delete in-use)

## Perubahan Implementasi

### 1) Policy: aturan final delete in-use

File:
- `app/Policies/CuratorMediaPolicy.php`

Behavior:
- User biasa dengan `DeleteOwn:CuratorMedia` tetap tidak bisa hapus media in-use.
- Admin (`Delete:CuratorMedia`) atau user dengan `DeleteUsed:CuratorMedia` bisa hapus media in-use.
- Pola serupa diterapkan untuk force delete dengan `ForceDeleteUsed:CuratorMedia`.

### 2) Filament Action: disable tombol delete untuk non-override

File:
- `app/Filament/Curator/Actions/CuratorMediaDeleteAction.php`
- `app/Filament/Curator/Actions/CuratorMediaDeleteBulkAction.php`

Behavior:
- Row delete dan bulk delete diblokir di UI jika media in-use dan user tidak punya override.
- Tooltip dan modal description menampilkan alasan block.

### 3) Business Action tetap netral permission

File:
- `app/Actions/Media/DeleteCuratorMediaAction.php`

Behavior:
- Tidak ada call ke `$user->can()` / permission check.
- Signature:
  - `handle(CuratorMedia $media, ?string $deleterId = null, bool $allowDeleteWhenUsed = false): bool`
- Authorization diputuskan caller (Policy/Filament Action/Controller).

### 4) Curator edit page pakai page custom project

File:
- `config/curator.php`

Behavior:
- `resource.pages.edit` diarahkan ke `App\Filament\Pages\Media\EditMedia`.
- Proteksi delete custom konsisten di halaman edit.

## Pengujian

Test yang ditambahkan/diupdate:

- `tests/Browser/CuratorMediaManagementTest.php`
- `tests/Unit/Policies/CuratorMediaPolicyTest.php`
- `tests/Unit/Filament/Curator/CuratorMediaActionsAndTableTest.php`
- `tests/Feature/CuratorMediaTest.php`
- `tests/Feature/Filament/CuratorMediaResourceTest.php`
- `tests/Feature/MediaUsageTest.php`

Status terakhir: semua test terkait skenario ini lulus.
