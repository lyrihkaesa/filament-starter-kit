# API JWT

> [!CAUTION]
> Dokumentasi ini belum final, karena masih dalam perencanaan, belum di uji coba oleh Kami.  
> Dokumentasi dibawah ini hanya catatan untuk Kaesa nanti pengembangan support JWT pada starter kit.

> [!NOTE]
> Dokumen ini menjelaskan cara migrasi dari **Sanctum** ke **JWT murni** menggunakan **lcobucci/jwt** dan guard Laravel `jwt`. Termasuk implementasi **abilities** seperti token di Sanctum.

---

## 1. Instalasi lcobucci/jwt

```bash
composer require lcobucci/jwt
```

---

## 2. Membuat JWT Helper

```php
// app/Helpers/JwtHelper.php
namespace App\Helpers;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Plain;

class JwtHelper
{
    protected static function config()
    {
        return Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText(env('JWT_SECRET', 'secret'))
        );
    }

    public static function generateToken($userId, array $abilities = [], $expiresInMinutes = 60)
    {
        $config = self::config();
        $now = new \DateTimeImmutable();
        $token = $config->builder()
            ->issuedAt($now)
            ->expiresAt($now->modify("+{$expiresInMinutes} minutes"))
            ->withClaim('uid', $userId)
            ->withClaim('abilities', $abilities)
            ->getToken($config->signer(), $config->signingKey());
        return $token->toString();
    }

    public static function parseToken(string $token): Plain
    {
        return self::config()->parser()->parse($token);
    }

    public static function validateToken(string $token): bool
    {
        $config = self::config();
        try {
            $parsed = self::parseToken($token);
            return $config->validator()->validate($parsed, ...[
                new \Lcobucci\JWT\Validation\Constraint\SignedWith($config->signer(), $config->signingKey()),
                new \Lcobucci\JWT\Validation\Constraint\ValidAt(new \Lcobucci\Clock\SystemClock(new \DateTimeZone('UTC')))
            ]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function getClaims(string $token): array
    {
        return self::parseToken($token)->claims()->all();
    }
}
```

---

## 3. Membuat Guard `jwt`

```php
// app/Guards/JwtGuard.php
namespace App\Guards;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use App\Models\User;
use App\Helpers\JwtHelper;

class JwtGuard implements Guard
{
    protected $user;
    protected $request;
    protected $provider;

    public function __construct(UserProvider $provider, Request $request)
    {
        $this->provider = $provider;
        $this->request = $request;
    }

    public function user()
    {
        if ($this->user) return $this->user;

        $token = $this->getTokenFromHeader();
        if (!$token || !JwtHelper::validateToken($token)) return null;

        $claims = JwtHelper::getClaims($token);
        return $this->user = User::find($claims['uid']);
    }

    public function check() { return $this->user() !== null; }
    public function guest() { return !$this->check(); }

    protected function getTokenFromHeader()
    {
        $header = $this->request->header('Authorization', '');
        return str_starts_with($header, 'Bearer ') ? substr($header, 7) : null;
    }

    public function abilities(): array
    {
        $token = $this->getTokenFromHeader();
        if (!$token) return [];
        $claims = JwtHelper::getClaims($token);
        return $claims['abilities'] ?? [];
    }

    public function hasAbility(string $ability): bool
    {
        return in_array($ability, $this->abilities());
    }
}
```

---

## 4. Registrasi Guard di `AuthServiceProvider`

```php
use Illuminate\Support\Facades\Auth;
use App\Guards\JwtGuard;

Auth::extend('jwt', function ($app, $name, array $config) {
    return new JwtGuard(Auth::createUserProvider($config['provider']), $app['request']);
});
```

Ubah `config/auth.php`:

```php
'guards' => [
    'api' => [
        'driver' => 'jwt', // gunakan guard jwt
        'provider' => 'users',
    ],
],
```

Sekarang route bisa pakai:

```php
Route::middleware(['auth:jwt'])->get('/user', function() {
    return auth()->user();
});
```

---

## 5. Login API dengan Ability

```php
public function login(Request $request)
{
    $user = User::where('email', $request->email)->first();
    if (!$user || !\Hash::check($request->password, $user->password)) {
        return response()->json([
            'success' => false,
            'message' => 'Email atau password salah',
            'data' => new \stdClass(),
            'errors' => ['email' => ['Email tidak ditemukan atau password salah']]
        ], 401);
    }

    $abilities = ['read-posts', 'edit-profile']; // contoh ability

    $token = JwtHelper::generateToken($user->id, $abilities, 120);

    return response()->json([
        'success' => true,
        'message' => 'Login berhasil',
        'data' => [
            'user' => $user,
            'token' => $token
        ],
        'errors' => null
    ]);
}
```

---

## 6. Middleware untuk Ability

```php
// app/Http/Middleware/CheckAbility.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckAbility
{
    public function handle($request, Closure $next, string $ability)
    {
        $guard = Auth::guard('jwt');
        if (!$guard->check() || !$guard->hasAbility($ability)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses',
                'data' => new \stdClass(),
                'errors' => null
            ], 403);
        }
        return $next($request);
    }
}
```

Gunakan di route:

```php
Route::middleware(['auth:jwt', 'ability:read-posts'])->get('/posts', [PostController::class, 'index']);
```

---

## 7. Logout

JWT bersifat **stateless**, jadi logout cukup di client dengan menghapus token. Jika perlu revokasi, simpan token di blacklist dan cek di `JwtGuard::validateToken`.
