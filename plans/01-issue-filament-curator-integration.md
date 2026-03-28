# Issue: Implementasi Filament Curator untuk Media Manager & Picker

## Summary

Tambahkan plugin **Filament Curator** sebagai media manager dan media picker resmi di panel Filament.

Arsitektur final yang dipakai:

- tabel `uploads` untuk temporary upload lifecycle
- tabel `curator` untuk final media library
- Filament memakai Curator sebagai media manager dan picker
- mobile app nantinya juga membaca final media dari tabel/resource `curator`

## Goal

Menyediakan pengalaman media manager yang rapi di Filament, dengan satu source of truth final media di Curator.

## Scope

- install dan konfigurasi plugin `awcodes/filament-curator`
- publish migration/config yang diperlukan
- pastikan final media memakai tabel `curator`
- siapkan struktur disk, directory, dan visibility yang sesuai kebutuhan project
- integrasikan picker Curator ke field yang relevan, minimal:
  - avatar user
  - thumbnail post
- pastikan admin bisa:
  - upload media
  - browse media
  - memilih media existing
  - attach media ke resource

## Non-Goals

- belum membangun signed upload API mobile
- belum membangun temporary upload resolver dari `upload_id`
- belum membangun media picker Flutter

## Proposed Technical Direction

- gunakan Curator sebagai final media manager di Filament
- jangan tambahkan tabel `media` baru
- final asset untuk picker dan manager menggunakan tabel `curator`
- field domain dapat memakai relasi atau FK yang mengarah ke record Curator
- validasi penggunaan media harus tetap domain-aware, misalnya:
  - avatar hanya image
  - thumbnail post hanya image

## Suggested Tasks

1. Install package dan publish migration/config Curator.
2. Review struktur model dan tabel Curator yang dipakai package.
3. Tentukan disk default, visibility default, dan folder strategy.
4. Integrasikan picker Curator ke resource/profile yang membutuhkan media.
5. Pastikan URL media final bekerja untuk local/private/public/S3-compatible sesuai kebutuhan project.
6. Tambahkan dokumentasi penggunaan Curator di starter kit.

## Acceptance Criteria

- Filament Curator terpasang dan dapat diakses dari panel admin.
- Admin dapat upload dan browse media dari panel.
- Avatar user dapat memilih media existing dari Curator.
- Thumbnail post dapat memilih media existing dari Curator.
- Final media tersimpan di tabel `curator`, bukan tabel lain.
- Dokumentasi project menjelaskan posisi Curator dalam arsitektur upload file.

## Risks

- perlu memastikan final URL/preview aman untuk file private
- perlu memastikan naming field dan relasi domain tetap jelas
- perlu menjaga supaya integrasi Curator tidak mengikat arsitektur mobile ke UI Filament

## Notes

Curator di issue ini diposisikan sebagai:

- final media library
- media manager di Filament
- picker source untuk admin

Bukan sebagai pengganti flow temporary signed upload untuk mobile API.
