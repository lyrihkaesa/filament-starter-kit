# Audit Trail Implementation Log

## Overview
Status: **Implemented & Upgraded to v5**
Date: April 8, 2026

Fitur Audit Trail telah ditingkatkan dari versi v4 ke **Spatie Laravel Activitylog v5** dan beralih dari plugin pihak ketiga ke implementasi **Native Filament Resource**.

## Technical Specs
- **Engine**: `spatie/laravel-activitylog` (^5.0)
- **UI**: Native Filament Resource (`ActivityResource`)
- **Compatibility**: Laravel 12, Filament v5, PHP 8.4+

## Implemented Changes

### 1. Dependency Upgrade
- Menghapus `pxlrbt/filament-activity-log`.
- Upgrade `spatie/laravel-activitylog` ke versi `^5.0`.
- ### Update 08 April 2026 (Fixing UUID Compatibility)
- **Error**: Seeder gagal karena `activity_log` menggunakan `bigint` untuk `subject_id` sedangkan project menggunakan UUID.
- **Fix**: Mengubah migrasi `2026_04_08_063542_create_activity_log_table` untuk menggunakan `nullableUuidMorphs` guna mendukung UUID.
- **Cleanup**: Menghapus migrasi tambahan (`add_event`, `add_batch_uuid`) dan mengonsolidasikannya ke migrasi utama agar lebih bersih.
- **Config**: Memperbarui `config/activitylog.php` ke versi v5 yang lebih ringkas.
- **Result**: `php artisan migrate:fresh --seed` berhasil dijalankan tanpa error.

### 2. Model Refactoring (`User.php`)
- Mengganti trait `Spatie\Activitylog\Traits\LogsActivity` menjadi `Spatie\Activitylog\Models\Concerns\HasActivity`.
- Memperbarui method `getActivitylogOptions()`:
    - Menggunakan `dontLogEmptyChanges()` (sebelumnya `dontSubmitEmptyLogs()`).

### 3. Global Activity Resource
- Membuat `App\Filament\Resources\Activities\ActivityResource` (Simple Resource).
- Mendukung:
    - Tabel log dengan filter `event` dan `subject_type`.
    - Pencarian log berdasarkan `subject_id` atau deskripsi.
    - View Action untuk melihat detail log.

### 4. User Integration
- Menambahkan Action `activities` pada `UsersTable`.
- Link diarahkan ke `ActivityResource` dengan pre-filter otomatis berdasarkan ID user tersebut.

### 5. Cleanup
- Menghapus CSS import plugin lama di `theme.css`.
- Menghapus file halaman `ListUserActivities.php` yang sudah tidak digunakan.

## Notes for Developers
Implementasi v5 lebih stabil dan mengikuti standar terbaru Spatie. Penggunaan `ActivityResource` secara global mempermudah manajemen log tanpa harus membuat halaman activities manual untuk setiap resource baru. Cukup gunakan pola link di `recordActions` tabel untuk melakukan filter cepat terhadap aktivitas model tertentu.

## Best Practice: Stable Morph Alias (Wajib)

Jangan simpan `subject_type` / `causer_type` sebagai FQCN (`App\Models\*`) di URL filter atau data baru.

Gunakan alias stabil, misalnya:
- `user` untuk `App\Models\User`
- `post` untuk `App\Models\Post`

Alasan:
- Aman saat rename model (`Post` -> `Article`) tanpa merusak data historis.
- URL filter jadi bersih dan readable.
- Mengurangi coupling antara data audit dengan nama class internal.

Implementasi yang dipakai:
- Definisikan mapping alias di satu tempat terpusat (`App\Support\Activity\ActivitySubjectType`).
- Register map di `AppServiceProvider` memakai `Relation::enforceMorphMap(...)`.
- UI/filter Filament hanya expose alias (`user`, `post`) ke user.
- Untuk backward compatibility, filter menerima data lama yang masih FQCN.

Catatan migrasi legacy data:
- Jika log lama masih berisi FQCN, lakukan backfill bertahap ke alias.
- Contoh SQL (sesuaikan DB):
```sql
UPDATE activity_log SET subject_type = 'user' WHERE subject_type = 'App\\Models\\User';
UPDATE activity_log SET subject_type = 'post' WHERE subject_type = 'App\\Models\\Post';
UPDATE activity_log SET causer_type = 'user' WHERE causer_type = 'App\\Models\\User';
```
