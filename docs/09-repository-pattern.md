# Repository Pattern (Jangan)

> **Catatan:** Artikel ini memang ditulis sebagai dokumentasi pribadi sekaligus panduan praktis. Fokus utamanya adalah menjelaskan kenapa Repository Pattern biasanya terasa over-engineering di Laravel, dan kenapa saya lebih memilih kombinasi **Action Pattern, Query Pattern, dan Service Pattern**.

Dokumen ini sengaja saya pertahankan nuansa opini dan catatan pribadinya, karena bagian ini memang merepresentasikan cara pikir starter kit ini.

## 1. Pendahuluan

Di banyak tutorial Laravel, kita sering melihat anjuran seperti ini:

"Pisahkan data access layer dengan Repository Pattern agar lebih clean."

Masalahnya, di Laravel, Repository Pattern sering hanya menghasilkan **boilerplate** tanpa manfaat nyata. Eloquent sudah cukup kuat sebagai abstraction layer untuk database.

Jadi, saya tidak menolak repository karena gaya-gayaan. Saya menolaknya karena dalam banyak project Laravel biasa, ia justru membuat arsitektur terasa lebih berat daripada yang diperlukan.

## 2. Apa Itu Repository Pattern

Repository Pattern adalah lapisan abstraksi untuk akses data.

Tujuannya secara teori:

- kode tidak langsung bergantung pada storage
- implementasi data source bisa ditukar
- domain layer lebih terlindungi dari detail persistence

Konsep ini sangat masuk akal di ekosistem seperti:

- Java Spring Boot
- .NET
- arsitektur clean architecture yang ketat

Tetapi Laravel sudah datang dengan Eloquent, query builder, scopes, relation, eager loading, accessor, dan banyak fasilitas lain yang sebenarnya sudah menjadi abstraction layer yang kaya.

## 3. Kenapa Repository Pattern Tidak Perlu di Laravel

Beberapa alasan utama:

- **Over-engineering**: terlalu banyak file hanya untuk mirror method Eloquent
- **Eloquent sudah abstraction layer**: ORM + query builder sudah cukup kuat
- **Kehilangan fitur Laravel**: scopes, eager loading, chaining jadi canggung
- **Salah abstraksi**: repository sering dipakai untuk hal yang sebenarnya lebih cocok masuk ke service atau action

### Contoh masalah yang sering saya lihat

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

## 4. Struktur Alternatif yang Sehat

Di starter kit ini, saya lebih suka struktur seperti ini:

```text
app/
    Actions/      # business logic / use cases
    Models/
    Queries/      # query objects untuk kebutuhan baca data
    Repositories/ # ❌ jangan sampai anda membuat ini tanpa alasan kuat
    Services/     # external API clients / integration layer
```

### Cara membaca struktur ini

- **Action** untuk mutasi dan proses bisnis
- **Query** untuk kebutuhan baca data yang mulai kompleks
- **Service** untuk integrasi ke sistem luar

Dengan pola ini, tanggung jawab file biasanya lebih jelas.

## 5. Eloquent Scopes

### Local Scope

```php
class User extends Model
{
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeWithRecentPosts($query, int $limit = 5)
    {
        return $query->with(['posts' => fn($q) => $q->latest()->limit($limit)]);
    }
}
```

Penggunaan:

```php
$users = User::active()->withRecentPosts(3)->get();
```

### Kenapa scope lebih enak

Untuk query yang masih dekat dengan model, scope sangat natural karena:

- tetap chainable
- tetap terasa idiomatis Laravel
- tidak menambah lapisan abstraksi baru

## 6. Custom Eloquent Builder (`newEloquentBuilder`)

Kalau query mulai lebih kaya, Anda bisa naik kelas ke custom builder.

### Builder Class

```php
class UserBuilder extends Builder
{
    public function active(): static
    {
        return $this->where('active', true);
    }

    public function search(string $term): static
    {
        return $this->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
    }
}
```

### Model Override

```php
class User extends Model
{
    public function newEloquentBuilder($query): UserBuilder
    {
        return new UserBuilder($query);
    }
}
```

Penggunaan:

```php
$users = User::active()->search('kaesa')->paginate(15);
```

## 7. Query Pattern (CQRS-style)

Untuk query yang tidak lagi "milik alami" model tunggal, saya lebih suka memakai Query Object.

### Query Object sederhana

```php
class ActiveUsersQuery
{
    public function get(int $limit = 10)
    {
        return User::active()->withRecentPosts()->take($limit)->get();
    }
}
```

### Query Object dengan ElasticSearch

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

### Kapan Query Object terasa tepat

- query melibatkan banyak kondisi dan transformasi
- data bisa datang dari beberapa sumber
- Anda ingin memberi nama eksplisit pada kebutuhan baca tertentu

## 8. Action Pattern (Use Case)

Untuk mutasi data, saya lebih memilih Action Pattern.

```php
class CreateUserAction
{
    public function __construct(protected WelcomeEmailService $mailer) {}

    public function handle(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([...]);
            $this->mailer->sendWelcome($user);

            return $user;
        });
    }
}
```

Controller:

```php
$user = app(CreateUserAction::class)->handle($request->validated());
```

Perhatikan bahwa saya memakai `handle()`, bukan `execute()`, agar konsisten dengan pola starter kit ini.

## 9. Service Pattern

Untuk API eksternal atau SDK pihak ketiga, saya lebih suka Service Pattern.

```php
class GoogleClientService
{
    public function __construct(protected GoogleClient $client) {}

    public function getUserProfile(string $token): array
    {
        $this->client->setAccessToken($token);

        return $this->client->fetchUserProfile();
    }
}
```

## 10. Testing

### Action Test

```php
public function test_create_user_and_send_email()
{
    $mailer = Mockery::mock(WelcomeEmailService::class);
    $mailer->shouldReceive('sendWelcome')->once();
    $this->app->instance(WelcomeEmailService::class, $mailer);

    $action = $this->app->make(CreateUserAction::class);
    $user = $action->handle([
        'name' => 'Kaesa',
        'email' => 'kaesa@example.com',
        'password' => 'secret',
    ]);

    $this->assertDatabaseHas('users', ['email' => 'kaesa@example.com']);
}
```

### Kenapa test action terasa enak

Karena action biasanya punya satu tugas jelas. Dibanding mengetes controller yang bercampur validasi, response, redirect, dan middleware, action sering lebih fokus dan lebih mudah dipahami.

## 11. Kapan Repository Pattern Masih Masuk Akal

Saya tidak bilang repository **tidak boleh sama sekali**. Ia masih masuk akal jika:

- Anda benar-benar menerapkan DDD dengan tim besar
- storage layer memang bisa diganti total
- Anda membuat package atau library publik yang butuh interface kontrak jelas

Tetapi untuk kebanyakan project Laravel admin panel biasa, itu sering terlalu jauh.

## 12. Anti-pattern Checklist

- ❌ Membuat `UserRepository` hanya untuk mirror method Eloquent
- ❌ Menaruh business logic di repository
- ❌ Mengembalikan QueryBuilder dari repository sehingga abstraksinya bocor
- ❌ Menyembunyikan Eloquent scope di repository dan kehilangan chaining alami Laravel

## 13. Perbandingan: Repository vs Action + Query + Service

### Repository (boilerplate)

```php
interface UserRepositoryInterface { public function find($id); }
class EloquentUserRepository implements UserRepositoryInterface {
    public function find($id) { return User::find($id); }
}
```

### Action + Query + Service (recommended)

- `CreateUserAction` untuk mutasi
- `UserSearchQuery` untuk read
- `GoogleClientService` untuk API

Pemisahan ini biasanya lebih jelas, lebih testable, dan minim boilerplate.

## 14. Practical Tips

- Gunakan `hydrate()` untuk data dari ElasticSearch jika memang perlu
- Tambahkan `@method` PHPDoc di model untuk autocomplete custom builder
- Jaga konsistensi return type di query object
- Biarkan controller tetap tipis: validasi → Action/Query → response
- Tambahkan caching di Query Object jika kebutuhannya memang ada

## 15. Kesimpulan

- **Repository Pattern sering over-engineering di Laravel**
- Gunakan:
  - **Action Pattern** untuk business logic
  - **Query Pattern** untuk data access yang lebih fleksibel
  - **Service Pattern** untuk API eksternal

Simpel, testable, maintainable, dan lebih cocok dengan cara kerja Laravel modern.

Kalau Anda pemula, pesan paling penting dari file ini sederhana:

> Jangan menambah lapisan arsitektur hanya karena terlihat "lebih senior". Tambahkan lapisan hanya jika benar-benar membantu menjelaskan sistem.
