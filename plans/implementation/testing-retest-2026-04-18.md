# Retest & PHPStan Stabilization Notes (2026-04-18)

## Referensi Dokumen
- `plans/implementation/code-quality-report.md`
- `plans/implementation/testing-refactor.md`

## Tujuan
- Menjalankan ulang quality checks.
- Menurunkan error PHPStan menjadi 0 (setara 100% lulus static analysis).
- Mendokumentasikan perubahan kode yang dilakukan.

## Ringkasan Hasil
- `composer run test:types`: **PASS** (`[OK] No errors`)
- `artisan test --compact --stop-on-failure`: **belum clean** karena issue environment hashing saat proses seed (`RuntimeException: Could not verify the hashed value's configuration.`), terjadi dari `database/seeders/ShieldSeeder.php`.

## Perubahan Kode yang Dilakukan

### 1) Type narrowing untuk ID user deleter
- File: `app/Filament/Curator/Actions/CuratorMediaDeleteAction.php`
- Perubahan:
  - Nilai `auth()->id()` dinormalisasi menjadi `?string` sebelum dikirim ke action delete.
- Alasan:
  - Signature `DeleteCuratorMediaAction::handle()` mengharapkan `?string`.

### 2) Callback collection dibuat kompatibel dengan kontrak `mixed`
- File: `app/Filament/Curator/Actions/CuratorMediaDeleteBulkAction.php`
- Perubahan:
  - Callback `filter()` menerima `mixed`, lalu dipersempit dengan `instanceof CuratorMedia`.
- Alasan:
  - Menghindari pelanggaran kontravarian parameter callable.

### 3) Konsistensi return type closure action
- File: `app/Filament/Curator/Actions/CuratorMediaUsagesAction.php`
- Perubahan:
  - `badge()` dan `tooltip()` disesuaikan jadi `string` (tanpa nullable yang tidak diperlukan).
- Alasan:
  - Menutup warning `return.unusedType`.

### 4) Guard untuk URL preview di halaman edit media
- File: `app/Filament/Pages/Media/EditMedia.php`
- Perubahan:
  - `Action::url()` diubah ke closure dengan guard `instanceof CuratorMedia`.
- Alasan:
  - Mencegah akses properti pada tipe gabungan `Model|int|string|null`.

### 5) Hardening `ActivityResource` untuk data mixed
- File: `app/Filament/Resources/Activities/ActivityResource.php`
- Perubahan:
  - Menambahkan `hasProperties()` agar tidak memanggil method collection pada nilai nullable/mixed.
  - Filter query `subject_id` dan date range diberi guard `is_scalar()` sebelum cast string.
  - Normalisasi array key melalui helper `normalizeArrayKeys()` agar return type tetap `array<string,mixed>`.
  - `stringifyValue()` dirapikan agar tidak melakukan cast yang berisiko terhadap tipe unsupported.
- Alasan:
  - Menutup error `method.nonObject`, `cast.string`, dan `return.type`.

### 6) Callback bulk delete posts diperketat tipenya
- File: `app/Filament/Resources/Posts/Tables/PostsTable.php`
- Perubahan:
  - Callback `each()` menerima `mixed` lalu guard `instanceof Post`.
- Alasan:
  - Menyesuaikan kontrak Collection generic dari Filament/Laravel.

### 7) Render Livewire dipersempit dengan `assert()`
- File: `app/Livewire/Posts/Show.php`
- Perubahan:
  - Menghapus inline ignore dan menggunakan:
    - `assert($view instanceof View);`
    - return `View` secara eksplisit.
- Alasan:
  - Sesuai arahan boleh pakai `assert()` untuk membantu PHPStan.

### 8) Atribut URL model diperjelas non-null
- File: `app/Models/CuratorMedia.php`
- Perubahan:
  - Return PHPDoc untuk accessor `url()` diubah menjadi `Attribute<string, never>`.
  - Closure getter dikunci return `string`.
- Alasan:
  - Getter memang selalu mengembalikan string URL.

### 9) Navigation sort Filament diberi fallback integer
- File: `app/Providers/Filament/AppPanelProvider.php`
- Perubahan:
  - `navigationSort(...)` memakai fallback `?? 0`.
- Alasan:
  - API plugin meminta `int|Closure`, bukan `int|null`.

## Catatan Testing Ulang
- Static analysis (`phpstan`) sudah **0 error**.
- Test suite aplikasi masih terhambat issue hashing configuration saat seeding database test.
- Error ini muncul dari seeder (`ShieldSeeder`) dan bukan dari area file yang diubah untuk perbaikan PHPStan.

## Next Action yang Disarankan
1. Verifikasi konfigurasi hash di environment testing (`config/hashing.php`, env test terkait driver/cost).
2. Jalankan ulang `artisan test --compact` setelah issue hashing diselesaikan.
3. Jika perlu, isolasi seeding test yang sensitif hash agar tidak memblokir suite utama.
