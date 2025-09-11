# API Sanctum (Default)

> **Catatan:** Starter kit ini menggunakan **Laravel Sanctum** untuk autentikasi API secara default.
> Jika Anda ingin menggunakan **JWT** silahkan cari tahu sendiri, atau tunggu saya buat dokumentasi migrasi.

## 1. Autentikasi API dengan Sanctum

Laravel Sanctum menyediakan sistem **token-based authentication** yang ringan dan aman. Cara kerja dasarnya adalah:

1. **Client** mengirimkan request login dengan `email` dan `password`.
2. Server melakukan validasi, lalu mengembalikan **personal access token**.
3. Client menyertakan token ini di header setiap request API:

```http
Authorization: Bearer {token}
```

### Instalasi dan Referensi

-   Dokumentasi Laravel Sanctum: [https://laravel.com/docs/12.x/sanctum](https://laravel.com/docs/12.x/sanctum)

---

## 2. Struktur Response JSON

Semua endpoint API pada starter kit ini mengembalikan **response JSON** dengan format standar sebagai berikut:

```json
{
    "message": "Deskripsi singkat response",
    "data": {
        // objek data spesifik endpoint
    },
    "errors": null
}
```

-   **success**: boolean, menandakan apakah request berhasil.
-   **message**: string, deskripsi singkat.
-   **data**: objek berisi data endpoint (bisa kosong `{}` atau array `[]`).
-   **errors**: objek berisi error jika ada; `null` jika tidak ada.

Contoh response sukses login:

```json
{
    "message": "Login berhasil",
    "data": {
        "user": {
            "id": 1,
            "name": "Kaesa",
            "email": "kaesa@example.com"
        },
        "token": "1|ABCD1234TOKENEXAMPLE"
    },
    "errors": null
}
```

Contoh response gagal login:

```json
{
    "message": "Email atau password salah",
    "data": {},
    "errors": {
        "email": ["Email tidak ditemukan"]
    }
}
```

---

## 3. Cara Menggunakan Sanctum

1. **Login / Mendapatkan Token**

```http
POST /api/login
Content-Type: application/json

{
  "email": "kaesa@example.com",
  "password": "password123"
}
```

Response akan mengembalikan **token** yang harus digunakan di header `Authorization`.

2. **Mengakses Endpoint yang Dilindungi**

```http
GET /api/user
Authorization: Bearer {token}
```

3. **Logout / Mencabut Token**

```http
POST /api/logout
Authorization: Bearer {token}
```

Token yang digunakan akan dicabut, dan client harus login ulang untuk mendapatkan token baru.

---

## Migrasi ke JWTs (Masih Perencanaan)

Jika ingin mengganti Sanctum dengan JWT, nanti yaa saya buat dokumentasinya.

-   PHP Libraries JWT: [https://www.jwt.io/libraries?programming_language=php](https://www.jwt.io/libraries?programming_language=php)
-   [lcobucci/jwt](https://github.com/lcobucci/jwt)
