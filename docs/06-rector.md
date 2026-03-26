# Rector - Automated Refactoring

Project ini menggunakan [Rector](https://github.com/rectorphp/rector) untuk **automated refactoring** dan **code quality/type checking**.

Selain Rector official, starter kit ini juga menggunakan package `driftingly/rector-laravel` untuk rule khusus Laravel.

Kalau Pint adalah tool untuk merapikan gaya penulisan, maka Rector adalah tool untuk membantu merapikan struktur kode dan modernisasi sintaks.

## Kenapa Saya Pakai Rector

Saat project berkembang, refactor kecil tapi banyak akan sering muncul:

- mengubah pola `if/else` menjadi early return
- menambahkan type declaration
- merapikan code smell sederhana
- mengikuti perubahan praktik terbaik versi PHP atau Laravel terbaru

Kalau semua dikerjakan manual, pekerjaan ini memakan waktu dan rawan terlewat.

## Installation

Untuk menambahkan Rector di project:

```bash
composer require --dev rector/rector
composer require --dev driftingly/rector-laravel
```

File konfigurasi utama Rector ada di `rector.php` di root project.

## Konfigurasi Rector

Contoh konfigurasi yang dipakai:

```php
<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use RectorLaravel\Set\LaravelLevelSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/bootstrap/app.php',
        __DIR__.'/database',
        __DIR__.'/public',
    ])
    ->withSkip([
        AddOverrideAttributeToOverriddenMethodsRector::class,
    ])
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        privatization: true,
        earlyReturn: true,
        strictBooleans: true,
    )
    ->withSets([
        LaravelLevelSetList::UP_TO_LARAVEL_120,
    ])
    ->withPhpSets();
```

### Penjelasan Config

- `withPaths` menentukan folder atau file yang akan diproses Rector
- `withSkip` menentukan rule yang sengaja tidak dijalankan
- `withPreparedSets` mengaktifkan kumpulan rule resmi Rector
- `withSets` mengaktifkan preset khusus Laravel
- `withPhpSets` menyesuaikan rule dengan versi PHP yang dipakai

### Penjelasan `withPreparedSets`

- `deadCode` untuk menghapus kode yang tidak terpakai
- `codeQuality` untuk meningkatkan kualitas struktur kode
- `typeDeclarations` untuk menambahkan type hints
- `privatization` untuk mengubah property atau method yang seharusnya private
- `earlyReturn` untuk merapikan flow kondisi
- `strictBooleans` untuk mendorong perbandingan boolean yang lebih aman

## Studi Kasus

Misalnya Anda punya banyak action lama yang ditulis sebelum project lebih disiplin terhadap type declaration. Rector bisa membantu:

- menambahkan beberapa type hints otomatis
- merapikan branch yang terlalu dalam
- menemukan pola code smell sederhana

Tetapi saya tetap menekankan: **Rector adalah alat bantu, bukan pengganti pemahaman kode**.

## Menjalankan Rector

### Dry-run

```bash
composer test:refactor
```

atau:

```bash
vendor/bin/rector process --dry-run
```

### Apply fixes

```bash
composer refactor
```

atau:

```bash
vendor/bin/rector process
```

> Pastikan commit semua perubahan penting sebelum menjalankan `rector process`, karena hasil refactor bisa cukup banyak.

## Menghapus `driftingly/rector-laravel`

Jika ingin remove package Laravel khusus:

```bash
composer remove driftingly/rector-laravel
```

### Efeknya

- preset `LaravelLevelSetList::UP_TO_LARAVEL_120` tidak bisa dipakai lagi
- Rector tetap bisa berjalan untuk rule default
- Laravel-specific refactor tidak akan aktif
- jika konfigurasi `withSets([LaravelLevelSetList::...])` tetap dibiarkan, Rector akan error

## Tips

1. Jalankan **dry-run** dulu sebelum apply.
2. Gunakan commit terpisah untuk hasil Rector.
3. Review diff dengan hati-hati, terutama di file bisnis penting.
4. Jangan pakai Rector sebagai alasan untuk menghindari review manual.

## Kesimpulan

Rector sangat membantu menjaga project tetap modern, tetapi tetap harus dipakai dengan sadar. Saya menggunakannya sebagai "asisten refactor", bukan sebagai pihak yang saya percaya 100% tanpa verifikasi.
