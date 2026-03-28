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
PUT /api/v1/uploads/{upload}/file
POST /api/v1/uploads/{upload}/mark-uploaded
GET /api/v1/uploads/{upload}
DELETE /api/v1/uploads/{upload}
```

> **Catatan Uji Coba (Bruno API Client)**
> Kami telah menyediakan koleksi Bruno API di folder `api-tests/bruno/v1/03-Uploads` agar Anda dapat langsung menguji flow ini tanpa menulis kode klien. 
> 
> Flow pengujian di Bruno:
> 1. Jalankan `01-Prepare` (upload_id akan otomatis tersimpan di environment)
> 2. Jalankan `02-UploadFileLocal` (pilih file Binary di tab Body)
> 3. Jalankan `03-MarkUploaded` (file akan difinalisasi)

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

## `prepare upload` Harus Berbasis `purpose`

`prepare upload` jangan dibuat satu rule longgar untuk semua file.

Yang lebih sehat adalah setiap upload harus membawa `purpose`, misalnya:

- `user_avatar`
- `post_thumbnail`
- `post_attachment`
- `post_video`
- `document_file`

`purpose` ini dipakai backend untuk menentukan:

- mime yang diizinkan
- ukuran maksimum
- apakah file harus image, document, atau video
- final directory
- visibility default
- apakah client boleh meminta visibility tertentu

Jadi backend tidak menebak dari nama field saja.

## Contoh Request `prepare upload`

```json
{
  "purpose": "user_avatar",
  "file_name": "avatar.jpg",
  "content_type": "image/jpeg",
  "size": 1832451,
  "requested_visibility": "public"
}
```

Contoh lain untuk attachment:

```json
{
  "purpose": "post_attachment",
  "file_name": "proposal.pdf",
  "content_type": "application/pdf",
  "size": 932145,
  "requested_visibility": "private"
}
```

Contoh lain untuk video:

```json
{
  "purpose": "post_video",
  "file_name": "teaser.mp4",
  "content_type": "video/mp4",
  "size": 24500912,
  "requested_visibility": "private"
}
```

## Registry Rule per `purpose`

Di backend sebaiknya ada registry atau config tunggal untuk rule upload. Misalnya secara konsep:

- `user_avatar`
  - allowed mime: `image/jpeg`, `image/png`, `image/webp`
  - max size: `2 MB`
  - default visibility: `public`
  - allowed visibility: `public`
  - final directory: `avatars`
- `post_thumbnail`
  - allowed mime: `image/jpeg`, `image/png`, `image/webp`
  - max size: `3 MB`
  - default visibility: `public`
  - allowed visibility: `public`
  - final directory: `posts/thumbnails`
- `post_attachment`
  - allowed mime: `application/pdf`
  - max size: `10 MB`
  - default visibility: `private`
  - allowed visibility: `private`, `public`
  - final directory: `posts/attachments`
- `post_video`
  - allowed mime: `video/mp4`, `video/quicktime`
  - max size: `100 MB`
  - default visibility: `private`
  - allowed visibility: `private`
  - final directory: `posts/videos`

Dengan pola ini, semua keputusan penting tetap ada di server.

## `requested_visibility` dari Client

Client boleh mengirim `requested_visibility`, tapi client tidak boleh menjadi penentu final.

Cara berpikir yang aman:

- client boleh mengusulkan `public` atau `private`
- backend memeriksa apakah `purpose` tersebut memang mengizinkan pilihan itu
- backend memeriksa role atau permission user
- backend menetapkan `final_visibility`

Jadi server tetap pemilik keputusan.

Contoh hasil yang sehat:

- avatar user meminta `private` -> backend tetap memaksa `public`
- attachment post meminta `public` -> backend boleh menerima jika rule dan permission mengizinkan
- video meminta `public` -> backend menolak jika purpose hanya boleh `private`

## Response `prepare upload`

Contoh response yang disarankan:

```json
{
  "message": "Upload prepared successfully.",
  "data": {
    "upload_id": "01J...",
    "purpose": "user_avatar",
    "status": "prepared",
    "disk": "s3",
    "temporary_path": "tmp/uploads/user-avatar/01J...",
    "final_visibility": "public",
    "max_size": 2097152,
    "accepted_file_types": [
      "image/jpeg",
      "image/png",
      "image/webp"
    ],
    "upload_type": "put",
    "upload_url": "https://storage.example.com/...",
    "method": "PUT",
    "headers": {
      "Content-Type": "image/jpeg"
    },
    "expires_at": "2026-03-29T10:15:30+07:00"
  }
}
```

Dengan kontrak seperti ini, Flutter bisa tahu rule final yang diputuskan server, bukan hanya rule yang diminta client.

## Validation Strategy

Validasi tidak boleh hanya di client.

Lapisan yang disarankan:

1. client validation untuk UX
2. prepare validation di Laravel
3. finalize validation di Laravel

### 1. Client validation

Dipakai untuk pengalaman pengguna, misalnya:

- tolak file lebih dari `2 MB` sebelum upload avatar
- tampilkan pesan kalau file bukan image
- hindari upload sia-sia

Tapi ini bukan security boundary.

### 2. Prepare validation

Di tahap `prepare`, backend memvalidasi metadata request:

- `purpose` valid atau tidak
- `content_type` termasuk allowed mime atau tidak
- `size` melebihi max atau tidak
- `requested_visibility` diizinkan atau tidak
- user boleh upload untuk purpose itu atau tidak

Kalau gagal di sini, signed upload tidak dibuat.

### 3. Finalize validation

Setelah file benar-benar ada di storage, backend harus validasi ulang:

- object benar-benar ada
- ukuran aktual object sesuai
- mime aktual masuk whitelist
- file benar-benar image jika purpose image
- file benar-benar video jika purpose video
- visibility object diset sesuai keputusan final server

Kalau finalize gagal, record Curator tidak dibuat.

## Contoh Rule per Jenis File

### Avatar

- purpose: `user_avatar`
- allowed mime: image only
- max size: `2 MB`
- visibility final: `public`
- final path: `avatars/...`

### Attachment

- purpose: `post_attachment`
- allowed mime: misalnya PDF dulu
- max size: `10 MB`
- visibility final: bisa `private`, opsional `public`
- final path: `posts/attachments/...`

### Video

- purpose: `post_video`
- allowed mime: `video/mp4`, `video/quicktime`
- max size: lebih besar
- visibility final: biasanya `private`
- final path: `posts/videos/...`

Jadi desain ini memang bisa menangani banyak tipe file, bukan hanya avatar.

## Contoh Kontrak Request Domain

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
  - set visibility Curator dari keputusan final server
  - kembalikan record Curator final
- jika input `curator_id`
  - pastikan record Curator valid
  - pastikan user berhak memakainya
  - kembalikan record Curator tersebut

Pola ini membuat code domain lebih rapi karena setiap action tidak perlu tahu detail upload lifecycle dari nol.

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
- avatar, thumbnail, attachment, dan media lain punya final source of truth yang sama

Jadi perbedaan antara Filament dan mobile hanya ada di cara upload atau UI picker, bukan di backend media library.

## Rekomendasi Production: Direct to S3

Untuk lingkungan production, sangat disarankan mengonfigurasi `FILESYSTEM_DISK=s3` di file `.env`.

Karena backend menggunakan method bawaan AWS S3 Adapter untuk meng-generate `temporaryUploadUrl()`, maka jika disk diatur ke `s3`, endpoint `prepare` akan otomatis mengembalikan URL upload langsung (presigned URL) ke bucket S3.

Keuntungan utamanya:
- **Menghemat bandwidth server**: File binary berukuran besar (seperti video atau dokumen) tidak masuk ke server aplikasi (PHP/Laravel) sama sekali. Client langsung mengunggah file tersebut secara aman ke storage S3.
- **Performa lebih baik**: Proses upload lebih cepat karena langsung memanfaatkan infrastruktur AWS S3 yang dirancang untuk menerima request file statis berskala besar.
- **Aman**: S3 temporary url otomatis kedaluwarsa sesuai waktu expires_at.

Sementara untuk local development, file tetap di-upload ke endpoint fallback di backend (sebagai PUT request biasa pada URL `/api/v1/uploads/{upload}/file`) karena `local` disk tidak bisa mengembalikan presigned URL.

## Kesimpulan

Arah implementasi mobile yang disarankan untuk starter kit ini adalah:

- temporary upload lewat `uploads`
- `prepare upload` wajib berbasis `purpose`
- validasi utama tetap di server
- client boleh mengirim `requested_visibility`, tetapi server yang memutuskan `final_visibility`
- final media library tetap `curator`
- endpoint domain menerima `*_upload_id` atau `*_curator_id`
- backend otomatis resolve hasil akhir ke record Curator

Pola ini paling fleksibel untuk sekarang dan tetap aman untuk nanti saat mobile sudah memiliki media picker sendiri.
