# Pest Coverage - Rencana & Dokumentasi

## Status Saat Ini
Tidak Wajib 100%.

## Alasan Implementasi
Upaya mencapai 100% test coverage seringkali memicu pembuatan test yang "palsu" atau hanya sekadar menjalankan baris kode tanpa benar-benar menguji logika bisnis (assertion yang lemah). Selain itu, boilerplate Filament yang bersifat deklaratif tidak selalu perlu diuji secara redundan jika framework sudah mengujinya.

## Keputusan
- Prioritas coverage ada pada **Action**, **Policy**, dan **API Endpoints**.
- Komponen UI yang bersifat deklaratif (seperti konfigurasi Table/Form di Filament) tidak diwajibkan memiliki coverage penuh.
- Mengabaikan (ignore) folder atau file yang bersifat boilerplate dalam laporan coverage.

## Rencana Kedepan
Meningkatkan kualitas pengujian daripada kuantitas coverage. Fokus pada skenario edge-case daripada sekadar mengejar persentase baris kode.
