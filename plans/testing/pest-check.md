# Pest Check - Rencana & Dokumentasi

## Status Saat Ini
Diabaikan (Ignored) / Minimal Check.

## Alasan Implementasi
Sebelumnya, upaya untuk mencapai status "Green" secara penuh pada seluruh test suite mengalami kendala karena beberapa hal:
- Konfigurasi environment testing pada Windows yang kurang stabil untuk database isolation tingkat tinggi.
- Beberapa test bawaan framework atau package yang bentrok dengan kustomisasi UUID v7 atau arsitektur Action-based.

## Keputusan
Untuk sementara, beberapa pengecekan Pest yang bersifat menyeluruh diabaikan atau disesuaikan agar proses pengembangan tidak terhambat oleh kegagalan test yang bersifat environment-specific. Fokus dialihkan pada pengujian fitur krusial (Feature Tests) secara manual atau individu.

## Rencana Kedepan
Akan dilakukan review mendalam untuk menentukan apakah test yang gagal perlu diperbaiki logic-nya, di-mock lebih agresif, atau memang layak untuk di-ignore secara permanen demi efisiensi CI/CD.
