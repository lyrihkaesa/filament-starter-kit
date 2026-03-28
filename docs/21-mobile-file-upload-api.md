# Mobile File Upload API

Dokumen ini menjelaskan arah implementasi API upload file untuk mobile app.

Fokus utamanya adalah membuat flow upload yang:

- enak dipakai dari Flutter
- tetap aman untuk storage jangka panjang
- konsisten dengan final media library Curator
- tidak memaksa client melakukan step yang berbelit

## Ringkasan Arsitektur

Arsitektur yang dipakai untuk mobile nanti adalah:

- `uploads` untuk temporary upload lifecycle
- `curator` untuk final media library

Jadi mobile app tidak upload langsung ke tabel `curator`. Mobile akan:

1. prepare upload
2. upload file ke temporary storage
3. submit endpoint domain dengan `upload_id`
4. backend otomatis membuat record final di `curator`

## Goal

Tujuan desain ini adalah:

- mobile bisa upload file baru dengan flow yang sederhana
- mobile nanti juga bisa pick media existing dari Curator
- backend tetap menjadi pemilik validasi dan finalisasi file
- domain model tetap menyimpan referensi final ke `curator`

## Kontrak Dasar yang Disarankan

Untuk field file single, endpoint domain menerima salah satu dari:

- `*_upload_id`
- `*_curator_id`

Contoh:

- `avatar_upload_id` atau `avatar_curator_id`
- `thumbnail_upload_id` atau `thumbnail_curator_id`

Untuk field multiple:

- `*_upload_ids`
- `*_curator_ids`

Contoh:

- `attachment_upload_ids`
- `attachment_curator_ids`

Rule pentingnya:

- hanya boleh salah satu mode dalam satu field
- backend yang resolve hasil akhirnya ke record Curator

## Kenapa Bukan Client yang Membuat Record Curator?

Karena itu membuat mobile flow terlalu berbelit.

Yang tidak ingin dipaksa ke client:

1. upload file
2. finalize ke Curator
3. ambil `curator_id`
4. submit lagi ke endpoint domain

Yang lebih sehat:

1. upload file
2. dapat `upload_id`
3. submit form domain dengan `upload_id`
4. backend otomatis finalize ke Curator

Jadi mobile cukup fokus ke UX form, bukan ke detail internal media lifecycle.

## Flow Utama

### Upload file baru

1. mobile memanggil `POST /api/v1/uploads/prepare`
2. backend memvalidasi metadata awal dan membuat upload session
3. mobile upload file ke temporary storage
4. mobile submit endpoint domain dengan `*_upload_id`
5. backend memvalidasi object final
6. backend copy atau move file ke lokasi final
7. backend membuat record di tabel `curator`
8. backend menyimpan `curator_id` ke model domain

### Pick media existing

1. mobile memanggil daftar media dari Curator
2. user memilih media existing
3. mobile submit endpoint domain dengan `*_curator_id`
4. backend memvalidasi record Curator dan meng-attach ke model domain

## Endpoint yang Disarankan

### Upload lifecycle

```text
POST /api/v1/uploads/prepare
POST /api/v1/uploads/{upload}/mark-uploaded
GET /api/v1/uploads/{upload}
DELETE /api/v1/uploads/{upload}
```

### Curator / media library

```text
GET /api/v1/curator
GET /api/v1/curator/{curator}
```

### Endpoint domain

Contoh:

```text
PATCH /api/v1/me
POST /api/v1/users
PATCH /api/v1/users/{user}
POST /api/v1/posts
PATCH /api/v1/posts/{post}
```

Jadi upload tetap service generik, tetapi finalisasi file terjadi di endpoint domain yang memang memakai file tersebut.

## Contoh Kontrak Request

### Update avatar sendiri dengan upload baru

```json
{
  "avatar_upload_id": "01J..."
}
```

### Update avatar sendiri dengan pick media existing

```json
{
  "avatar_curator_id": "01J..."
}
```

### Create user oleh admin dengan avatar baru

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "secret-password",
  "avatar_upload_id": "01J..."
}
```

### Update post dengan thumbnail existing

```json
{
  "title": "Judul Post",
  "thumbnail_curator_id": "01J..."
}
```

## Resolver yang Dibutuhkan di Backend

Di backend sebaiknya ada satu resolver tunggal untuk pola ini:

- input: `upload_id | curator_id`
- output: final record Curator

Contoh tanggung jawab resolver:

- jika input `upload_id`
  - pastikan upload valid
  - pastikan purpose sesuai
  - finalize ke storage final
  - buat record Curator
  - kembalikan record Curator final
- jika input `curator_id`
  - pastikan record Curator valid
  - pastikan user berhak memakainya
  - kembalikan record Curator tersebut

Pola ini membuat code domain lebih rapi karena setiap action tidak perlu tahu detail upload lifecycle dari nol.

## Validation Strategy

Validasi tidak boleh hanya di client.

Lapisan yang disarankan:

1. client validation untuk UX
2. prepare validation di Laravel
3. finalize validation di Laravel

Validation juga harus berbasis `purpose`, misalnya:

- `user_avatar`
- `post_thumbnail`
- `post_attachment`

Setiap purpose bisa punya aturan berbeda untuk:

- mime yang diizinkan
- ukuran maksimum
- visibility default
- final directory

## Temporary Upload Cleanup

Karena mobile memakai temporary upload, cleanup wajib ada.

Minimal perlu:

- status upload yang jelas
- scheduler untuk upload expired
- penghapusan object `tmp/...` yang tidak pernah difinalize

Tanpa cleanup, bucket akan cepat berisi orphan file.

## Kenapa Final Media Tetap Curator?

Supaya Filament dan mobile tidak punya dua dunia media yang terpisah.

Dengan keputusan ini:

- admin di Filament memakai Curator
- mobile media picker nanti juga membaca Curator
- avatar, thumbnail, dan media lain punya final source of truth yang sama

Jadi perbedaan antara Filament dan mobile hanya ada di cara upload atau UI picker, bukan di backend media library.

## Kesimpulan

Arah implementasi mobile yang disarankan untuk starter kit ini adalah:

- temporary upload lewat `uploads`
- final media library tetap `curator`
- endpoint domain menerima `*_upload_id` atau `*_curator_id`
- backend otomatis resolve hasil akhir ke record Curator

Pola ini paling fleksibel untuk sekarang dan tetap aman untuk nanti saat mobile sudah memiliki media picker sendiri.
