# Guard & Alur Sanctum

Panduan ini menjelaskan cara kerja **Laravel Guard** dan bagaimana Anda membangun alur otorisasi modern menggunakan Sanctum token, policy, dan validasi atribut.

**Catatan Khusus UUID v7:** Tabel `personal_access_tokens` telah dimodifikasi untuk menggunakan **UUID v7** sebagai primary key. Hal ini dilakukan dengan meng-override model bawaan melalui `App\Models\PersonalAccessToken` dan mendaftarkannya di `AppServiceProvider`.

## Memahami Guard

Guard menentukan bagaimana aplikasi Anda mengenali siapa user yang sedang mengakses.

### `web` Guard vs `api` (Sanctum) Guard

| Fitur | `web` Guard | `api` (Sanctum) Guard |
|:--- |:--- |:--- |
| **Autentikasi** | Session + Cookies | API Tokens / SPA Cookies |
| **Kegunaan** | Web Tradisional / Filament | Apps Mobile / Integrasi Luar |
| **Sifat** | Berbasis Sesi | Stateless (Berbasis Token) |

### Bolehkah pakai `web` guard untuk API?

Bisa, jika API tersebut dipakai untuk SPA di domain yang sama. Namun, untuk aplikasi mobile atau client luar, Anda biasanya akan memakai sistem token Sanctum.

## Alur Otorisasi Modern (3 Lapis)

Ini adalah pendekatan yang saya sukai untuk memproses request API secara aman.

### 1. Sanctum Ability (Cek Scope Token)

Pertama, cek apakah **token API** memang punya kemampuan untuk melakukan aksi tersebut.

```php
// Token mungkin hanya punya akses ['user:read-only']
if ($user->tokenCan('user:write')) {
    // Lanjut ke lapisan berikutnya
}
```

Ini adalah gerbang pertama.

### 2. Policy / Gate (Cek Izin User)

Kedua, cek apakah **user** yang memiliki token itu memang berhak melakukan aksi tersebut.

```php
// User harus punya permission 'create_user'
if (Gate::allows('create', User::class)) {
    // Lanjut ke lapisan berikutnya
}
```

Ini penting karena token valid belum tentu berarti user boleh melakukan semua hal.

### 3. Validasi Atribut (Cek Isi Data)

Ketiga, cek apakah **isi data** yang dikirimkan sesuai dengan aturan bisnis.

```php
// Misal: Boleh buat user, tapi tidak boleh memberi role 'admin'
if ($request->role === 'admin' && ! $user->hasRole('super_admin')) {
    abort(403, 'Anda tidak berhak membuat user dengan role admin.');
}
```

Lapisan ini sering dilupakan pemula. Mereka hanya fokus pada auth dan lupa bahwa data tertentu juga bisa punya batasan khusus.

## Contoh Praktis di Controller

Menggabungkan semua lapisan dalam satu proses:

```php
public function store(Request $request): Response
{
    // 1. Cek Kemampuan Token (Sanctum Ability)
    if (! $request->user()->tokenCan('user:create')) {
        abort(403, 'Token Anda tidak punya izin membuat data.');
    }

    // 2. Cek Izin User (Policy/Gate)
    Gate::authorize('create', User::class);

    // 3. Validasi Atribut & Bisnis
    $validated = $request->validate([
        'email' => 'required|email|unique:users',
        'role' => 'required|string',
    ]);

    if ($validated['role'] === 'admin' && ! $request->user()->isSuperAdmin()) {
        abort(403, 'Akses ditolak untuk pembuatan role admin.');
    }

    // SEMUA OK -> Panggil Action untuk simpan data
    // CreateUserAction::handle($validated);
}
```

Catatan: di starter kit ini pola action yang saya gunakan adalah `handle()`, jadi jika Anda menulis implementasi final-nya, tetap ikuti pola itu.

## Catatan Implementasi di Starter Kit Ini

Pada starter kit ini, saya sengaja membagi tanggung jawab seperti berikut:

- **API controller** mengecek Sanctum token ability seperti `tokenCan('users:read')`
- **Form Request** menangani validasi dan authorization yang bisa dipakai ulang di web maupun API
- **Action** hanya menangani logika bisnis

Artinya, saya **tidak meletakkan authorization ability token di Action**.

Alasan utamanya:

- action tetap reusable
- request class tetap bisa dipakai oleh web controller dan API controller
- token-specific concern tetap tinggal di lapisan API

Dengan pembagian ini, flow API biasanya menjadi:

1. API controller cek ability token Sanctum
2. Form Request cek authorization dan validasi
3. controller memanggil action
4. API Resource membentuk response JSON

## Studi Kasus

Bayangkan ada mobile app untuk supervisor.

Supervisor berhasil login dan punya token valid. Tetapi:

- token-nya hanya punya ability `user:read`
- user tersebut tidak punya permission membuat user
- atau user boleh membuat user biasa, tetapi tidak boleh membuat role `admin`

Kalau Anda hanya mengecek token login, keamanan masih bolong.

Karena itu saya suka pendekatan **defense in depth**:

- token layer
- user permission layer
- business attribute layer

## Kesimpulan

Strategi ini memastikan aplikasi aman di beberapa level sekaligus:

- level infrastruktur token
- level user permission
- level aturan data

Untuk developer pemula, pesan terpenting dari dokumen ini adalah:

> Jangan puas hanya karena request sudah "authenticated". API yang benar-benar aman juga harus tahu **apa** yang boleh dilakukan user, dan **data seperti apa** yang boleh ia kirim.
