# 🛠 Rector Guide for Filament Starter Kit

Project ini menggunakan [Rector](https://github.com/rectorphp/rector) untuk **automated refactoring** dan **code quality/type checking**.  
Selain Rector official, kita menggunakan package `driftingly/rector-laravel` untuk rule khusus Laravel.

---

## 📦 Installation

Untuk menambahkan Rector di project:

```bash
composer require --dev rector/rector
composer require --dev driftingly/rector-laravel
```

File konfigurasi utama Rector ada di `rector.php` di root project.

---

## ⚙️ Konfigurasi Rector

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

-   **withPaths** → path folder/file yang akan di-scan oleh Rector.
-   **withSkip** → list rules atau rector yang ingin dilewatkan (tidak dijalankan).
-   **withPreparedSets** → preset Rector resmi untuk refactor otomatis:

    -   `deadCode` → hapus kode tidak terpakai
    -   `codeQuality` → optimasi kualitas kode
    -   `typeDeclarations` → menambahkan type hints
    -   `privatization` → ubah property/method yang bisa private
    -   `earlyReturn` → refactor untuk early return
    -   `strictBooleans` → paksa boolean strict comparisons

-   **withSets** → preset khusus Laravel dari driftingly/rector-laravel (`UP_TO_LARAVEL_120`)
-   **withPhpSets** → pakai rule terbaru sesuai versi PHP yang digunakan

---

## 🚀 Menjalankan Rector

-   **Dry-run (cek tanpa perubahan)**:

```bash
composer test:refactor
# atau
vendor/bin/rector process --dry-run
```

-   **Apply fixes**:

```bash
composer refactor
# atau
vendor/bin/rector process
```

> ⚠ Pastikan commit semua perubahan sebelum menjalankan `rector process`, karena bisa melakukan banyak refactor sekaligus.

---

## ❌ Menghapus `driftingly/rector-laravel`

Jika ingin remove package Laravel khusus:

```bash
composer remove driftingly/rector-laravel
```

### Efeknya:

-   Preset `LaravelLevelSetList::UP_TO_LARAVEL_120` tidak bisa dipakai lagi.
-   Rector tetap berjalan untuk rule default (`deadCode`, `codeQuality`, dll).
-   Laravel-specific refactor dan patch (misal: route optimizations, Model type hints) tidak berlaku lagi.
-   Jika ada konfigurasi `withSets([LaravelLevelSetList::...])`, Rector akan error → harus hapus atau ganti dengan set lain.

---

## 💡 Tips

1. Jalankan **dry-run** dulu sebelum apply, supaya tahu apa yang akan berubah.
2. Gunakan commit terpisah untuk perubahan Rector, supaya mudah rollback.
3. Selalu perbarui package Rector dan Laravel preset jika upgrade Laravel.
4. Contributor baru bisa menambahkan rule kustom di `rector.php` sesuai kebutuhan, lalu commit perubahan terpisah.

---

## 📌 Referensi

-   [Rector Official Docs](https://github.com/rectorphp/rector)
-   [driftingly/rector-laravel](https://github.com/driftingly/rector-laravel)
