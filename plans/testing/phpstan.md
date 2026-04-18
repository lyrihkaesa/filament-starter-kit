# PHPStan - Rencana & Dokumentasi

## Status Saat Ini
Level 0 - Stabil (dengan beberapa pengecualian).

## Alasan Implementasi
Mencapai Level tinggi pada PHPStan sangat sulit dilakukan pada project yang menggunakan Filament v5 dan banyak menggunakan data bertipe `mixed` (seperti properti pada Spatie Activity Log). Upaya paksa untuk mencapai level tinggi justru menyebabkan kode menjadi penuh dengan `assert()` atau `@var` yang tidak perlu dan mengotori kebersihan kode.

## Keputusan
- Menggunakan Level 0 sebagai baseline wajib lulus.
- Mengabaikan error yang berkaitan dengan internal library yang tidak bisa diubah tanpa risiko merusak fungsionalitas.
- Fokus pada keamanan tipe (type safety) di level Action dan Model.

## Rencana Kedepan
Meninjau kembali aturan PHPStan secara berkala. Jika ada pola baru di Laravel 12 yang memudahkan static analysis tanpa mengotori kode, maka level akan ditingkatkan secara bertahap.
