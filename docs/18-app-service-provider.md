# `AppServiceProvider` Essentials Configuration

Project ini menggunakan beberapa konfigurasi global di `AppServiceProvider` untuk meningkatkan performa, keamanan, pengalaman development, dan konsistensi perilaku aplikasi.

Dokumen ini saya sesuaikan langsung dengan isi [`app/Providers/AppServiceProvider.php`](https://github.com/lyrihkaesa/filament-starter-kit/blob/main/app/Providers/AppServiceProvider.php), jadi bagian yang dijelaskan di sini mengikuti konfigurasi project yang benar-benar sedang dipakai.

## Kenapa `AppServiceProvider` Penting

Banyak developer baru menganggap file ini hanya formalitas. Padahal justru di sinilah kita sering meletakkan aturan global seperti:

- bagaimana URL dibentuk
- bagaimana model Laravel berperilaku
- bagaimana database diproteksi
- bagaimana testing dibuat lebih aman

Kalau bagian ini dibiarkan tanpa arah, project cepat terasa inkonsisten.

## Asset Prefetching

```php
Vite::useAggressivePrefetching();
```

### Tujuan

Membantu browser memuat asset lebih cepat saat user berpindah halaman.

### Kenapa saya pakai ini

Untuk admin panel, perpindahan halaman sangat sering terjadi. Prefetching membantu UX terasa lebih cepat tanpa kita harus menulis optimasi frontend manual yang rumit.

### Studi kasus

Bayangkan admin membuka daftar user, lalu pindah ke halaman edit, lalu ke role management. Dengan aggressive prefetching, sebagian asset sudah mulai disiapkan lebih awal.

## Force HTTPS

```php
URL::forceHttps();
```

### Tujuan

Memastikan URL yang dihasilkan aplikasi menggunakan `https://`.

### Catatan penting

Di project ini baris ini sedang aktif. Karena itu, jika Anda memakai **Laravel Herd**, pastikan site Anda dijadikan **Secure Site**. Kalau belum, URL bisa terasa aneh atau request tertentu bisa bermasalah.

Kalau Anda sedang development di environment yang belum siap HTTPS, Anda bisa mengomentari baris ini terlebih dahulu.

### Studi kasus

Kalau aplikasi production Anda ada di balik SSL tetapi aplikasi masih menghasilkan URL `http://`, maka:

- link di email bisa salah
- asset URL bisa tidak konsisten
- redirect atau callback URL bisa membingungkan

## Password Defaults (Opsional)

```php
// Password::defaults(fn (): Password => app()->isProduction()
//     ? Password::min(8)->uncompromised()->letters()->numbers()->symbols()
//     : Password::min(8)
// );
```

### Tujuan

Menetapkan standar password global dari satu tempat.

### Status di project ini

Bagian ini masih dikomentari, jadi belum aktif.

### Kenapa tetap saya simpan

Karena ini berguna saat project mulai membutuhkan standar password yang lebih ketat, terutama di production.

## Prevent Lazy Loading (Anti N+1)

```php
Model::preventLazyLoading(! app()->isProduction());
```

### Tujuan

Mencegah terjadinya query N+1 selama fase development dan testing tanpa merusak kompatibilitas Filament.

### Kenapa bukan `Model::shouldBeStrict()`?

`Model::shouldBeStrict()` mengaktifkan tiga fitur sekaligus:
1. `preventLazyLoading()`
2. `preventSilentlyDiscardingAttributes()`
3. `preventAccessingMissingAttributes()`

Pada Filament, query tabel sering kali hanya memilih sebagian kolom tertentu (misalnya `select('id', 'name')`) demi efisiensi. Fitur `preventAccessingMissingAttributes()` akan melempar exception saat Filament mengakses properti internal model yang sengaja tidak dimuat. Oleh karena itu, *best practice* di starter kit ini adalah mengaktifkan `preventLazyLoading(! app()->isProduction())` secara terarah.

## Unguard Mass Assignment di Local

```php
if (app()->isLocal()) {
    Model::unguard();
}
```

### Tujuan

Mematikan proteksi mass-assignment saat development lokal.

### Kenapa saya pakai ini

Karena starter kit ini menggunakan **Action Pattern**, strict typing, dan alur mutasi data yang lebih terkontrol. Jadi di local, saya memilih fleksibilitas lebih tinggi untuk development.

### Catatan penting

Ini hanya aktif saat `app()->isLocal()`. Jadi perilaku ini memang dibatasi untuk environment development.

### Studi kasus

Saat Anda menulis seeder, factory, atau action baru, kadang lebih nyaman bekerja tanpa harus terus menyesuaikan `$fillable`. Selama struktur mutasi data tetap disiplin, pendekatan ini masih masuk akal.

## Protect Destructive Commands

```php
DB::prohibitDestructiveCommands(app()->isProduction());
```

### Tujuan

Mencegah command database yang destruktif berjalan di production.

### Contoh command berbahaya

- `migrate:fresh`
- `db:wipe`
- operasi hapus massal yang tidak semestinya dijalankan di server production

### Cara pikir saya

Saya lebih suka proteksi seperti ini aktif dari awal daripada mengandalkan semua orang selalu ingat sedang ada di environment apa.

## Handle Potentially Lost Transactions

```php
DB::handlePotentiallyLostTransactions();
```

### Tujuan

Membantu Laravel menangani situasi transaksi database yang berpotensi hilang atau gagal secara tidak ideal.

### Kenapa ini penting

Saat aplikasi mulai kompleks, kita makin sering bergantung pada transaksi untuk menjaga integritas data. Fitur ini adalah bagian dari pendekatan defensif agar perilaku transaksi lebih aman.

### Cara membayangkannya

Kalau `DB::transaction()` adalah pagar utama untuk menjaga beberapa perubahan tetap utuh, maka konfigurasi ini membantu Laravel lebih sadar ketika ada gangguan yang bisa membuat transaksi tidak berakhir seperti yang diharapkan.

## Immutable Dates

```php
Date::use(CarbonImmutable::class);
```

### Tujuan

Menggunakan `CarbonImmutable` agar object tanggal tidak berubah tanpa sengaja.

### Kenapa ini penting

Developer pemula sering tidak sadar bahwa manipulasi tanggal bisa mengubah object awal. Dengan immutable date, perubahan menjadi lebih eksplisit dan lebih aman diprediksi.

## Automatically Eager Load Relationships

```php
Model::automaticallyEagerLoadRelationships();
```

### Tujuan

Membantu Laravel melakukan eager loading relasi secara otomatis.

### Status di project ini

Bagian ini **aktif** di `AppServiceProvider`.

### Kenapa saya nyalakan

Karena project admin panel sering menampilkan relasi berulang-ulang di banyak halaman. Fitur ini membantu mengurangi beberapa kasus N+1 query tanpa kita harus selalu mengingat eager loading manual di setiap tempat.

### Catatan

Meski aktif, developer tetap perlu memahami query yang sedang berjalan. Fitur ini membantu, tetapi bukan pengganti pemahaman struktur data.

## Testing: Prevent Stray HTTP Requests (Opsional)

```php
// if (app()->runningUnitTests()) {
//     Http::preventStrayRequests();
// }
```

### Tujuan

Mencegah test secara tidak sengaja melakukan request HTTP keluar ke layanan eksternal.

### Kenapa ini berguna

Saat menulis test, kita biasanya ingin semua dependency eksternal di-fake atau di-mock. Kalau tidak, test bisa:

- lambat
- tidak stabil
- tergantung koneksi internet
- diam-diam memanggil API sungguhan

### Status di project ini

Masih dikomentari, tetapi sangat layak dipertimbangkan jika test suite makin banyak berhubungan dengan API eksternal.

## Testing: Fake Sleep (Opsional)

```php
// if (app()->runningUnitTests()) {
//     Sleep::fake();
// }
```

### Tujuan

Menonaktifkan jeda `sleep` saat testing agar test tetap cepat.

### Kenapa ini berguna

Kadang ada flow retry, polling, atau throttling sederhana yang memakai `sleep`. Di production, jeda itu masuk akal. Di testing, jeda itu hanya membuat suite menjadi lambat.

Dengan `Sleep::fake()`, test tetap menjalankan alur logikanya tanpa benar-benar menunggu.

### Studi kasus

Bayangkan Anda punya service yang mencoba ulang request 3 kali dan setiap percobaan memakai jeda 1 detik. Tanpa fake sleep, satu test bisa menunggu beberapa detik tanpa memberi nilai tambah. Dengan `Sleep::fake()`, test tetap memverifikasi flow retry tanpa membuang waktu nyata.

## Keamanan Dokumentasi API (Scramble Gate)

```php
Gate::define('viewApiDocs', function (?User $user): bool {
    if (app()->isLocal()) {
        return true;
    }

    return $user !== null && ($user->hasRole('super_admin') || $user->email === 'admin@example.com');
});
```

### Tujuan

Mengontrol otorisasi akses halaman dokumentasi OpenAPI Swagger (`/docs/api`). Di environment lokal otomatis terbuka untuk mempercepat testing, sedangkan di staging/production diproteksi hanya untuk user dengan role `super_admin`.

## Konfigurasi Default Document Head & SEO (`laravel/head`)

```php
Head::defaults(function (HeadBuilder $head): void {
    $appName = config()->string('app.name', 'Laravel');

    $head
        ->title($appName, suffix: " - {$appName}")
        ->description('Filament Starter Kit for Laravel with best practices')
        ->canonical()
        ->og(type: OgType::Website, siteName: $appName)
        ->searchableByRobots();
});
```

### Tujuan

Menetapkan metadata document `<head>` default untuk seluruh aplikasi:
- Template judul otomatis (`Page Title - AppName`).
- Default meta description dan OpenGraph metadata.
- Canonical URL otomatis berbasis URL request aktif.
- Direktif robots (`all`) yang dapat di-override di level route atau komponen Livewire.

## Enforce Morph Map

```php
Relation::enforceMorphMap(ActivitySubjectType::morphMap());
```

### Tujuan

Mencegah penyimpanan Fully Qualified Class Name (FQCN) seperti `App\Models\Post` ke dalam kolom polymorphic database (`subject_type`). Sebagai gantinya, digunakan alias yang stabil seperti `post` atau `user`. Jika ada class baru yang belum terdaftar di map, Laravel akan melempar exception sehingga integritas database tetap terjaga saat refactoring namespace model.

## Sinkronisasi Driver Gambar Glide (Curator)

```php
$this->app->resolving(GlideManager::class, function (GlideManager $manager): void {
    $configDriver = config('image.driver');
    $driver = ($configDriver === \Intervention\Image\Drivers\Imagick\Driver::class) ? 'imagick' : 'gd';
    // set serverConfig ...
});
```

### Tujuan

Memastikan server pemrosesan thumbnail dan media pada Filament Curator menggunakan driver gambar yang selaras (`gd` atau `imagick`) dengan konfigurasi `IMAGE_DRIVER` di environment aplikasi.

## Rate Limiter API

```php
RateLimiter::for('api', function (Request $request): Limit {
    $key = $request->user()?->getAuthIdentifier() ?? $request->ip();

    return Limit::perMinute(60)->by($key);
});

RateLimiter::for('api-auth', fn (Request $request): Limit => Limit::perMinute(5)->by($request->ip()));
```

### Tujuan

Melindungi endpoint API dari serangan brute force dan denial of service:
- `api`: Batas 60 request per menit per user (atau per IP jika guest).
- `api-auth`: Batas ketat 5 request per menit per IP untuk endpoint autentikasi (`/api/v1/auth/login`).

## Studi Kasus Besar

Misalnya project Anda sudah dipakai tim internal:

- ada developer baru yang memakai Herd tapi belum mengaktifkan secure site
- ada action yang banyak memakai transaksi database
- ada halaman admin yang sering menampilkan relasi
- ada test yang berhubungan dengan API eksternal
- ada flow retry yang memakai sleep

Semua itu bisa dipengaruhi langsung oleh konfigurasi kecil di `AppServiceProvider`. Karena itu, file ini terlihat sederhana, tetapi dampaknya global.

## Tips

- Gunakan `app()->isLocal()`, `app()->isProduction()`, dan `app()->runningUnitTests()` untuk memisahkan perilaku per environment
- Jangan aktifkan semua fitur hanya karena terlihat canggih; pahami dulu efek sampingnya
- Setelah mengubah `AppServiceProvider`, uji flow utama aplikasi karena dampaknya bisa terasa ke banyak tempat
