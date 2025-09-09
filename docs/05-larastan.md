# Larastan - Typescript for PHP

Project ini menggunakan [Larastan](https://github.com/nunomaduro/larastan) untuk **static analysis** dan **type checking** di Laravel. Larastan adalah extension dari PHPStan khusus Laravel.

---

## 📦 Installation

```bash
composer require --dev nunomaduro/larastan
```

---

## ⚙️ Configuration

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

### Penjelasan Config

-   **includes** → load extension Larastan dan Carbon untuk type inference.
-   **paths** → folder/folder mana yang akan dianalisis (`app/` saja).
-   **level** → level strictness dari 0 (paling longgar) sampai max (paling ketat).

> ⚠ Level `max` bisa menimbulkan banyak warning, tapi membantu menjaga kualitas kode.  
> Saya sarankan minimum gunakan Level `6` untuk Filament Project.

---

## 🚀 Menjalankan Larastan

```bash
composer test:types
# atau
vendor/bin/phpstan analyse
```

---

## 💡 Tips

1. Jalankan Larastan secara rutin sebelum merge ke main branch.
2. Commit hasil fix Larastan secara terpisah supaya mudah rollback.
3. Contributor baru dapat menambahkan path tambahan atau rules khusus di `phpstan.neon`.

---

## 📌 Referensi

-   [Larastan Official](https://github.com/nunomaduro/larastan)
-   [PHPStan Official](https://phpstan.org/)
