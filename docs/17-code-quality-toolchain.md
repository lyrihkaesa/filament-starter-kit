# Code Quality Toolchain: Pint, Larastan & Rector

Project ini menggunakan tiga tool kualitas kode yang saling melengkapi:

- **[Laravel Pint](https://laravel.com/docs/12.x/pint)** — formatter otomatis untuk gaya penulisan PHP
- **[Larastan](https://github.com/nunomaduro/larastan)** — static analysis / type checking
- **[Rector](https://github.com/rectorphp/rector)** — automated refactoring

Ketiga tool ini membentuk satu ekosistem. Cara pikir yang saya gunakan:

1. **Pint** merapikan *format* kode (spasi, urutan, style)
2. **Rector** merapikan *struktur* kode (type hints, early return, dead code)
3. **Larastan** memvalidasi *kebenaran tipe* sebelum runtime

Jalankan dalam urutan: `pint → rector → larastan`

---

## Workflow yang Disarankan

```bash
# 1. Rapikan gaya penulisan dulu
vendor/bin/pint --dirty

# 2. Terapkan refactoring otomatis
vendor/bin/rector process

# 3. Validasi type safety
vendor/bin/phpstan analyse
```

Atau gunakan alias composer yang sudah disiapkan:

```bash
composer lint      # Pint
composer refactor  # Rector
composer test:types  # Larastan
```

---

## Laravel Pint (Code Formatter)

Pint dipakai supaya tim tidak menghabiskan energi memperdebatkan format penulisan yang sebenarnya bisa diserahkan ke tool.

Saat project berkembang, inkonsistensi kecil akan menumpuk:

- ada yang pakai `==`, ada yang pakai `===`
- ada yang tidak menulis visibility
- ada yang masih pakai `DateTime`
- ada yang menulis beberapa statement dalam satu baris

### Visual Studio Code

Jika Anda menggunakan Visual Studio Code, gunakan ekstensi [Laravel Pint](https://marketplace.visualstudio.com/items?itemName=open-southeners.laravel-pint).

Contoh konfigurasi `settings.json`:

```json
"editor.formatOnSave": true,
"[php]": {
    "editor.defaultFormatter": "open-southeners.laravel-pint"
}
```

### File yang Dikecualikan

Rule Pint **tidak dijalankan** pada beberapa file tertentu, misalnya:

- `tests/TestCase.php`
- `intelephense_helper.php`
- `_ide_helper.php`

Lihat file `pint.json` untuk daftar final aturan yang aktif di project ini.

### Aturan Penting yang Aktif

#### `declare_strict_types`

Tambahkan `declare(strict_types=1);` di setiap file PHP:

```php
<?php
// After
declare(strict_types=1);
```

#### `final_class`

Semua class ditandai `final` kecuali memang perlu di-extend:

```php
// Before
class User {}

// After
final class User {}
```

#### `strict_comparison`

Gunakan `===` atau `!==`, bukan `==` atau `!=`:

```php
// Before
if ($a == $b) {}

// After
if ($a === $b) {}
```

#### `date_time_immutable`

Wajib gunakan `DateTimeImmutable` alih-alih `DateTime`:

```php
// Before
$d = new DateTime();

// After
$d = new DateTimeImmutable();
```

#### `no_useless_else`

Hindari `else` setelah `return`:

```php
// Before
if ($a) return 1;
else return 2;

// After
if ($a) return 1;
return 2;
```

#### `visibility_required`

Semua method dan property wajib memiliki visibility:

```php
// Before
function foo() {}

// After
public function foo() {}
```

#### `ordered_class_elements`

Elemen class harus sesuai urutan yang rapi: `use`, `const`, property, constructor, lalu method.

#### `protected_to_private`

Property atau method `protected` akan diubah menjadi `private` jika memang tidak perlu diwariskan.

#### `no_multiple_statements_per_line`

Satu statement per baris:

```php
// Before
$a = 1; $b = 2;

// After
$a = 1;
$b = 2;
```

#### `array_push`

Gunakan `[]` ketimbang `array_push`:

```php
// Before
array_push($arr, 1);

// After
$arr[] = 1;
```

### Rule yang Dimatikan

#### `new_with_parentheses`

Rule ini **dimatikan** (`false`) sehingga `new Class` tanpa `()` masih diperbolehkan:

```php
// Allowed
$user = new User;
```

### Menjalankan Pint

```bash
# Auto-fix
composer lint

# Cek tanpa auto-fix
composer lint -- --test
```

### Studi Kasus

Misalnya ada dua developer yang sama-sama membuat action. Kalau tidak ada formatter, review akan dipenuhi komentar seperti "tolong pakai strict comparison", "visibility-nya mana?". Dengan Pint, diskusi itu hilang dan review bisa fokus ke hal yang lebih penting seperti apakah authorization sudah benar dan apakah logic bisnis sudah tepat.

---

## Larastan (Static Analysis / "TypeScript for PHP")

Project ini menggunakan [Larastan](https://github.com/nunomaduro/larastan) untuk **static analysis** dan **type checking**. Larastan adalah extension dari PHPStan yang memahami ekosistem Laravel dengan jauh lebih baik.

Istilah bercanda "TypeScript for PHP" membantu banyak developer pemula membayangkannya: tool ini membantu menangkap banyak kesalahan lebih awal, bahkan sebelum kode dijalankan.

### Kenapa Larastan Penting

Beberapa bug tidak selalu muncul saat klik manual atau test biasa, misalnya:

- memanggil method yang salah nama
- mengira sebuah variable pasti `User`, padahal bisa `null`
- salah return type
- mengakses relasi atau property yang tidak valid

Larastan membantu mendeteksi kelas masalah seperti ini lebih cepat.

### Konfigurasi

File konfigurasi ada di `phpstan.neon`:

```neon
includes:
    - vendor/larastan/larastan/extension.neon
    - vendor/nesbot/carbon/extension.neon

parameters:
    paths:
        - app/
    level: max
```

**Penjelasan:**

- `includes` memuat extension Larastan dan Carbon agar type inference lebih akurat
- `paths` menentukan folder yang dianalisis
- `level` menentukan seberapa ketat analisis dilakukan (dari longgar sampai `max`)

> Level `max` memang bisa memunculkan banyak warning, tetapi ini disengaja untuk menjaga kualitas dasar project. Kalau Anda baru memulai, turunkan dulu lalu tingkatkan bertahap.

### Studi Kasus

Misalnya Anda punya action seperti ini:

```php
final class UpdateUserAction
{
    public function handle(User $user, array $data): User
    {
        // ...
    }
}
```

Lalu di tempat lain Anda memanggilnya dengan hasil query yang bisa `null`. Tanpa static analysis, bug ini mungkin baru terlihat ketika flow tertentu dijalankan. Dengan Larastan, Anda diperingatkan lebih awal bahwa nilai yang dikirim belum tentu `User`.

### Menjalankan Larastan

```bash
composer test:types

# Atau langsung
vendor/bin/phpstan analyse
```

### Tips

1. Jalankan Larastan secara rutin sebelum merge ke branch utama.
2. Jika hasil error banyak, kerjakan bertahap dan jangan panik.
3. Fokus dulu memahami error yang paling sering muncul:
   - kemungkinan `null`
   - type return yang tidak konsisten
   - pemanggilan method yang tidak valid
4. Tambahkan PHPDoc yang benar-benar berguna, bukan sekadar ramai komentar.

---

## Rector (Automated Refactoring)

Project ini menggunakan [Rector](https://github.com/rectorphp/rector) untuk **automated refactoring** dan **code quality**. Selain Rector official, starter kit ini juga menggunakan package `driftingly/rector-laravel` untuk rule khusus Laravel.

Kalau Pint adalah tool untuk merapikan gaya penulisan, maka Rector adalah tool untuk membantu merapikan **struktur kode** dan modernisasi sintaks.

### Kenapa Saya Pakai Rector

Saat project berkembang, refactor kecil tapi banyak akan sering muncul:

- mengubah pola `if/else` menjadi early return
- menambahkan type declaration
- merapikan code smell sederhana
- mengikuti perubahan praktik terbaik versi PHP atau Laravel terbaru

### Konfigurasi Rector

File konfigurasi ada di `rector.php` di root project:

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

### Penjelasan `withPreparedSets`

- `deadCode` — menghapus kode yang tidak terpakai
- `codeQuality` — meningkatkan kualitas struktur kode
- `typeDeclarations` — menambahkan type hints
- `privatization` — mengubah property atau method yang seharusnya private
- `earlyReturn` — merapikan flow kondisi
- `strictBooleans` — mendorong perbandingan boolean yang lebih aman

### Menjalankan Rector

```bash
# Dry-run dulu (lihat perubahan tanpa apply)
composer test:refactor

# Apply perubahan
composer refactor

# Atau langsung
vendor/bin/rector process --dry-run
vendor/bin/rector process
```

> Pastikan commit semua perubahan penting sebelum menjalankan `rector process`, karena hasil refactor bisa cukup banyak.

### Studi Kasus

Misalnya Anda punya banyak action lama yang ditulis sebelum project lebih disiplin terhadap type declaration. Rector bisa membantu menambahkan type hints otomatis, merapikan branch yang terlalu dalam, dan menemukan pola code smell sederhana.

Tetapi saya tetap menekankan: **Rector adalah alat bantu, bukan pengganti pemahaman kode**.

### Menghapus `driftingly/rector-laravel`

Jika ingin remove package Laravel khusus:

```bash
composer remove driftingly/rector-laravel
```

Efeknya: preset `LaravelLevelSetList::UP_TO_LARAVEL_120` tidak bisa dipakai lagi. Rector tetap bisa berjalan untuk rule default, tetapi Laravel-specific refactor tidak akan aktif. Jika konfigurasi `withSets([LaravelLevelSetList::...])` tetap dibiarkan, Rector akan error.

### Tips

1. Jalankan **dry-run** dulu sebelum apply.
2. Gunakan commit terpisah untuk hasil Rector.
3. Review diff dengan hati-hati, terutama di file bisnis penting.
4. Jangan pakai Rector sebagai alasan untuk menghindari review manual.

---

## Ringkasan

| Tool | Tujuan | Perintah |
|------|--------|---------|
| **Pint** | Format & style penulisan | `composer lint` |
| **Rector** | Refactoring & modernisasi struktur | `composer refactor` |
| **Larastan** | Type checking & static analysis | `composer test:types` |

Ketiga tool ini paling efektif dijalankan bersama secara berurutan sebelum membuat pull request.
