# Tutorial: Membuat Modul Baru dari Nol

Panduan ini ditujukan bagi pemula yang ingin memahami alur kerja standar di starter kit ini, mulai dari pembuatan tabel hingga pengujian fitur.

Kita akan mengambil studi kasus: **Modul Product**.

---

## Langkah 1: Migration & Model

Buat model beserta file migration-nya.

```bash
php artisan make:model Product -m
```

Buka file migration di `database/migrations/..._create_products_table.php`:

```php
public function up(): void
{
    Schema::create('products', function (Blueprint $table) {
        $table->uuid('id')->primary(); // Pakai UUID sesuai standar
        $table->string('name');
        $table->text('description')->nullable();
        $table->decimal('price', 10, 2);
        $table->integer('stock')->default(0);
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}
```

Jalankan migrasi:
```bash
php artisan migrate
```

---

## Langkah 2: Query Pattern (Scope & Builder)

Jangan biarkan Model dikotori oleh logika query yang rumit. Mulailah dengan **Local Scope**, dan jika sudah kompleks, pindah ke **Custom Eloquent Builder**.

### Level 1: Local Scope (Sederhana)

Tambahkan method di `app/Models/Product.php`:

```php
public function scopeActive(Builder $query): void
{
    $query->where('is_active', true);
}
```

### Level 2: Custom Eloquent Builder (Lebih Rapi & Kompleks)

Jika logic query Anda mulai banyak (misal: filter berdasarkan kategori, rentang harga, stok menipis), buatlah class Builder khusus.

1. Buat folder `app/Models/Builders` (jika belum ada).
2. Buat file `ProductBuilder.php`:

```php
namespace App\Models\Builders;

use Illuminate\Database\Eloquent\Builder;

final class ProductBuilder extends Builder
{
    public function whereActive(): self
    {
        return $this->where('is_active', true);
    }

    public function whereOutOfStock(): self
    {
        return $this->where('stock', '<=', 0);
    }
}
```

3. Hubungkan di Model `Product.php`:

```php
public function newEloquentBuilder($query): ProductBuilder
{
    return new ProductBuilder($query);
}
```

Sekarang Anda bisa memanggil: `Product::query()->whereActive()->whereOutOfStock()->get()`.

> **Catatan:** Baca detailnya di [docs/03-query-pattern.md](./03-query-pattern.md).

---

## Langkah 3: Factory & Seeder

Agar testing mudah, buatlah Factory.

```bash
php artisan make:factory ProductFactory --model=Product
```

Edit code di `database/factories/ProductFactory.php` agar menghasilkan data dummy yang realistis.

---

## Langkah 4: Action Pattern (Business Logic)

Gunakan action untuk mutasi data seperti Create, Update, atau Delete.

```bash
php artisan make:action --model=Product
```

Ini akan menghasilkan 3 action di subfolder `app/Actions/Products/`. Edit `CreateProductAction.php` untuk menambahkan logic tambahan (misal slug, atau log):

```php
public function handle(array $data): Product
{
    return Product::create($data);
}
```

---

## Langkah 5: Policy (Authorization)

Pastikan hanya user tertentu yang bisa mengelola produk.

```bash
php artisan make:policy ProductPolicy --model=Product
```

Lalu daftarkan rule-nya (misal: hanya Admin yang bisa delete).

---

## Langkah 6: Filament Resource (UI Admin)

Gunakan command khusus starter-kit agar file resource terbagi rapi.

```bash
php artisan make:starter-resource Product
```

Command ini akan membuat folder:
- `app/Filament/Resources/ProductResource/`
- `app/Filament/Resources/ProductResource/Schemas/` (Isi Form dan Table)
- `app/Filament/Resources/ProductResource/Pages/`

Hubungkan **Action Pattern** di file Page (Create/Edit) agar UI Filament memanggil logika bisnis yang sama dengan API atau Job.

---

## Langkah 7: Testing dengan Pest

Terakhir, buatlah test untuk memastikan fitur berjalan benar.

```bash
php artisan make:test Products/CreateProductTest --pest
```

Contoh test sederhana:
```php
it('can create a product via action', function () {
    $data = [
        'name' => 'Kopi Gayo',
        'price' => 50000,
        'stock' => 10,
    ];

    $product = app(CreateProductAction::class)->handle($data);

    expect($product->name)->toBe('Kopi Gayo');
    expect(Product::count())->toBe(1);
});
```

---

## Ringkasan Alur Kerja

1. **Database:** Migration & Model
2. **Query:** Scope atau Builder khusus
3. **Dummy:** Factory
4. **Logic:** Action Pattern (handle())
5. **Security:** Policy
6. **UI:** Filament Resource
7. **Quality:** Test

Dengan mengikuti alur di atas, modul Anda akan sangat rapi, mudah dites, dan siap berkembang tanpa menjadi "ruwet".
