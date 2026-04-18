# Pest Type Coverage - Rencana & Dokumentasi

## Status Saat Ini
Upaya Maksimal (Best Effort).

## Alasan Implementasi
Meskipun Type Coverage sangat baik untuk dokumentasi dan keamanan kode, terdapat area di mana penggunaan `mixed` tidak terhindarkan karena keterbatasan library pihak ketiga atau sifat data JSON yang dinamis. Pemaksaan type hint di area ini seringkali menyebabkan runtime error jika data yang datang tidak sesuai ekspektasi.

## Keputusan
- Wajib menggunakan strict typing (`declare(strict_types=1)`) di semua file PHP baru.
- Menambahkan type hint pada parameter method dan return type sebisa mungkin.
- Mengizinkan penggunaan `mixed` atau `scalar` di area yang memang berisiko jika dikunci tipenya.

## Rencana Kedepan
Meninjau ulang area yang masih menggunakan `mixed` saat melakukan refactoring. Dengan adanya fitur-fitur baru di PHP 8.4+, diharapkan type coverage dapat meningkat secara alami tanpa merusak fleksibilitas kode.
