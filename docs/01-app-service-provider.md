# `AppServiceProvider` Essentials Configuration

Project ini menggunakan beberapa konfigurasi dari [NunoMaduro Essentials](https://github.com/nunomaduro/essentials) untuk meningkatkan performa, keamanan, dan kualitas kode.

---

## 🚀 Asset Prefetching

```php
Vite::useAggressivePrefetching();
```

-   Mempercepat akses asset di browser.
-   Cocok untuk production dan local.

---

## ✅ Force HTTPS

```php
URL::forceHttps();
```

-   Memastikan semua URL menggunakan `https://`.
-   Disarankan aktifkan hanya di production.

---

## ✅ Immutable Dates

```php
Date::use(CarbonImmutable::class);
```

-   Menggunakan `CarbonImmutable` untuk mencegah perubahan tanggal tidak sengaja.

---

## ✅ Protect Destructive Commands

```php
DB::prohibitDestructiveCommands(app()->isProduction());
```

-   Mencegah operasi destruktif di database production (drop, truncate, dsb).

---

## ⚠ Optional / Commented Features

-   **Strict Models**: `Model::shouldBeStrict()` → mencegah lazy loading tidak sengaja, atribut tidak ada.
-   **Unguard Mass Assignment**: `Model::unguard()` → mempermudah seeding / mocking di local.
-   **Automatic Eager Loading**: `Model::automaticallyEagerLoadRelationships()` → otomatis load relasi, bisa diaktifkan jika perlu. `Laravel v12.8`
-   **Password Defaults**: `Password::defaults(...)` → atur minimum length password di production.

> Developer dapat menyalakan fitur ini dengan menghapus komentar pada baris terkait di `AppServiceProvider::boot()`.

---

## 💡 Tips

-   Gunakan environment check (`app()->isLocal()`, `app()->isProduction()`) untuk memisahkan konfigurasi dev/production.
-   Semua fitur Essentials kompatibel dengan Laravel 10 dan Filament.
