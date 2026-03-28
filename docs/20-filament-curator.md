# Filament Curator

Starter kit ini memakai **Filament Curator** sebagai media manager dan media picker resmi di panel Filament.

## Posisi Curator di Arsitektur

Untuk saat ini, peran Curator adalah:

- final media library di tabel `curator`
- media manager untuk admin di Filament
- picker untuk field yang membutuhkan media existing

Curator bukan pengganti flow upload API mobile berbasis signed upload. Untuk mobile, file temporary tetap direncanakan lewat tabel `uploads`, lalu file final akan masuk ke Curator.

## Field yang Sudah Diintegrasikan

Saat ini minimal field berikut sudah memakai Curator picker:

- avatar user
- thumbnail post

## Konfigurasi Dasar

Konfigurasi Curator ada di [`config/curator.php`](E:/Projects/Laravel/filament-starter-kit/config/curator.php).

Default starter kit diarahkan ke:

- disk mengikuti `FILAMENT_FILESYSTEM_DISK` atau `FILESYSTEM_DISK`
- visibility default `private`
- navigasi Curator muncul di grup `Content`

## Relasi Domain

Agar integrasi Curator tidak memutus kompatibilitas lama terlalu keras, starter kit menambahkan foreign key baru:

- `users.avatar_curator_id`
- `posts.thumbnail_curator_id`

Filament memakai relasi Curator tersebut untuk picker dan preview.

## Catatan

- kalau ingin private media penuh di object storage, pastikan disk yang dipakai mendukung `temporaryUrl()`
- Curator cocok untuk picker/admin media manager
- upload API mobile akan dibangun terpisah di atas tabel `uploads`, lalu hasil finalnya masuk ke Curator
