# Architecture Overview

Dokumen ini menjelaskan secara menyeluruh cara starter kit ini bekerja: mulai dari pemisahan tanggung jawab antar layer, konvensi penamaan, hingga alasan di balik setiap keputusan arsitektur. Ini adalah titik masuk yang baik sebelum Anda membaca dokumen lain yang lebih detail.

## Filosofi Utama

Arsitektur starter kit ini dibangun di atas satu prinsip sederhana:

> **Setiap file harus punya satu alasan untuk berubah.**

Filament Resource tidak boleh tahu cara mengirim email. Controller tidak boleh tahu cara memindahkan file. Model tidak boleh berisi business logic yang kompleks. Masing-masing punya perannya sendiri, dan starter kit ini menegakkan itu melalui struktur folder dan konvensi yang jelas.

---

## Tiga Layer Utama

Berikut adalah tiga layer yang paling sering Anda temui saat membangun fitur baru.

### 1. Mutations (Create, Update, Delete) → `app/Actions/`

Semua operasi yang **mengubah state** — buat, ubah, hapus — harus hidup di dalam kelas Action.

```
app/Actions/
├── Users/
│   ├── CreateUserAction.php
│   ├── UpdateUserAction.php
│   └── DeleteUserAction.php
└── Posts/
    └── PublishPostAction.php
```

**Konvensi:**
- Satu class = satu pekerjaan bisnis yang spesifik
- Method utama selalu `handle(...)`
- Action tidak boleh berisi authorization atau routing logic
- Action boleh memakai `DB::transaction()` jika operasi melibatkan lebih dari satu model
- Generate dengan: `php artisan make:action --model=Post`

**Contoh:**

```php
final readonly class CreateUserAction
{
    public function handle(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create($data);
            $user->assignRole(Role::MEMBER);

            return $user;
        });
    }
}
```

**Mengapa bukan di Controller atau Filament Page?**

Karena logic ini perlu bisa dipanggil dari mana saja: controller API, Filament Page, Artisan Command, atau bahkan Queue Job. Menaruhnya di Action berarti ia hanya ditulis sekali.

Bacaan lebih lanjut: [02-action-pattern.md](./02-action-pattern.md)

---

### 2. Queries (Read) → Model Scopes / `app/Models/Builders/`

Pembacaan data mengikuti pendekatan **progresif** berdasarkan kompleksitas model.

#### Fase 1: Local Scopes di dalam Model

Untuk model yang masih sederhana, gunakan atribut `#[Scope]` langsung di file model.

```php
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

> **Kapan pindah ke Builder?** Ketika model mulai punya lebih dari 4–5 scope, atau scope-nya mulai melibatkan join atau subquery yang kompleks.

---

#### Fase 2: Custom Eloquent Builder di `app/Models/Builders/`

Saat scope bertambah banyak, pindahkan semua logika query ke file Builder tersendiri.

```
app/Models/
├── Builders/
│   └── UserBuilder.php
└── User.php
```

**Builder:**

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
        return $this->where(fn ($q) =>
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
        );
    }
}
```

**Registrasi ke Model:**

```php
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

Bacaan lebih lanjut: [03-query-pattern.md](./03-query-pattern.md)

---

### 3. External Integrations → `app/Services/`

Semua komunikasi dengan sistem di luar aplikasi — API pihak ketiga, SDK eksternal, layanan payment, SMS, notifikasi push — harus dibungkus dalam kelas Service.

```
app/Services/
├── GoogleClientService.php
├── MidtransPaymentService.php
└── FcmNotificationService.php
```

**Konvensi:**
- Service hanya mengetahui cara berbicara dengan sistem eksternal
- Service tidak boleh berisi business logic domain
- Service diinjeksikan ke Action jika diperlukan

**Contoh:**

```php
final class GoogleClientService
{
    public function __construct(
        protected readonly GoogleClient $client
    ) {}

    public function getUserProfile(string $token): array
    {
        $this->client->setAccessToken($token);

        return $this->client->fetchUserProfile();
    }
}
```

Dipakai dari Action:
```php
final readonly class RegisterWithGoogleAction
{
    public function __construct(
        private GoogleClientService $google
    ) {}

    public function handle(string $token): User
    {
        $profile = $this->google->getUserProfile($token);

        return User::firstOrCreate(
            ['email' => $profile['email']],
            ['name'  => $profile['name']],
        );
    }
}
```

---

## Ringkasan Struktur Folder

```
app/
├── Actions/          # Mutations: Create, Update, Delete, Custom Business Logic
│   └── Users/
│       ├── CreateUserAction.php
│       ├── UpdateUserAction.php
│       └── DeleteUserAction.php
├── Models/
│   ├── Builders/     # Custom Eloquent Builders (dipakai saat scope sudah banyak)
│   │   └── UserBuilder.php
│   └── User.php      # Scopes sederhana tetap di sini di fase awal
├── Services/         # External integrations: API clients, SDK wrappers
│   └── GoogleClientService.php
├── Filament/         # Filament Resources, Pages, Panels (UI layer saja)
└── Http/
    ├── Controllers/  # Thin controllers: validasi → Action → response
    └── Requests/     # Form Request: validasi & authorization
```

---

## Alur Request Lengkap

Berikut alur request yang ideal, dari user klik tombol hingga data tersimpan.

### Di Admin Panel (Filament)

```
User klik "Simpan" di Filament Page
  → Filament Action (UI trigger)
  → Form Request / Policy (authorization)
  → Action::handle() (business logic)
  → Model (persistence)
  → Response (notifikasi Filament)
```

### Di API (Mobile/Frontend)

```
Client kirim PATCH /api/v1/users/{user}
  → Middleware (auth, throttle)
  → Form Request (validasi + authorization)
  → Action::handle() (business logic, sama dengan yang dipakai Filament)
  → Model (persistence)
  → JsonResource (response)
```

Kunci pentingnya: **Action yang sama dipakai oleh Filament Page dan API Controller**. Tidak ada duplikasi business logic.

---

## Tabel Keputusan Cepat

| Skenario | Solusi |
| :--- | :--- |
| Buat/ubah/hapus data | Action class di `app/Actions/` |
| Filter/search data (sederhana) | `#[Scope]` di Model |
| Filter/search data (kompleks / banyak scope) | Custom Builder di `app/Models/Builders/` |
| Integrasi API pihak ketiga | Service class di `app/Services/` |
| Tombol, modal, konfirmasi di Filament | Filament Action (bukan Action Pattern) |
| Validasi & authorization HTTP request | Form Request di `app/Http/Requests/` |

---

## Bacaan Lanjutan

- [02-action-pattern.md](./02-action-pattern.md) — Detail Action Pattern dan command `make:action`
- [03-query-pattern.md](./03-query-pattern.md) — Scopes vs Custom Eloquent Builders & kenapa bukan Repository Pattern
- [04-policy-and-action-integration.md](./04-policy-and-action-integration.md) — Cara Policy berinteraksi dengan Action
- [14-api.md](./14-api.md) — Struktur API dan penggunaan Action dari Controller
