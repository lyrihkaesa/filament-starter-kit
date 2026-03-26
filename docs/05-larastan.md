# Larastan - Typescript for PHP

Project ini menggunakan [Larastan](https://github.com/nunomaduro/larastan) untuk **static analysis** dan **type checking** di Laravel. Larastan adalah extension dari PHPStan yang memahami ekosistem Laravel dengan jauh lebih baik.

Saya sengaja mempertahankan istilah bercanda "Typescript for PHP" karena untuk banyak developer pemula, itulah cara paling mudah membayangkannya: tool ini membantu menangkap banyak kesalahan lebih awal, bahkan sebelum kode dijalankan.

## Kenapa Larastan Penting

Beberapa bug tidak selalu muncul saat klik manual atau test biasa, misalnya:

- memanggil method yang salah nama
- mengira sebuah variable pasti `User`, padahal bisa `null`
- salah return type
- mengakses relasi atau property yang tidak valid

Larastan membantu mendeteksi kelas masalah seperti ini lebih cepat.

## Installation

```bash
composer require --dev nunomaduro/larastan
```

## Configuration

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

- `includes` untuk memuat extension Larastan dan Carbon agar type inference lebih akurat
- `paths` menentukan folder mana yang dianalisis
- `level` menentukan seberapa ketat analisis dilakukan

Level berjalan dari longgar sampai `max`.

> Level `max` memang bisa memunculkan banyak warning, tetapi saya sengaja menyukainya untuk menjaga kualitas dasar project. Kalau Anda baru memulai, Anda bisa menurunkannya dulu lalu meningkatkannya bertahap.

## Cara Pikir Saya Soal Type Safety

Saya tidak melihat static analysis sebagai "tambahan opsional untuk project enterprise". Justru di project kecil sampai menengah, tool seperti ini sangat membantu karena:

- tim sering bergerak cepat
- refactor sering terjadi
- satu kesalahan type kecil bisa merembet ke banyak file

Kalau Anda memakai Action Pattern, Larastan akan makin terasa manfaatnya karena contract antar class menjadi lebih jelas.

## Studi Kasus

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

Lalu di tempat lain Anda memanggilnya dengan hasil query yang bisa `null`.

Tanpa static analysis, bug ini mungkin baru terlihat ketika flow tertentu dijalankan. Dengan Larastan, Anda bisa diperingatkan lebih awal bahwa nilai yang dikirim belum tentu `User`.

## Menjalankan Larastan

```bash
composer test:types
```

Atau:

```bash
vendor/bin/phpstan analyse
```

## Tips

1. Jalankan Larastan secara rutin sebelum merge ke branch utama.
2. Jika hasil error banyak, kerjakan bertahap dan jangan panik.
3. Untuk developer baru, fokus dulu memahami error yang paling sering muncul:
   - kemungkinan `null`
   - type return yang tidak konsisten
   - pemanggilan method yang tidak valid
4. Jika perlu, tambahkan PHPDoc yang benar-benar berguna, bukan sekadar ramai komentar.

## Referensi

- [Larastan Official](https://github.com/nunomaduro/larastan)
- [PHPStan Official](https://phpstan.org/)
