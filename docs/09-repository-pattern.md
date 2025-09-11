# Repository Pattern (Jangan)

> **Catatan:** Artikel ini ditulis sebagai dokumentasi pribadi sekaligus panduan praktis.  
> Fokus: kenapa Repository Pattern biasanya over-engineering di Laravel,  
> dan alternatif yang lebih sehat: **Action Pattern, Query Pattern, dan Service Pattern.**

---

## 1. Pendahuluan

Di banyak tutorial Laravel, kita sering lihat anjuran:  
“Pisahkan data access layer dengan Repository Pattern agar lebih clean.”

Masalahnya: di Laravel, Repository Pattern biasanya hanya menghasilkan **boilerplate** tanpa manfaat nyata.  
Eloquent sudah cukup kuat sebagai abstraction layer untuk database.

---

## 2. Apa Itu Repository Pattern

Repository Pattern = lapisan abstraksi untuk akses data.

-   Tujuan: kode tidak langsung bergantung pada storage (DB, API, file).
-   Konsep ini wajar di **Java (Spring Boot)** atau **Flutter (Clean Architecture)**.

---

## 3. Kenapa Repository Pattern Tidak Perlu di Laravel

-   **Over-engineering**: terlalu banyak file hanya untuk mirror method Eloquent.
-   **Eloquent sudah abstraction layer** (ORM + query builder).
-   **Kehilangan fitur Laravel**: scopes, eager loading, chaining.
-   **Salah abstraksi**: Repository cocok untuk unify multiple datasource. Untuk API eksternal, gunakan **Service Pattern**.

---

## 4. Struktur Alternatif yang Sehat

Gunakan kombinasi ini:

```
app/
    Actions/ # business logic (Use Cases)
    Models/
    Queries/ # query objects
    Repositories/ # ❌ Jangan sampai anda membuat ini
    Services/ # external API clients
```

---

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

---

## 6. Custom Eloquent Builder (`newEloquentBuilder`)

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

---

## 7. Query Pattern (CQRS-style)

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

---

## 8. Action Pattern (Use Case)

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

---

## 9. Service Pattern

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

---

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

---

## 11. Kapan Repository Pattern Masih Masuk Akal

-   Strict Domain-Driven Design dengan tim besar.
-   Storage layer bisa diganti total (RDBMS → API).
-   Library/framework publik yang butuh interface kontrak.

---

## 12. Anti-pattern Checklist

-   ❌ Membuat `UserRepository` hanya untuk mirror method Eloquent.
-   ❌ Menaruh business logic di repository.
-   ❌ Mengembalikan QueryBuilder dari repository (leak abstraksi).
-   ❌ Menyembunyikan Eloquent scope di repo, menghilangkan chaining.

---

## 13. Perbandingan: Repository vs Action+Query+Service

### Repository (boilerplate)

```php
interface UserRepositoryInterface { public function find($id); }
class EloquentUserRepository implements UserRepositoryInterface {
    public function find($id) { return User::find($id); }
}
```

### Action + Query + Service (recommended)

-   `CreateUserAction` untuk mutasi,
-   `UserSearchQuery` untuk read,
-   `GoogleClientService` untuk API.

Lebih jelas, testable, dan minim boilerplate.

---

## 14. Practical Tips

-   Gunakan `hydrate()` untuk data dari ElasticSearch.
-   Tambahkan `@method` PHPDoc di Model untuk autocomplete custom builder.
-   Konsistensi return type di Query Objects (Collection, Paginator, array).
-   Controller tetap tipis: validasi → Action/Query → Response.
-   Cache query di Query Objects jika perlu.

---

## 15. Kesimpulan

-   **Repository Pattern = over-engineering di Laravel**.
-   Gunakan:

    -   **Action Pattern** untuk business logic,
    -   **Query Pattern** untuk data access fleksibel,
    -   **Service Pattern** untuk API eksternal.

-   Simpel, testable, maintainable.
-   Jangan pakai Repository hanya karena tutorial — pikirkan konteks.
