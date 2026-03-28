# Issue: Implementasi API Upload File Mobile dengan `uploads + curator`

## Summary

Bangun API upload file untuk mobile app yang mendukung:

- direct upload ke S3-compatible temporary path via signed upload
- finalisasi file ke final storage
- pembuatan record final di tabel `curator`
- attach file ke domain model melalui `upload_id` atau `curator_id`

Tujuannya supaya mobile app tidak perlu melakukan flow berbelit seperti:

`upload -> finalize ke curator -> ambil curator_id -> submit domain`

Sebaliknya, mobile cukup:

- upload file dan pegang `upload_id`, lalu
- submit endpoint domain dengan `*_upload_id`

Backend akan otomatis memfinalisasi ke Curator.

## Goal

Menyediakan fondasi upload file mobile yang scalable, maintainable, dan cocok untuk future media picker di mobile.

## Scope

- buat tabel `uploads` untuk temporary upload lifecycle
- buat endpoint prepare upload
- buat signed upload instruction untuk object storage
- simpan file baru ke temporary path
- buat resolver backend yang menerima:
  - `*_upload_id`
  - `*_curator_id`
- jika menerima `*_upload_id`, backend otomatis:
  - validasi upload
  - move/copy dari temp ke final path
  - buat record final di tabel `curator`
  - attach ke domain model
- siapkan pola untuk:
  - avatar user
  - thumbnail post
  - attachment lain di masa depan

## Non-Goals

- belum membangun UI Flutter media picker
- belum membangun upload resumable/chunked video besar
- belum membangun background processing yang kompleks untuk media transformation

## Proposed API Direction

### Upload lifecycle

- `POST /api/v1/uploads/prepare`
- `POST /api/v1/uploads/{upload}/mark-uploaded` jika diperlukan

### Domain usage

Contoh pola request:

- `avatar_upload_id` atau `avatar_curator_id`
- `thumbnail_upload_id` atau `thumbnail_curator_id`
- `attachment_upload_ids` atau `attachment_curator_ids`

### Example UX

- mobile upload file baru:
  - prepare
  - upload ke temp
  - submit form domain dengan `*_upload_id`
- mobile pick media existing:
  - browse `curator`
  - submit form domain dengan `*_curator_id`

## Suggested Technical Direction

- gunakan tabel `uploads` sebagai source of truth temporary upload
- gunakan tabel `curator` sebagai final media source of truth
- domain endpoint harus fleksibel menerima upload baru atau pick existing
- buat resolver tunggal, misalnya:
  - input: `upload_id | curator_id`
  - output: final record `curator`
- validasi harus berbasis `purpose`, misalnya:
  - `user_avatar`
  - `post_thumbnail`
  - `post_attachment`

## Suggested Tasks

1. Buat migration dan model `uploads`.
2. Definisikan lifecycle status upload.
3. Buat endpoint `POST /api/v1/uploads/prepare`.
4. Implementasikan signed upload untuk disk/object storage yang dipilih.
5. Buat resolver upload-to-curator.
6. Integrasikan ke endpoint domain untuk avatar user.
7. Integrasikan ke endpoint domain untuk thumbnail post.
8. Tambahkan cleanup strategy untuk upload temporary yang expired.
9. Tambahkan dokumentasi kontrak API untuk Flutter/mobile.

## Acceptance Criteria

- API dapat menyiapkan signed upload untuk file mobile.
- Mobile dapat upload file ke temporary storage.
- Endpoint domain dapat menerima `*_upload_id` dan otomatis membuat record di `curator`.
- Endpoint domain dapat menerima `*_curator_id` untuk media existing.
- Avatar user mendukung dua mode:
  - upload baru
  - pick dari Curator
- Thumbnail post mendukung dua mode:
  - upload baru
  - pick dari Curator
- Temporary upload yang tidak jadi dipakai dapat dibersihkan.
- Dokumentasi API cukup jelas untuk tim Flutter.

## Risks

- validasi MIME dan size tidak boleh hanya percaya metadata client
- perlu mutual-exclusive validation antara `*_upload_id` dan `*_curator_id`
- final URL/access policy perlu jelas untuk public/private media
- operasi "move" di S3-compatible biasanya adalah copy + delete

## Notes

Desain ini sengaja dibuat supaya mobile app:

- sederhana saat upload file baru
- tetap future-proof saat nanti punya media picker
- tidak perlu tahu detail internal pembuatan record Curator
