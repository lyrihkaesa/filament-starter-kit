# Pint - Code Style or Linter

Project ini menggunakan [Laravel Pint](https://laravel.com/docs/12.x/pint) dengan preset `laravel` dan beberapa aturan tambahan atau kustom.

Tujuannya bukan sekadar membuat kode terlihat rapi. Pint dipakai supaya tim tidak menghabiskan energi untuk memperdebatkan format penulisan yang sebenarnya bisa diserahkan ke tool.

## Kenapa Saya Pakai Pint

Saat project mulai berkembang, inkonsistensi kecil akan menumpuk:

- ada yang pakai `==`, ada yang pakai `===`
- ada yang tidak menulis visibility
- ada yang masih pakai `DateTime`
- ada yang menulis beberapa statement dalam satu baris

Masalahnya bukan hanya estetika. Inkonsistensi seperti ini membuat review lebih lambat dan menambah beban mental saat membaca kode.

Dengan Pint, sebagian besar keputusan gaya penulisan dipindahkan ke tool.

## Visual Studio Code

Jika Anda menggunakan Visual Studio Code, saya sarankan gunakan ekstensi [Laravel Pint](https://marketplace.visualstudio.com/items?itemName=open-southeners.laravel-pint).

Biasanya developer PHP pemula sudah terbiasa menggunakan Intelephense untuk banyak hal, tetapi untuk formatting saya lebih merekomendasikan Pint agar hasilnya konsisten dengan aturan repository.

Contoh konfigurasi `settings.json`:

```json
"editor.formatOnSave": true,
"[php]": {
    "editor.defaultFormatter": "open-southeners.laravel-pint"
}
```

Dengan konfigurasi ini, setiap kali file PHP disimpan, formatnya akan langsung disesuaikan.

## File yang Dikecualikan

Rule Pint **tidak dijalankan** pada beberapa file tertentu, misalnya:

- `tests/TestCase.php`
- `intelephense_helper.php`
- `_ide_helper.php`

Lihat file `pint.json` untuk daftar final dan aturan yang benar-benar aktif di project ini.

## Aturan yang Digunakan

Berikut beberapa aturan penting yang aktif di `pint.json`.

### `array_push`

Gunakan `[]` ketimbang `array_push`.

```php
// Before
array_push($arr, 1);

// After
$arr[] = 1;
```

### `backtick_to_shell_exec`

Hindari backtick, gunakan `shell_exec()`.

```php
// Before
$a = `ls -la`;

// After
$a = shell_exec('ls -la');
```

### `date_time_immutable`

Wajib gunakan `DateTimeImmutable` alih-alih `DateTime`.

```php
// Before
$d = new DateTime();

// After
$d = new DateTimeImmutable();
```

### `declare_strict_types`

Tambahkan `declare(strict_types=1);` di setiap file PHP.

```php
<?php
// Before: tanpa declare

// After
declare(strict_types=1);
```

### `lowercase_keywords`

Kata kunci PHP selalu huruf kecil.

```php
// Before
IF ($a) {}

// After
if ($a) {}
```

### `lowercase_static_reference`

Gunakan lowercase untuk `self`, `static`, dan `parent`.

```php
// Before
SELF::class;

// After
self::class;
```

### `final_class`

Semua class ditandai `final` kecuali memang perlu di-extend.

```php
// Before
class User {}

// After
final class User {}
```

### `no_superfluous_elseif`

Hindari `elseif` yang sebenarnya bisa dipisah menjadi `if`.

```php
// Before
if ($a) {
} elseif ($b) {
}

// After
if ($a) {
}
if ($b) {
}
```

### `no_useless_else`

Hindari `else` setelah `return`.

```php
// Before
if ($a) return 1;
else return 2;

// After
if ($a) return 1;
return 2;
```

### `no_multiple_statements_per_line`

Satu statement per baris.

```php
// Before
$a = 1; $b = 2;

// After
$a = 1;
$b = 2;
```

### `ordered_class_elements`

Elemen class harus sesuai urutan yang rapi, misalnya `use`, `const`, property, constructor, lalu method.

```php
// Before
public function foo() {}
public const BAR = 'bar';

// After
public const BAR = 'bar';
public function foo() {}
```

### `protected_to_private`

Property atau method `protected` akan diubah menjadi `private` jika memang tidak perlu diwariskan.

```php
// Before
protected $value;

// After
private $value;
```

### `strict_comparison`

Gunakan `===` atau `!==`, bukan `==` atau `!=`.

```php
// Before
if ($a == $b) {}

// After
if ($a === $b) {}
```

### `visibility_required`

Semua method dan property wajib memiliki visibility.

```php
// Before
function foo() {}

// After
public function foo() {}
```

## Rule yang Dimatikan

### `new_with_parentheses`

Rule ini **dimatikan** (`false`) sehingga `new Class` tanpa `()` masih diperbolehkan.

```php
// Allowed
$user = new User;
```

## Studi Kasus

Misalnya ada dua developer yang sama-sama membuat action:

- Developer A menulis pendek, rapat, dan tidak strict
- Developer B menulis sangat verbose dan urutannya berbeda

Kalau tidak ada formatter, review akan dipenuhi komentar seperti:

- "tolong pakai strict comparison"
- "visibility-nya mana?"
- "urutkan property dulu"

Dengan Pint, sebagian besar diskusi itu hilang. Review bisa fokus ke hal yang lebih penting seperti:

- apakah authorization sudah benar
- apakah logic bisnis sudah tepat
- apakah test sudah cukup

## Menjalankan Pint

Linting otomatis bisa dijalankan dengan:

```bash
composer lint
```

Atau untuk mengecek tanpa auto-fix:

```bash
composer lint -- --test
```

## Catatan

- Jika ada rule yang dianggap terlalu ketat, Anda bisa menyesuaikannya di `pint.json`
- Sebaiknya commit perubahan linting besar secara terpisah agar diff logic dan diff formatting tidak bercampur
- Pint bukan pengganti review, tetapi sangat membantu menjaga standar dasar project
