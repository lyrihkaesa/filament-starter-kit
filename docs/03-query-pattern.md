# Query Pattern: Scopes, Custom Builders & Kenapa Bukan Repository

Dokumen ini menjelaskan dua hal yang saling berkaitan:

1. **Strategi pengambilan data (Read)** — bagaimana cara membuat query yang bersih dan terukur
2. **Kenapa Repository Pattern tidak dipakai** — dan apa alternatif yang lebih sesuai dengan Laravel

Dua topik ini saya gabungkan karena jawabannya saling menjelaskan satu sama lain.

---

## Filosofi Pengambilan Data

Dalam aplikasi ini, tanggung jawab pengambilan data dibagi menjadi dua fase berdasarkan kompleksitas model:

1. **Fase Standar (Simple to Medium):** Gunakan `Local Scope` dengan atribut `#[Scope]` langsung di dalam file Model.
2. **Fase Lanjut (Large/Complex):** Pindahkan logika ke **Custom Query Builder** (file terpisah) agar Model tetap tipis dan mudah dibaca.

Filosofi dasarnya:

> Jangan lakukan over-engineering di awal, tapi bersiaplah untuk refactoring.

---

## 1. Local Scopes dengan Atribut `#[Scope]` (Rekomendasi Awal)

Gunakan pola ini jika model masih sederhana dan hanya memiliki beberapa filter. Sejak Laravel 12, kita menggunakan atribut `#[Scope]` agar kodenya lebih bersih.

### Contoh Implementasi

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    #[Scope]
    protected function search(Builder $query, string $term): void
    {
        $query->where('name', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%");
    }
}
```

Penggunaan:

```php
User::query()->active()->search('kaesa')->paginate(15);
```

**Kelebihan:** Sangat cepat diimplementasikan dan sudah bawaan Laravel.
**Kekurangan:** Jika filter bertambah banyak, file Model akan sulit dibaca.

> **Kapan pindah ke Builder?** Ketika model mulai punya lebih dari 4–5 scope, atau scope-nya melibatkan join atau subquery yang kompleks.

---

## 2. Custom Eloquent Builder — `app/Models/Builders/`

Saat scope bertambah banyak, pindahkan semua logika query ke file **Dedicated Builder**. Ini sering disebut "Query Object" atau "Custom Builder" pattern.

### Langkah-langkah Implementasi

#### A. Buat Class Builder Baru

Buat folder `app/Models/Builders/` dan file builder khusus, misalnya `UserBuilder.php`:

```php
namespace App\Models\Builders;

use Illuminate\Database\Eloquent\Builder;

class UserBuilder extends Builder
{
    public function active(): static
    {
        return $this->where('is_active', true);
    }

    public function search(string $term): static
    {
        return $this->where(function ($query) use ($term) {
            $query->where('name', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%");
        });
    }
}
```

#### B. Daftarkan di Model

Override method `newEloquentBuilder` di dalam Model:

```php
namespace App\Models;

use App\Models\Builders\UserBuilder;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static UserBuilder query()
 */
class User extends Model
{
    public function newEloquentBuilder($query): UserBuilder
    {
        return new UserBuilder($query);
    }
}
```

Penggunaan tetap identik:

```php
User::query()->active()->search('kaesa')->paginate(15);
```

Model tetap bersih. Semua query logic terpusat di satu file Builder.

### Struktur Folder

```
app/Models/
├── Builders/
│   └── UserBuilder.php
└── User.php
```

---

## 3. Tabel Keputusan: Mana yang Saya Pilih?

| Kondisi | Pola yang Disarankan |
| :--- | :--- |
| Model baru / sederhana | `#[Scope]` di dalam model |
| Model mulai memiliki > 5 scope | Pindahkan ke **Custom Builder** |
| Query melibatkan banyak Join/Subquery | Gunakan **Custom Builder** atau **Query Object** |

---

## 4. Query Object (CQRS-Style)

Untuk query yang tidak lagi "milik alami" satu model tunggal, saya lebih suka memakai Query Object.

### Query Object Sederhana

```php
class ActiveUsersQuery
{
    public function get(int $limit = 10)
    {
        return User::active()->withRecentPosts()->take($limit)->get();
    }
}
```

### Query Object dengan Sumber Data Berbeda

```php
class UserSearchQuery
{
    public function __construct(protected ?ElasticClient $elastic = null) {}

    public function search(string $term, int $limit = 20)
    {
        if (config('search.driver') === 'elastic' && $this->elastic) {
            $resp = $this->elastic->search([...]);
            return User::hydrate(collect($resp['hits']['hits'])->pluck('_source')->all());
        }

        return User::search($term)->take($limit)->get();
    }
}
```

### Kapan Query Object Terasa Tepat

- Query melibatkan banyak kondisi dan transformasi
- Data bisa datang dari beberapa sumber
- Anda ingin memberi nama eksplisit pada kebutuhan baca tertentu

---

## 5. Memahami Soft Deletes & Global Scopes

Soft Delete adalah contoh nyata dari **Global Scope** bawaan Laravel. Saat Anda menggunakan trait `SoftDeletes`, Laravel secara otomatis menambahkan scope yang memfilter data agar yang sudah "dihapus" (punya `deleted_at`) tidak muncul.

### Penggunaan di Controller / API

```php
// Mengambil semua data (termasuk yang di-soft delete)
$users = User::withTrashed()->get();

// Hanya mengambil data yang sudah di-soft delete
$deletedUsers = User::onlyTrashed()->get();

// Mengembalikan data yang terhapus
$user->restore();
```

### Integrasi di Filament Resource

Untuk model yang memiliki Soft Delete, Filament butuh konfigurasi tambahan:

1. **Tambahkan Filter di Tabel:**

```php
use Filament\Tables\Filters\TrashedFilter;

public static function table(Table $table): Table
{
    return $table->filters([
        TrashedFilter::make(),
    ]);
}
```

2. **Tambahkan Action Restore & Force Delete:**

```php
$table->actions([
    DeleteAction::make(),
    RestoreAction::make(),
    ForceDeleteAction::make(),
]);
```

---

## 6. Kasus Khusus: User Anonymization

Model `User` memiliki logika khusus. Saat user dihapus secara permanen (`forceDelete`), sistem tidak benar-benar menghapus barisnya dari database demi integritas data (misalnya agar order lama tetap punya relasi user).

**Cara kerjanya:**

- Di `app/Models/User.php`, terdapat event listener `forceDeleting`
- Saat `forceDelete()` dipanggil, data sensitif (nama, email) akan diubah menjadi "Anonymous"
- User tersebut tetap dalam status "soft deleted" agar tidak bisa login, tapi data historisnya tetap aman

Pahami logika ini sebelum melakukan `forceDelete` pada model `User`.

---

## 7. Kenapa Repository Pattern Tidak Dipakai di Sini

Di banyak tutorial Laravel, Anda sering melihat anjuran seperti ini:

> "Pisahkan data access layer dengan Repository Pattern agar lebih clean."

Masalahnya, di Laravel, Repository Pattern sering hanya menghasilkan **boilerplate** tanpa manfaat nyata. Eloquent sudah cukup kuat sebagai abstraction layer untuk database.

### Masalah yang Sering Terjadi

Developer membuat:

```php
interface UserRepositoryInterface
{
    public function find(int $id): ?User;
}
```

lalu implementasinya hanya:

```php
public function find(int $id): ?User
{
    return User::find($id);
}
```

Di titik ini, repository hanya menjadi "kulit" tambahan tanpa nilai arsitektural yang nyata.

### Alasan Utama

- **Over-engineering**: terlalu banyak file hanya untuk mirror method Eloquent
- **Eloquent sudah abstraction layer**: ORM + query builder sudah cukup kuat
- **Kehilangan fitur Laravel**: scopes, eager loading, chaining jadi canggung
- **Salah abstraksi**: repository sering dipakai untuk hal yang lebih cocok masuk ke service atau action

### Anti-Pattern Checklist

- ❌ Membuat `UserRepository` hanya untuk mirror method Eloquent
- ❌ Menaruh business logic di repository
- ❌ Mengembalikan QueryBuilder dari repository sehingga abstraksinya bocor
- ❌ Menyembunyikan Eloquent scope di repository dan kehilangan chaining alami Laravel

---

## 8. Alternatif yang Lebih Sehat

Di starter kit ini, saya lebih suka struktur seperti ini:

```text
app/
    Actions/        # business logic / use cases (Create, Update, Delete)
    Models/
    Models/Builders/ # query objects untuk kebutuhan baca data kompleks
    Services/       # external API clients / integration layer
```

Pemisahan tanggung jawab yang lebih jelas:

- **Action** untuk mutasi dan proses bisnis
- **Builder / Query Object** untuk kebutuhan baca data yang mulai kompleks
- **Service** untuk integrasi ke sistem luar

### Perbandingan: Repository vs Action + Query + Service

**Repository (boilerplate):**

```php
interface UserRepositoryInterface { public function find($id); }
class EloquentUserRepository implements UserRepositoryInterface {
    public function find($id) { return User::find($id); }
}
```

**Action + Query + Service (recommended):**

- `CreateUserAction` untuk mutasi
- `UserSearchQuery` untuk read kompleks
- `GoogleClientService` untuk API eksternal

Pemisahan ini biasanya lebih jelas, lebih testable, dan minim boilerplate.

---

## 9. Kapan Repository Pattern Masih Masuk Akal

Saya tidak bilang repository **tidak boleh sama sekali**. Ia masih masuk akal jika:

- Anda benar-benar menerapkan DDD dengan tim besar
- Storage layer memang bisa diganti total
- Anda membuat package atau library publik yang butuh interface kontrak jelas

Tetapi untuk kebanyakan project Laravel admin panel biasa, itu sering terlalu jauh.

---

## 10. Tips Praktis

- Gunakan `hydrate()` untuk data dari ElasticSearch jika memang perlu
- Tambahkan `@method` PHPDoc di model untuk autocomplete custom builder
- Jaga konsistensi return type di query object
- Biarkan controller tetap tipis: validasi → Action/Query → response
- Tambahkan caching di Query Object jika kebutuhannya memang ada

---

## Kesimpulan

- Mulai dari `#[Scope]` di model untuk query sederhana
- Naik ke Custom Builder saat scope mulai banyak (> 5)
- Pakai Query Object untuk query kompleks yang tidak lagi "milik" satu model saja
- **Repository Pattern sering over-engineering di Laravel** — gunakan Action + Query + Service sebagai gantinya

Kalau Anda pemula, pesan paling penting dari dokumen ini:

> Jangan menambah lapisan arsitektur hanya karena terlihat "lebih senior". Tambahkan lapisan hanya jika benar-benar membantu menjelaskan sistem.

Bacaan lanjutan: [02-action-pattern.md](./02-action-pattern.md), [04-policy-and-action-integration.md](./04-policy-and-action-integration.md)
