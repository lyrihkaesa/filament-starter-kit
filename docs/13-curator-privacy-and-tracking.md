# Curator Ownership, Privacy & Usage Tracking

Dokumen ini menjelaskan dua aspek penting dari Filament Curator di starter kit ini:

1. **Ownership & Privacy** — siapa yang memiliki media dan siapa yang bisa melihatnya
2. **Usage Tracking** — pelacakan penggunaan media untuk menjaga integritas data

---

## Bagian 1: Ownership & Privacy (Blameable)

Starter kit ini mengimplementasikan sistem **kepemilikan** (ownership) dan **privasi** (privacy) pada Curator Media Manager untuk meningkatkan keamanan dan akuntabilitas data.

### Fitur Utama

- **Explicit Ownership:** Setiap record media mencatat siapa yang mengunggahnya melalui kolom `created_by`.
- **Granular Privacy:** Media dapat diatur menjadi `PRIVATE`, `MEMBER`, atau `PUBLIC`.
- **Automatic Visibility Sync:** Sistem secara otomatis mensinkronkan status `privacy` (logical) dengan `visibility` (physical) di storage (misalnya S3).
- **Policy Enforcement:** Akses untuk mengedit dan menghapus media dibatasi hanya untuk pemilik, admin, atau super admin.

---

### Arsitektur Database

Kolom-kolom berikut telah ditambahkan ke tabel `curator` (via migration):

- `created_by` (UUID): Foreign key ke tabel `users`.
- `deleted_by` (UUID): Siapa yang menghapus (Soft Delete).
- `deleted_at` (Timestamp): Waktu penghapusan.
- `privacy` (String): Menyimpan status privasi media (Default: `private`).

---

### Model CuratorMedia

Model `App\Models\CuratorMedia` telah diperbarui dengan:

- **Relationship:** Method `creator()` yang merujuk ke model `User`.
- **Casts:** Properti `privacy` di-cast ke enum `App\Enums\Privacy`.
- **Auto-blame:** Menggunakan event `creating` untuk otomatis mengisi `created_by` dengan ID user yang sedang login.
- **Visibility Sync:** Menggunakan event `saving` untuk sinkronisasi dua arah otomatis antara `privacy` dan `visibility` (physical storage).

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

---

### Enum Privacy

Enum `App\Enums\Privacy` mendefinisikan tiga level akses:

1. **PRIVATE (Default):** Hanya dapat dilihat oleh `creator`, `admin`, dan `super_admin`. Physical visibility di storage adalah `private`.
2. **MEMBER:** Dapat dilihat oleh semua user yang sudah login. Physical visibility di storage adalah `private`.
3. **PUBLIC:** Dapat dilihat oleh siapa saja. Physical visibility di storage akan otomatis menjadi `public` (memungkinkan akses langsung tanpa signed URL pada S3).

---

### Security Policy (Permission-Only)

`App\Policies\CuratorMediaPolicy` menangani otorisasi menggunakan sistem **Permission** secara ketat (Best Practice Spatie & Filament Shield). Policy mengikuti konvensi penamaan `{Action}:{Model}` yang digunakan di proyek ini.

#### Logika Akses

1. **View:**
   - Public/Member privacy diizinkan otomatis.
   - Private diizinkan jika punya permission `View:CuratorMedia` atau (pemilik dan punya `ViewOwn:CuratorMedia`).

2. **Update/Delete/Restore:**
   - Diizinkan jika punya permission `{Action}:CuratorMedia` (Global).
   - Diizinkan jika pemilik (`created_by`) dan punya permission `{Action}Own:CuratorMedia` (Ownership).

Hal ini memungkinkan Admin untuk memiliki semua permission (misalnya `Update:CuratorMedia`), sedangkan Member hanya diberikan permission `*Own` (misalnya `UpdateOwn:CuratorMedia`) saja.

#### Proteksi Integritas

Aksi **Delete** dan **Force Delete** akan diblokir jika media masih digunakan, **kecuali** user memiliki permission override khusus.

Permission override yang dipakai:
- `DeleteUsed:CuratorMedia`
- `ForceDeleteUsed:CuratorMedia`

Integritas ini dijaga lewat `CheckMediaUsageAction` + `CuratorMediaPolicy`.

---

### Pemilihan Privacy di Form

Untuk mendukung pemilihan privasi saat mengunggah atau mengedit media, starter kit menggunakan `CustomMediaForm` di `app/Filament/Curator/CustomMediaForm.php`.

Form ini menambahkan field `Select` untuk privasi:

```php
Select::make('privacy')
    ->label('Privacy')
    ->options(Privacy::class)
    ->default(Privacy::PUBLIC)
    ->required()
```

---

## Bagian 2: Media Usage Tracking & Integrity

Starter kit ini dilengkapi dengan sistem pelacakan penggunaan media secara eksplisit untuk menjamin integritas data, mencegah "Broken Images", dan mendukung siklus hidup aset yang aman.

### Mengapa Menggunakan Usage Tracking?

Secara default, Media Manager tidak tahu di mana file media digunakan. Sistem pelacakan ini memberikan manfaat:

1. **Integritas Data:** Mencegah penghapusan media yang masih digunakan oleh model lain (Post, User, dll).
2. **Audit Aset:** Mengetahui dengan pasti aset mana saja yang masih aktif.
3. **Soft Delete & Restore:** Mendukung fitur "Recycle Bin" di mana media yang dihapus tidak langsung hilang, namun otomatis tersembunyi dari relasi model yang menggunakannya.

---

### Arsitektur Database: Tabel `curator_media_usages`

Tabel ini mencatat relasi antara media dan model yang menggunakannya secara Polymorphic:

- `curator_media_id` (UUID): Referensi ke media.
- `model_id`, `model_type`: Model domain (misalnya `App\Models\Post`).
- `field_name`: Nama kolom (misalnya `thumbnail_curator_id`).

---

### Action Pattern untuk Usage Tracking

Sistem ini menggunakan pola **Action Pattern** dengan method `handle()` dan `DB::transaction` untuk menjamin atomisitas data.

#### 1. `SyncMediaUsageAction`

Menyinkronkan penggunaan media saat model dibuat atau diperbarui:

```php
resolve(SyncMediaUsageAction::class)->handle($model, 'field_name', $curatorId);
```

#### 2. `DeleteCuratorMediaAction`

Melakukan Soft Delete pada media dan mencatat siapa yang melakukan penghapusan (`deleted_by`):

```php
resolve(DeleteCuratorMediaAction::class)->handle(
    media: $media,
    deleterId: $user->id,
    allowDeleteWhenUsed: false,
);
```

#### 3. `DeleteAllMediaUsagesAction`

Membersihkan catatan penggunaan media saat model pemilik (User/Post) benar-benar dihapus (Force Delete).

#### 4. `ListCuratorMediaUsagesAction`

Mengambil daftar lokasi penggunaan media untuk kebutuhan audit di UI Curator:

```php
resolve(ListCuratorMediaUsagesAction::class)->handle($media);
```

---

### Proteksi Penghapusan

`App\Policies\CuratorMediaPolicy` menerapkan aturan industri:

- **Cegah Penghapusan Default:** Media tidak dapat di-*delete* atau di-*force delete* jika statusnya masih tercatat digunakan (`CheckMediaUsageAction`).
- **Override Terbatas:** Penghapusan media in-use hanya boleh lewat permission override (`DeleteUsed:CuratorMedia` / `ForceDeleteUsed:CuratorMedia`) atau permission global yang setara.
- **Otomatis Tersembunyi:** Karena menggunakan `SoftDeletes` pada model `CuratorMedia`, relasi Eloquent (seperti `$user->avatarMedia`) secara otomatis akan mengembalikan `null` jika media tersebut sedang berada di "Recycle Bin" (di-*soft delete*). Jika di-*restore*, maka akan muncul kembali secara otomatis.

Selain proteksi di policy, starter kit ini juga menambahkan proteksi langsung di layer Filament Curator:

- halaman edit media menampilkan warning kalau media masih dipakai
- row delete action di tabel media diblok untuk media yang masih dipakai
- bulk delete dibatalkan jika ada media terpilih yang masih dipakai

Jadi proteksinya berlapis:

1. UI memberi warning dan mencegah aksi yang salah.
2. Policy menolak delete in-use jika user tidak punya override.
3. `DeleteCuratorMediaAction` tetap aman by default (`allowDeleteWhenUsed=false`).

Pendekatan ini dibuat defensif supaya integritas aset tidak bergantung pada satu lapisan saja.

## Orkestrasi Wajib Lewat Action (Tanpa Observer)

Project ini sengaja tidak mengandalkan observer untuk sinkronisasi usage media agar alur bisnis lebih eksplisit dan mudah di-maintain.

Pola yang dipakai:

1. **Create/Update domain model** memanggil Action domain (`CreatePostAction`, `UpdatePostAction`, `CreateUserAction`, `UpdateUserAction`).
2. Action domain tersebut memanggil `SyncMediaUsageAction`.
3. **Delete domain model** memanggil Action delete domain (`DeletePostAction`, dll) yang memanggil `DeleteAllMediaUsagesAction`.

Keuntungan:

- Alur mudah ditelusuri dari controller/API/Filament.
- Behavior konsisten antara panel dan API.
- Tidak ada side-effect tersembunyi dari observer.

### Catatan Naming di Closure Action Filament

Untuk code style internal project ini, service injection di closure action Filament sebaiknya tidak diberi nama `$action` jika closure tersebut sudah berada di context action Filament.

Contoh yang disarankan:

```php
->using(fn (CuratorMedia $record, DeleteCuratorMediaAction $deleteMediaAction): bool => $deleteMediaAction->handle($record))
```

Alasannya:

- `$action` secara mental sudah identik dengan action Filament
- nama seperti `$deleteMediaAction` lebih mudah dipahami developer baru
- mengurangi ambiguitas saat membaca closure `before()`, `using()`, `action()`, dan hook action lainnya

---

## Pengujian (Testing)

Fitur ini telah dilindungi dengan unit & feature tests menggunakan Pest:

- `tests/Feature/CuratorMediaTest.php`: Menguji ownership, soft delete, blameable, sinkronisasi privasi, dan logika akses per level privasi.
- `tests/Feature/MediaUsageTest.php`: Menguji integritas tabel usage.

Untuk menjalankan tes:

```bash
php artisan test --compact --filter=CuratorMediaTest
php artisan test --compact --filter=MediaUsageTest
```
