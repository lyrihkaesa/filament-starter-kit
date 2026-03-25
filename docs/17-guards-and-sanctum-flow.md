# 🛡️ Guard & Alur Sanctum

Panduan ini menjelaskan cara kerja **Laravel Guard** dan bagaimana Anda membangun alur otorisasi modern menggunakan Sanctum token, policy, dan validasi atribut.

## 🛡️ Memahami Guard

Guard (Penjaga) menentukan bagaimana aplikasi Anda mengenali siapa user yang sedang mengakses.

### `web` Guard vs `api` (Sanctum) Guard

| Fitur | `web` Guard | `api` (Sanctum) Guard |
|:--- |:--- |:--- |
| **Autentikasi** | Session + Cookies | API Tokens / SPA Cookies |
| **Kegunaan** | Web Tradisional / Filament | Apps Mobile / Integrasi Luar |
| **Sifat** | Berbasis Sesi | Stateless (Berbasis Token) |

> [!TIP]
> **Bolehkah pakai `web` guard untuk API?**
> Bisa, jika API tersebut digunakan untuk SPA (Single Page Application) di domain yang sama. Namun, untuk aplikasi mobile atau client luar, Anda **wajib** menggunakan sistem token Sanctum.

## 🔄 Alur Otorisasi Modern (3 Lapis)

Ini adalah standar industri untuk memproses permintaan (request) di API Laravel secara aman.

### 1. Sanctum Ability (Cek Scope Token)
Pertama, cek apakah **Token API** tersebut punya "kemampuan" untuk melakukan aksi tersebut. Ini adalah gerbang pertama.
```php
// Token mungkin hanya punya akses ['user:read-only']
if ($user->tokenCan('user:write')) {
    // Lanjut ke lapisan berikutnya
}
```

### 2. Policy / Gate (Cek Izin User)
Kedua, cek apakah **User** yang memiliki token tersebut memang punya Izin permanen untuk melakukan aksi itu.
```php
// User harus punya permission 'create_user'
if (Gate::allows('create', User::class)) {
    // Lanjut ke lapisan berikutnya
}
```

### 3. Validasi Atribut (Cek Isi Data)
Ketiga, cek apakah **Isi Data** yang dikirimkan sesuai dengan aturan bisnis yang berlaku.
```php
// Misal: Boleh buat user, tapi tidak boleh memberi role 'admin'
if ($request->role === 'admin' && ! $user->hasRole('super_admin')) {
    abort(403, 'Anda tidak berhak membuat user dengan role admin.');
}
```

## 🚀 Contoh Praktis di Controller

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
    // CreateUserAction::execute($validated);
}
```

> [!NOTE]
> Strategi "Defense-in-Depth" ini memastikan aplikasi Anda aman di level infrastruktur (Token), level user (Permission), dan level data (Atribut).
