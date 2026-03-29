# 23 - Query Patterns: Scopes vs Custom Builders

Dokumen ini menjelaskan strategi pengambilan data (Read) di dalam aplikasi agar tetap bersih, terukur, dan sesuai dengan prinsip CQRS yang digabungkan dengan **Action Pattern**.

## Filosofi Pengambilan Data
Dalam aplikasi ini, kita membagi tanggung jawab pengambilan data menjadi dua fase berdasarkan kompleksitas model:

1.  **Fase Standar (Small to Medium):** Menggunakan `Local Scope` dengan atribut `#[Scope]` di dalam file Model.
2.  **Fase Lanjut (Large/Complex):** Memindahkan logika ke **Custom Query Builder** (file terpisah) agar Model tetap "Thin" (tipis).

---

## 1. Local Scopes dengan Atribut `#[Scope]` (Rekomendasi Awal)
Gunakan pola ini jika model Anda masih sederhana dan hanya memiliki beberapa filter pencarian. Sejak Laravel 12, kita menggunakan atribut khusus agar kodenya lebih bersih.

### Contoh Implementasi:
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('is_published', true);
    }

    #[Scope]
    protected function byCategory(Builder $query, int $categoryId): void
    {
        $query->where('category_id', $categoryId);
    }
}
```

**Kelebihan:** Sangat cepat untuk diimplementasikan dan sudah bawaan Laravel.
**Kekurangan:** Jika filter bertambah banyak, file Model akan menjadi sulit dibaca.

---

## 2. Custom Query Builders (Rekomendasi Skala Besar)
Jika sebuah Model mulai memiliki banyak logika query, pindahkan semua logika tersebut ke file **Dedicated Builder**. Ini sering disebut sebagai "Query Object" atau "Custom Builder" pattern.

### Langkah Implementasi:

#### A. Buat Class Builder Baru
Buat folder `app/Models/Builders` dan buat file builder khusus (misal: `UserBuilder.php`).

```php
namespace App\Models\Builders;

use Illuminate\Database\Eloquent\Builder;

class UserBuilder extends Builder
{
    public function whereActive(): self
    {
        return $this->where('active', true);
    }

    public function search(string $term): self
    {
        return $this->where(function ($query) use ($term) {
            $query->where('name', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%");
        });
    }
}
```

#### B. Daftarkan di Model
Overide method `newEloquentBuilder` di dalam Model Anda.

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

---

## Strategi Migrasi
"Jangan melakukan over-engineering di awal, tapi bersiaplah untuk refactoring."

| Kondisi | Pola yang Disarankan |
| :--- | :--- |
| Model baru / Sederhana | `#[Scope]` di dalam model. |
| Model mulai memiliki > 5 scope | Pindahkan ke **Custom Builder**. |
| Query melibatkan banyak Join/Subquery | Gunakan **Custom Builder** atau **Query Object** (Dedicated Class). |

---

## 3. Memahami Soft Deletes & Global Scopes
Soft Delete adalah contoh nyata dari **Global Scope** bawaan Laravel. Saat Anda menggunakan trait `SoftDeletes`, Laravel secara otomatis menambahkan scope yang memfilter data agar yang sudah "dihapus" (punya `deleted_at`) tidak muncul.

### A. Penggunaan di Controller / API
Jika Anda ingin mengambil data termasuk yang sudah dihapus, Anda harus memanggil scope tambahannya secara eksplisit:

```php
// Mengambil semua data (termasuk yang di-soft delete)
$users = User::withTrashed()->get();

// Hanya mengambil data yang sudah di-soft delete
$deletedUsers = User::onlyTrashed()->get();

// Mengembalikan data yang terhapus
$user->restore();
```

### B. Integrasi di Filament Resource
Untuk model yang memiliki Soft Delete, Filament butuh konfigurasi tambahan di Resource agar user bisa melihat, merestore, atau menghapus permanen data tersebut.

1.  **Gunakan Trait di Resource:**
    Pastikan `Resource` Anda menggunakan trait `Eloquent\Resources\Resource\Pages\EditRecord\Concerns\SoftDeleting` jika diperlukan, tapi standarnya Filament akan mendeteksi otomatis jika Model menggunakan `SoftDeletes`.

2.  **Tambahkan Filter di Tabel:**
    Agar user bisa memfilter data yang terhapus di tabel:
    ```php
    use Filament\Tables\Filters\TrashedFilter;

    public static function table(Table $table): Table
    {
        return $table
            ->filters([
                TrashedFilter::make(),
            ]);
    }
    ```

3.  **Tambahkan Action Restore & Force Delete:**
    ```php
    use Filament\Tables\Actions\DeleteAction;
    use Filament\Tables\Actions\ForceDeleteAction;
    use Filament\Tables\Actions\RestoreAction;

    $table->actions([
        DeleteAction::make(),
        RestoreAction::make(),
        ForceDeleteAction::make(),
    ]);
    ```

---

## 4. Kasus Khusus: User Anonymization
Di proyek ini, model `User` memiliki logika khusus. Saat user dihapus secara permanen (`forceDelete`), sistem tidak benar-benar menghapus barisnya dari database demi integritas data (misal: agar order lama tetap punya relasi user).

**Cara Kerjanya:**
- Di `app/Models/User.php`, terdapat event listener `forceDeleting`.
- Saat `forceDelete()` dipanggil, data sensitif (nama, email) akan diubah menjadi "Anonymous".
- User tersebut akan tetap dalam status "soft deleted" agar tidak bisa login, tapi data historisnya tetap aman.

Pahami logika ini sebelum melakukan operasi `forceDelete` pada model `User`.
