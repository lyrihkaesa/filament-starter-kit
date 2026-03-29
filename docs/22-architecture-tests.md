# Architecture Testing Standard

Dokumen ini menjelaskan standarisasi pengujian arsitektur aplikasi menggunakan **Pest Arch**. Fokus utamanya adalah menjaga agar pola desain yang telah ditetapkan (terutama **Action Pattern**) tetap terjaga dan tidak terjadi pelanggaran struktur yang membuat aplikasi sulit dirawat.

## Tujuan

- Menjamin konsistensi penggunaan **Action Pattern**.
- Memastikan tidak ada business logic yang bocor ke Controller atau Filament Page.
- Menjaga kebersihan kode dengan *strict typing* dan standar PHP modern.
- Mencegah penggunaan fitur-fitur yang dianggap tidak aman atau *deprecated*.

## Konfigurasi Standar

Kami menggunakan preset bawaan Pest untuk mencakup area-area penting:

- `arch()->preset()->php()`: Memastikan penggunaan fitur PHP yang modern dan aman.
- `arch()->preset()->strict()`: Memaksa penggunaan `declare(strict_types=1)` dan *type hints*.
- `arch()->preset()->laravel()`: Mengikuti praktik terbaik Laravel.
- `arch()->preset()->security()`: Menghindari fungsi-fungsi PHP yang berbahaya.

## Aturan Khusus

Salah satu aturan paling penting dalam starter kit ini adalah **larangan penggunaan Controller**. Karena kita menggunakan Filament untuk admin dan Action untuk logic, Controller seringkali hanya menjadi *boilerplate* yang tidak perlu atau tempat persembunyian logic yang salah.

```php
arch('controllers')
    ->expect('App\Http\Controllers')
    ->not->toBeUsed();
```

## Implementasi Kode Standar

Berikut adalah blok kode standar yang harus ada dalam `tests/Feature/ArchitectureTest.php`:

```php
<?php

declare(strict_types=1);

arch()->preset()->php();
arch()->preset()->strict();
arch()->preset()->laravel();
arch()->preset()->security()->ignoring([
    'assert',
]);

arch('controllers')
    ->expect('App\Http\Controllers')
    ->not->toBeUsed();
```

---

*Catatan: Isu ini dibuat untuk menstandarisasi pengujian arsitektur di seluruh modul aplikasi.*
