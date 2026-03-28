# Issue: Stabilize Curator Temporary Uploads and Mixed Visibility Media

## Summary

Setelah integrasi awal Filament Curator, ditemukan dua masalah teknis penting yang harus dibereskan agar media manager stabil untuk kebutuhan jangka panjang:

- upload temporary Curator melalui Livewire gagal ketika disk temporary dan disk final berada pada root filesystem yang sama
- preview URL image gagal ketika record Curator berisi campuran media `private` dan `public`

Issue ini merangkum perubahan lanjutan untuk menstabilkan arsitektur media pada starter kit:

- Livewire temporary upload dipindahkan ke disk khusus `uploads_tmp`
- Curator tetap `private` secara default untuk media manager umum
- field domain tertentu seperti avatar dan thumbnail diarahkan eksplisit ke media `public`
- model Curator dioverride agar URL turunan image tetap aman untuk media `private` maupun `public`

## Background

Pada starter kit ini, `FILESYSTEM_DISK=local` mengarah ke storage private (`storage/app/private`). Saat Curator mengunggah file melalui Livewire:

1. file masuk ke `livewire-tmp`
2. Curator memindahkan file ke lokasi final
3. package masih mencoba membaca metadata dari file temporary

Jika disk temporary dan disk final sama, file temporary sudah tidak ada saat metadata dibaca, sehingga muncul error metadata seperti:

- `Unable to retrieve the file_size for file at location: livewire-tmp/...`

Setelah itu muncul masalah kedua:

- Curator memakai Glide dengan asumsi source image global tunggal
- ketika media tersimpan di disk yang berbeda visibility/root-nya, preview image bisa salah mencari file dan menghasilkan `FileNotFoundException`

## Goal

Membuat integrasi Curator aman dipakai untuk:

- media manager private secara default
- field tertentu yang wajib public seperti avatar dan thumbnail
- arsitektur upload yang kelak dapat berkembang ke mobile API tanpa menumpuk technical debt

## Scope

- tambahkan disk khusus `uploads_tmp` pada filesystem config
- publish dan atur `config/livewire.php` agar temporary upload memakai disk khusus
- pastikan starter kit tidak lagi menyimpan Livewire temporary upload di disk final Curator
- override model Curator ke `App\Models\CuratorMedia`
- ubah turunan URL image Curator agar memakai URL final per media, bukan Glide source global
- arahkan avatar dan thumbnail ke disk `public` dengan visibility `public`
- pertahankan default Curator global tetap `private`
- tambah test untuk konfigurasi temporary upload dan perilaku URL media

## Non-Goals

- belum membangun access level `members`
- belum menambahkan pilihan bebas `public/private` dari sisi user/client
- belum membangun policy media sharing lintas user
- belum membangun signed upload API mobile berbasis `upload_id -> curator`

## Proposed Technical Direction

### 1. Dedicated temporary upload disk

Gunakan disk baru:

- `uploads_tmp`

Dengan tujuan:

- temporary upload tidak bentrok dengan lokasi final media
- Curator dapat menyelesaikan proses upload tanpa kehilangan metadata file temporary

### 2. Curator remains private by default

Media manager umum harus tetap default:

- disk mengikuti config Curator
- visibility default `private`

Ini lebih aman untuk kebutuhan jangka panjang karena tidak semua media layak menjadi public.

### 3. Public-only field configuration for selected domain fields

Beberapa field domain harus tegas diarahkan ke media public:

- `users.avatar_curator_id`
- `posts.thumbnail_curator_id`

Konfigurasinya langsung di `CuratorPicker`, bukan mengubah default global Curator.

### 4. Custom Curator model for URL behavior

Override model package ke:

- `App\Models\CuratorMedia`

Tujuan:

- `url` tetap mengikuti mekanisme disk record masing-masing
- media private tetap menghasilkan signed/temporary URL jika driver mendukung
- media public tetap menghasilkan direct URL
- turunan URL seperti `thumbnail_url`, `medium_url`, `large_url` tidak lagi memaksa Glide source global yang rawan salah path pada mixed visibility setup

## Expected Changes

- `config/filesystems.php`
  - tambah disk `uploads_tmp`
- `config/livewire.php`
  - set temporary file upload disk ke `uploads_tmp`
- `config/curator.php`
  - model diarahkan ke `App\Models\CuratorMedia`
- `app/Models/CuratorMedia.php`
  - custom URL behavior untuk media turunan
- form/avatar/thumbnail resource Filament
  - avatar dan thumbnail diarahkan ke disk `public` + visibility `public`
- test baru
  - validasi disk temporary Livewire terpisah dari disk final Curator
  - validasi URL media Curator bekerja konsisten

## Acceptance Criteria

- Upload Curator di Filament tidak lagi gagal dengan error metadata `livewire-tmp`
- Avatar dan thumbnail baru tersimpan sebagai media public
- Media manager umum Curator tetap default private
- Preview/render media tidak lagi gagal saat project memiliki campuran media private dan public
- Test terkait Curator visibility dan temporary upload tersedia dan lulus
- `pint`, `phpstan`, dan `rector --dry-run` lulus setelah perubahan

## Risks

- menonaktifkan Glide-derived URLs untuk mixed visibility berarti optimasi resize image perlu dirancang ulang jika nanti dibutuhkan serius
- jika nanti ingin access level `members`, perlu layer policy/domain tambahan, tidak cukup hanya mengandalkan visibility filesystem
- bila nanti berpindah ke S3-compatible multi-bucket, aturan mapping disk dan URL provider perlu diperjelas lagi

## Follow-up Suggestions

Tahap berikut yang disarankan setelah issue ini selesai:

1. definisikan enum/domain access level seperti `private`, `public`, `members`
2. buat policy media sharing lintas user
3. rancang endpoint API mobile yang bisa menerima `*_upload_id` atau `*_curator_id`
4. dokumentasikan dengan jelas field mana yang wajib public dan mana yang harus private
