# Curator Media Usage Tracking & Integrity

Starter kit ini dilengkapi dengan sistem pelacakan penggunaan media secara eksplisit untuk menjamin integritas data, mencegah "Broken Images", dan mendukung siklus hidup aset yang aman.

## Mengapa Menggunakan Usage Tracking?

Secara default, Media Manager tidak tahu di mana file media digunakan. Sistem pelacakan ini memberikan manfaat:
1.  **Integritas Data:** Mencegah penghapusan media yang masih digunakan oleh model lain (Post, User, dll).
2.  **Audit Aset:** Mengetahui dengan pasti aset mana saja yang masih aktif.
3.  **Soft Delete & Restore:** Mendukung fitur "Recycle Bin" di mana media yang dihapus tidak langsung hilang, namun otomatis tersembunyi dari relasi model yang menggunakannya.

## Arsitektur Database

### 1. Tabel `curator` (Enhanced)
- `created_by` (UUID): Siapa yang mengunggah.
- `deleted_by` (UUID): Siapa yang menghapus (Soft Delete).
- `deleted_at` (Timestamp): Waktu penghapusan.
- `privacy` (String): Level akses (`PRIVATE`, `MEMBER`, `PUBLIC`).

### 2. Tabel `curator_media_usages`
Tabel ini mencatat relasi antara media dan model yang menggunakannya secara Polymorphic:
- `curator_media_id` (UUID): Referensi ke media.
- `model_id`, `model_type`: Model domain (misal: `App\Models\Post`).
- `field_name`: Nama kolom (misal: `thumbnail_curator_id`).

## Best Practice Action Pattern

Sistem ini menggunakan pola **Action Pattern** dengan method `handle()` dan `DB::transaction` untuk menjamin atomisitas data.

### 1. `SyncMediaUsageAction`
Menyinkronkan penggunaan media saat model dibuat atau diperbarui.
```php
resolve(SyncMediaUsageAction::class)->handle($model, 'field_name', $curatorId);
```

### 2. `DeleteCuratorMediaAction`
Melakukan Soft Delete pada media dan mencatat siapa yang melakukannya.
```php
resolve(DeleteCuratorMediaAction::class)->handle($media, $user);
```

### 3. `DeleteAllMediaUsagesAction`
Membersihkan catatan penggunaan media saat model pemilik (User/Post) dihapus.

## Proteksi Penghapusan

`App\Policies\CuratorMediaPolicy` menerapkan aturan industri:
- **Cegah Penghapusan:** Media tidak dapat di-*delete* atau di-*force delete* jika statusnya masih tercatat digunakan (`CheckMediaUsageAction`).
- **Otomatis Tersembunyi:** Karena menggunakan `SoftDeletes` pada model `CuratorMedia`, relasi Eloquent (seperti `$user->avatarMedia`) secara otomatis akan mengembalikan `null` jika media tersebut sedang berada di "Recycle Bin" (di-*soft delete*). Jika di-*restore*, maka akan muncul kembali secara otomatis.

## Pengujian (Testing)

Fitur ini diuji secara ketat di:
- `tests/Feature/MediaUsageTest.php`: Menguji integritas tabel usage.
- `tests/Feature/CuratorMediaTest.php`: Menguji Soft Delete, Blameable (`deleted_by`), dan sinkronisasi privasi.
