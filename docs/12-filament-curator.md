# Filament Curator

Starter kit ini memakai **Filament Curator** sebagai final media library di panel Filament.

Dokumen ini menjelaskan posisi Curator di arsitektur project, konfigurasi storage yang dipakai sekarang, dan pola integrasi yang sudah disiapkan agar tetap nyaman dipakai ulang.

## Ringkasan Arsitektur

Arsitektur media di starter kit ini sekarang dibagi menjadi dua lapisan:

- `curator` untuk final media library
- `uploads` untuk temporary upload lifecycle API mobile di tahap berikutnya

Artinya:

- Filament memakai Curator sebagai media manager dan media picker
- file final yang dipakai model domain disimpan sebagai record di tabel `curator`
- upload temporary mobile nantinya tidak langsung masuk ke tabel `curator`

## Posisi Curator Saat Ini

Curator dipakai sebagai:

- media manager resmi di Filament
- final source of truth untuk media yang sudah sah dipakai aplikasi
- picker untuk field yang memang perlu memilih media existing
- target akhir untuk upload langsung dari Filament

Curator bukan pengganti flow upload API mobile berbasis signed upload. Untuk mobile, file temporary tetap direncanakan lewat tabel `uploads`, lalu hasil final akan dibuat menjadi record Curator.

## Storage Strategy

Starter kit ini sengaja dibuat sederhana:

- developer cukup memilih backend storage utama: `local` atau `s3`
- Curator mengikuti disk default aktif dari konfigurasi
- visibility default Curator tetap `private`
- field tertentu boleh override visibility jika memang harus `public`

Konfigurasi utamanya ada di [config/curator.php](https://github.com/lyrihkaesa/filament-starter-kit/blob/main/config/curator.php).

Default-nya sekarang:

- `CURATOR_DEFAULT_DISK` mengikuti `FILAMENT_FILESYSTEM_DISK` atau `FILESYSTEM_DISK`
- `CURATOR_DEFAULT_VISIBILITY=private`

Jadi mental model-nya:

- `local` = media final tetap bisa private dan dilayani via temporary URL
- `s3` = media final bisa private atau public sesuai visibility record

## Ownership & Privacy (Blameable)

Starter kit ini telah meningkatkan fitur Curator dengan sistem kepemilikan dan privasi yang lebih eksplisit:

- **Created By:** Setiap media yang diunggah kini mencatat siapa pembuatnya melalui kolom `created_by`.
- **Privacy Level:** Setiap media memiliki status privasi (`PRIVATE`, `MEMBER`, atau `PUBLIC`) yang menentukan siapa yang dapat melihat media tersebut.
- **Security Policy:** Hanya `creator`, `admin`, dan `super_admin` yang memiliki akses penuh untuk mengedit atau menghapus media.

Penjelasan mendalam mengenai fitur ini dapat dilihat di [docs/13-curator-privacy-and-tracking.md](13-curator-privacy-and-tracking.md).

## Dedicated Temporary Upload Disk

Untuk upload Filament dan Curator, temporary upload Livewire sekarang memakai disk terpisah.

Konfigurasi ini ada di [config/livewire.php](https://github.com/lyrihkaesa/filament-starter-kit/blob/main/config/livewire.php).

Disk temporary terpisah ini penting karena Curator membaca metadata dari file temporary setelah file dipindahkan ke lokasi final. Kalau temporary upload dan final media memakai root filesystem yang sama, metadata file temporary bisa hilang lebih dulu dan upload gagal.

Karena itu starter kit sekarang memakai:

- disk temporary upload khusus `uploads_tmp`
- disk final media mengikuti Curator default disk

Ini berlaku baik untuk `local` maupun `s3` sebagai storage final.

## Mixed Visibility: Private by Default, Public by Exception

Secara default, media Curator tetap dianggap private.

Ini keputusan yang disengaja supaya:

- dokumen atau media sensitif tidak otomatis terbuka
- starter kit aman untuk kebutuhan jangka panjang
- public media hanya dibuka jika memang jelas dibutuhkan

Saat ini pengecualiannya adalah field yang memang wajar public, misalnya avatar user. Untuk kasus seperti itu, field dapat override visibility langsung di komponen upload tanpa mengubah default global Curator.

Untuk API mobile nantinya, client juga boleh mengirim `requested_visibility`, tetapi backend tetap harus memutuskan `final_visibility` berdasarkan `purpose`, permission, dan policy server.

## Model Curator Kustom

Starter kit mengarahkan model Curator ke [app/Models/CuratorMedia.php](https://github.com/lyrihkaesa/filament-starter-kit/blob/main/app/Models/CuratorMedia.php).

Model ini dipakai supaya URL turunan media tetap konsisten untuk setup mixed visibility. Pendekatan ini lebih aman dibanding mengandalkan asumsi satu source image global ketika project mulai memiliki campuran media public dan private.

## Integrasi Domain Saat Ini

Field domain yang saat ini sudah terhubung ke Curator:

- `users.avatar_curator_id`
- `posts.thumbnail_curator_id`

Relasi model yang dipakai:

- user -> `avatarMedia`
- post -> `thumbnailCurator`

Dengan pola ini, model domain tidak menyimpan path file mentah sebagai source of truth utama. Yang disimpan adalah referensi ke record Curator.

## Avatar User: Direct Upload Tanpa Picker

Avatar user sekarang tidak lagi memakai `CuratorPicker` seperti thumbnail post.

Sebagai gantinya, avatar memakai komponen reusable [app/Filament/Forms/Components/CuratorFileUpload.php](https://github.com/lyrihkaesa/filament-starter-kit/blob/main/app/Filament/Forms/Components/CuratorFileUpload.php).

Tujuan pola ini:

- user bisa upload avatar langsung tanpa harus membuka media manager dulu
- hasil upload tetap otomatis masuk ke tabel `curator`
- field model tetap menyimpan `avatar_curator_id`
- pengalaman upload terasa seperti avatar uploader biasa, bukan picker media generik

Pemakaiannya saat ini ada di:

- [app/Filament/Pages/Auth/EditProfile.php](https://github.com/lyrihkaesa/filament-starter-kit/blob/main/app/Filament/Pages/Auth/EditProfile.php)
- [app/Filament/Resources/Users/Schemas/UserForm.php](https://github.com/lyrihkaesa/filament-starter-kit/blob/main/app/Filament/Resources/Users/Schemas/UserForm.php)

## CuratorFileUpload

`CuratorFileUpload` adalah komponen reusable berbasis `FileUpload` yang dibuat untuk kasus upload langsung tetapi final result-nya tetap record Curator.

Komponen ini sekarang menangani sendiri hal-hal berikut:

- hydrate preview dari `curator_id` yang sudah ada
- upload file baru lewat perilaku native `FileUpload`
- saat save, otomatis membuat atau memperbarui record Curator
- mengembalikan state final berupa `curator_id`

Prinsip API komponen ini sekarang dibuat konsisten:

- kalau sudah ada default bawaan `FileUpload`, gunakan default itu
- override cukup lewat method standar seperti `disk()`, `directory()`, `visibility()`, `acceptedFileTypes()`, `maxSize()`, dan lain-lain
- jangan menambahkan helper alias yang membuat API terasa berbeda tanpa alasan kuat

Contoh pemakaian:

```php
CuratorFileUpload::make('avatar_curator_id')
    ->relationship('avatarMedia', 'id')
    ->avatar()
    ->imageEditor()
    ->directory('avatars')
    ->visibility('public');
```

## Thumbnail Post: Tetap Picker

Untuk thumbnail post, starter kit saat ini masih memakai `CuratorPicker` di [app/Filament/Resources/Posts/Schemas/PostForm.php](https://github.com/lyrihkaesa/filament-starter-kit/blob/main/app/Filament/Resources/Posts/Schemas/PostForm.php).

Itu masih cocok karena kebutuhan thumbnail post lebih dekat ke media picker/editorial flow:

- bisa upload media baru dari Curator
- bisa memilih media existing
- tetap menyimpan `thumbnail_curator_id`

Jadi sekarang ada dua pola resmi yang sama-sama valid:

- `CuratorFileUpload` untuk direct upload yang hasil akhirnya otomatis menjadi record Curator
- `CuratorPicker` untuk browse dan pick media existing dari Curator

## Public dan Private di `local` dan `s3`

Cara berpikir yang dipakai di starter kit ini:

### Jika storage final adalah `local`

- media private dilayani dengan `temporaryUrl()` atau route signed yang setara
- media public tetap bisa diakses langsung jika field memang diarahkan ke visibility public

### Jika storage final adalah `s3`

- media `public` menggunakan URL biasa
- media `private` menggunakan signed URL / temporary URL

Jadi visibility tetap penting walaupun backend storage hanya dipilih dari `local` atau `s3`.

## Arah Pengembangan Berikutnya

Tahap berikut yang sudah direncanakan adalah upload API mobile dengan tabel `uploads`.

Pola yang akan dipakai:

- mobile upload baru -> pegang `upload_id`
- endpoint domain menerima `*_upload_id` atau `*_curator_id`
- backend otomatis resolve hasil akhir ke record Curator
- `prepare upload` akan membaca rule per `purpose`
- client boleh mengusulkan visibility, tetapi server menetapkan hasil akhirnya

Dengan pola itu:

- mobile tidak perlu membuat record Curator secara manual sebelum submit form domain
- Filament dan mobile tetap bermuara ke final media library yang sama, yaitu `curator`

## Kesimpulan

Arah starter kit ini sekarang adalah:

- Curator menjadi final media library tunggal
- default media tetap `private`
- field tertentu seperti avatar boleh override menjadi `public`
- avatar memakai direct upload yang otomatis terhubung ke Curator
- field lain yang cocok untuk picker tetap bisa memakai `CuratorPicker`
- API mobile nanti dibangun di atas `uploads -> curator`, bukan sistem media yang terpisah
