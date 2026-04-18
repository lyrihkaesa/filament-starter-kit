# UUID sebagai Primary Key Default

Di starter kit ini, saya **menyarankan menggunakan UUID** untuk primary key dibandingkan auto increment biasa.

Ini bukan sekadar preferensi gaya. Saya memang ingin project baru yang dibuat dari starter kit ini bergerak ke pola identifier yang lebih aman dan lebih fleksibel untuk jangka panjang.

Selain itu, project ini juga sudah punya rule khusus untuk agent di [`uuids.md`](https://github.com/lyrihkaesa/filament-starter-kit/blob/main/.agents/rules/uuids.md) agar saat agent seperti Gemini CLI, Codex, atau Claude membuat migration dan model baru, mereka mengikuti preferensi ini.

## Kenapa Saya Lebih Suka UUID daripada Auto Increment

Auto increment itu sederhana, tetapi punya beberapa keterbatasan:

- mudah ditebak urutannya
- kurang ideal kalau data nantinya berasal dari banyak sumber
- lebih terasa "lokal database" daripada identifier yang fleksibel dibawa ke banyak konteks

UUID memberi beberapa keuntungan:

- identifier lebih sulit ditebak
- lebih aman untuk diekspos di URL atau API
- lebih cocok jika nanti ada kebutuhan sinkronisasi atau data dari beberapa sumber
- tidak bergantung pada urutan angka database lokal

## Kenapa Bukan Auto Increment

Kalau Anda membuat URL seperti:

```text
/users/1
/users/2
/users/3
```

user atau attacker lebih mudah menebak pola datanya.

Kalau identifier memakai UUID, pola ini tidak lagi mudah ditebak.

Untuk admin panel internal, ini memang bukan satu-satunya lapisan keamanan. Tetapi saya tetap lebih suka identifier yang tidak terlalu predictable.

## Kenapa Saya Tetap Pilih UUID, Bukan ULID

ULID sering dianggap menarik karena bentuknya lebih pendek dan sortable. Tetapi untuk starter kit ini, saya tetap memilih **UUID sebagai rekomendasi utama**.

Alasannya:

- Laravel sudah punya dukungan UUID yang sangat baik
- arah modern UUID di Laravel sudah cukup bagus
- saya ingin rule project tetap sederhana dan konsisten
- saya tidak merasa perlu menambah percabangan keputusan "pakai UUID atau ULID" untuk baseline starter kit

Jadi posisi saya:

- **UUID adalah default recommendation**
- **ULID bukan dilarang**, tetapi bukan pilihan utama starter kit ini

## Laravel dan UUID v7

Hal penting yang saya ingin jelaskan di sini: pendekatan UUID modern di Laravel sudah mendukung **UUID v7** yang sortable.

Kenapa ini penting?

Salah satu kritik lama terhadap UUID versi acak lama adalah urutannya tidak ramah untuk insertion order dan indexing tertentu. UUID v7 memperbaiki arah ini dengan pendekatan yang lebih sortable.

Jadi kalau dulu orang menghindari UUID karena alasan performa insertion acak, sekarang diskusinya tidak sesederhana itu lagi. Itulah salah satu alasan kenapa saya nyaman menjadikan UUID sebagai rekomendasi utama.

## Catatan Tentang UUID Versi Lama

Sebagai catatan pribadi saya:

- UUID versi lama tetap valid
- UUID versi lama tetap bisa dipakai
- tetapi saya tidak menjadikannya sebagai rekomendasi utama untuk tabel baru

Kenapa?

Karena untuk desain schema baru, saya lebih suka bergerak ke pendekatan UUID modern yang lebih sortable dibanding UUID lama yang murni acak.

Jadi, kalau ada schema lama yang masih memakai UUID versi lama, itu bukan berarti salah. Saya hanya tidak ingin menjadikannya baseline untuk desain baru.

## Kustomisasi Paket Pihak Ketiga (PENTING)

Salah satu keunikan starter kit ini adalah **pemaksaan UUID v7** bahkan untuk paket pihak ketiga yang secara default menggunakan Integer/BigInt.

Jika Anda terbiasa menggunakan paket seperti Spatie Permission atau Activitylog, Anda mungkin akan bingung kenapa di sini mereka menggunakan UUID. Berikut adalah penjelasannya:

### 1. Kenapa Saya Mengubah Default Paket?
Konsistensi adalah kunci. Saya tidak ingin memiliki database yang campur aduk antara UUID (untuk tabel utama) dan BigInt (untuk tabel paket). Dengan UUID v7 yang sortable, kita mendapatkan keuntungan keamanan UUID tanpa mengorbankan performa indexing database secara signifikan.

### 2. Apa Saja yang Diubah?
Saya melakukan "Override" pada model dan migrasi bawaan paket-paket berikut:

- **Spatie Permission**: Tabel `roles` dan `permissions` menggunakan UUID.
- **Spatie Activitylog**: Tabel `activity_log` menggunakan UUID.
- **Laravel Sanctum**: Tabel `personal_access_tokens` menggunakan UUID.

### 3. Cara Kerjanya (Custom Models)
Untuk mendukung ini, saya tidak menggunakan model bawaan paket secara langsung, melainkan meng-extend mereka ke dalam folder `app/Models/`:

- `App\Models\Role` meng-extend `Spatie\Permission\Models\Role`
- `App\Models\Permission` meng-extend `Spatie\Permission\Models\Permission`
- `App\Models\Activity` meng-extend `Spatie\Activitylog\Models\Activity`
- `App\Models\PersonalAccessToken` meng-extend `Laravel\Sanctum\PersonalAccessToken`

Semua model di atas menggunakan trait `HasUuids` dan dikonfigurasi dengan:
```php
public $incrementing = false;
protected $keyType = 'string';
```

### 4. Konfigurasi yang Harus Diperhatikan
Jangan kaget jika Anda melihat isi file konfigurasi paket sudah saya ubah:
- `config/permission.php`: `models.role` dan `models.permission` diarahkan ke `App\Models`.
- `config/activitylog.php`: `activity_model` diarahkan ke `App\Models\Activity`.
- `AppServiceProvider.php`: Menggunakan `Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class)`.

**Pesan untuk Developer:** Jika Anda menambah paket baru yang memiliki tabel database, pastikan Anda mengikuti pola yang sama: gunakan migrasi UUID dan buat custom model jika paket tersebut default-nya menggunakan Integer.

## Rule Project untuk Agent

Project ini punya rule khusus di:

- [`uuids.md`](https://github.com/lyrihkaesa/filament-starter-kit/blob/main/.agents/rules/uuids.md)

Tujuan rule itu adalah supaya agent seperti:

- Gemini CLI
- Codex
- Claude

otomatis mengikuti preferensi project ini saat membuat:

- migration baru
- model baru
- relationship baru

Artinya, agent seharusnya tidak lagi default ke:

```php
$table->id();
```

tetapi ke pola UUID.

## Pola Migration yang Saya Sarankan

Untuk tabel baru:

```php
$table->uuid('id')->primary();
```

Untuk foreign key:

```php
$table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
```

Jangan campur integer foreign key dengan UUID primary key untuk desain baru, kecuali memang ada alasan khusus dan sangat sadar.

## Pola Model yang Saya Sarankan

Di model Eloquent:

```php
use Illuminate\Database\Eloquent\Concerns\HasUuids;

final class Post extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';
}
```

Ini membuat model bekerja dengan primary key string berbasis UUID.

## Studi Kasus

Bayangkan Anda membuat tabel:

- `users`
- `posts`
- `comments`

Kalau Anda dari awal memakai UUID, maka relasinya tetap konsisten:

- `users.id` adalah UUID
- `posts.user_id` juga UUID
- `comments.post_id` juga UUID

Struktur seperti ini lebih enak dijaga konsistensinya daripada memulai dari integer lalu belakangan ingin migrasi ke UUID saat aplikasi sudah besar.

## Kapan Auto Increment Masih Mungkin Dipakai

Saya tidak bilang auto increment haram. Tetapi di starter kit ini, ia **bukan default recommendation**.

Kalau suatu saat ada kebutuhan yang sangat spesifik dan memang lebih cocok pakai integer ID, itu bisa diputuskan secara sadar. Tetapi untuk baseline project baru, saya tetap menyarankan UUID.

## Kesimpulan

Posisi saya di starter kit ini jelas:

- sarankan **UUID** untuk tabel baru
- jangan default ke auto increment
- tidak perlu menjadikan **ULID** sebagai pilihan utama
- pahami bahwa pendekatan UUID modern Laravel sudah bergerak ke **UUID v7** yang sortable
- anggap UUID versi lama sebagai catatan historis atau kompatibilitas, bukan default desain baru

Kalau developer atau agent membuat migration baru di repository ini, saya ingin arah default-nya mengikuti keputusan tersebut.
