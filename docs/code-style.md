# 🎨 Code Style Rules (Laravel Pint)

Project ini menggunakan [Laravel Pint](https://laravel.com/docs/pint) dengan preset `laravel` dan beberapa aturan tambahan/kustom.  
Tujuannya untuk menjaga konsistensi dan kualitas kode di seluruh project.

---

## Visual Studio Code

Jika anda menggunakan visual studio code gunakanlah ekstensi [Laravel Pint](https://marketplace.visualstudio.com/items?itemName=open-southeners.laravel-pint) untuk formating dengan Pint, Biasanya anda melakukan formating php menggunakan Ekstensi [PHP Intelephense](https://marketplace.visualstudio.com/.items?itemName=bmewburn.vscode-intelephense-client).

Saya merekomendasikan Laravel Pint untuk best partice linter.
Buka file `.php` > click kanan mouse pada editor > `Format Document With` > `Laravel Pint`. Atau bisa diconfigurasi secara default lewat `settings.json` vscode:

```json
"editor.formatOnSave": true,
"[php]": {
    "editor.defaultFormatter": "open-southeners.laravel-pint"
},
```

---

## 📂 File yang Dikecualikan

Rule Pint **tidak dijalankan** pada file berikut:

-   `tests/TestCase.php`
-   `intelephense_helper.php`
-   `_ide_helper.php`

---

## ⚙️ Aturan yang Digunakan

Berikut adalah aturan yang aktif di `pint.json`:

### `array_push`

Gunakan `[]` ketimbang `array_push`.

```php
// Before
array_push($arr, 1);

// After
$arr[] = 1;
```

---

### `backtick_to_shell_exec`

Hindari backtick, gunakan `shell_exec()`.

```php
// Before
$a = `ls -la`;

// After
$a = shell_exec('ls -la');
```

---

### `date_time_immutable`

Wajib gunakan `DateTimeImmutable` alih-alih `DateTime`.

```php
// Before
$d = new DateTime();

// After
$d = new DateTimeImmutable();
```

---

### `declare_strict_types`

Tambahkan `declare(strict_types=1);` di setiap file PHP.

```php
<?php
// Before: tanpa declare

// After
declare(strict_types=1);
```

---

### `lowercase_keywords`

Kata kunci PHP selalu huruf kecil.

```php
// Before
IF ($a) {}

// After
if ($a) {}
```

---

### `lowercase_static_reference`

Gunakan lowercase untuk `self`, `static`, `parent`.

```php
// Before
SELF::class;

// After
self::class;
```

---

### `final_class`

Semua class ditandai `final` kecuali memang perlu di-extend.

```php
// Before
class User {}

// After
final class User {}
```

---

### `no_superfluous_elseif`

Hindari `elseif` yang bisa jadi `if`.

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

---

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

---

### `no_multiple_statements_per_line`

Satu statement per baris.

```php
// Before
$a = 1; $b = 2;

// After
$a = 1;
$b = 2;
```

---

### `ordered_class_elements`

Elemen class harus sesuai urutan (use, const, property, construct, method, dll).

```php
// Before
public function foo() {}
public const BAR = 'bar';

// After
public const BAR = 'bar';
public function foo() {}
```

---

### `protected_to_private`

Property/method `protected` jadi `private` jika tidak diwariskan.

```php
// Before
protected $value;

// After
private $value;
```

---

### `strict_comparison`

Gunakan `===` atau `!==` bukan `==` atau `!=`.

```php
// Before
if ($a == $b) {}

// After
if ($a === $b) {}
```

---

### `visibility_required`

Semua method/property wajib ada visibility.

```php
// Before
function foo() {}

// After
public function foo() {}
```

---

## ❌ Rule Dimatikan

### `new_with_parentheses`

Rule ini **dimatikan** (`false`) → membolehkan `new Class` tanpa `()`.

```php
// Allowed
$user = new User;
```

---

## 🚀 Menjalankan Pint

Linting otomatis bisa dijalankan dengan:

```bash
composer lint
```

Atau untuk mengecek tanpa auto-fix:

```bash
composer lint -- --test
```

---

## 📌 Catatan

-   Jika ada rule yang dianggap terlalu ketat, bisa **dimatikan di `pint.json`**.
-   Selalu commit perubahan linting secara terpisah dengan prefix:

    ```bash
    chore(lint): apply Pint code style fixes
    ```
