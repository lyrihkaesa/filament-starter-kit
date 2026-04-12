# API Sanctum (Mobile Ready)

> **Catatan:** Starter kit ini memakai **Laravel Sanctum** untuk autentikasi API mobile dan integrasi client luar. Fokus implementasinya adalah sederhana, typed, dan enak dipakai oleh frontend seperti Flutter.

## Kenapa Sanctum

Saya sengaja memilih Sanctum sebagai default karena:

- solusi resmi Laravel
- lebih sederhana dirawat dibanding JWT
- cocok untuk mobile app yang memakai bearer token
- logout cukup dengan revoke token saat ini
- mendukung token ability yang jelas

Untuk kebutuhan starter kit, ini biasanya lebih waras daripada langsung membawa kompleksitas refresh token dan token lifecycle ala JWT.

## Base URL dan Versi API

API menggunakan **URL versioning** dan saat ini semua endpoint public ada di prefix:

```text
/api/v1
```

Contoh base URL lokal jika memakai Herd:

```text
http://filament-starter-kit.test/api/v1
```

## Format Response JSON

Starter kit ini memakai kontrak response yang konsisten dan ramah untuk Flutter:

- `message` selalu string
- `data` hanya muncul jika ada payload sukses
- `errors` hanya muncul jika ada error
- `meta` hanya muncul jika endpoint memang mengembalikan metadata tambahan, misalnya pagination

Jika `data`, `errors`, atau `meta` tidak ada, key tersebut memang sengaja **tidak dikirim**.

Untuk success response, implementasi sekarang mengikuti gaya Laravel Resource response. Jadi controller langsung me-return `UserResource` atau `UserCollection`, lalu menambahkan `message` lewat `->additional(...)`.

Aturan kontraknya sekarang sederhana:

- `data` = object untuk endpoint detail atau single resource
- `data` = array untuk endpoint collection/list
- `meta` = object tambahan, misalnya pagination

## Field `can` untuk Frontend

Agar Flutter bisa menyembunyikan tombol seperti gaya Filament atau Inertia, API mengirim hasil final authorization dalam bentuk boolean `can`.

Prinsipnya:

- frontend tidak perlu menebak dari role
- frontend cukup baca boolean `can`
- keputusan akhir tetap datang dari backend
- `can` dihitung dari kombinasi **token ability Sanctum** dan **policy**

Contoh untuk single item:

```json
{
    "message": "User retrieved successfully.",
    "data": {
        "id": "2f4f4ad8-5320-4f66-8bc4-e8f5d1b6fcb0",
        "name": "Kaesa",
        "email": "kaesa@example.com",
        "avatar": null,
        "created_at": "2026-03-27T10:15:30+00:00",
        "updated_at": "2026-03-27T10:15:30+00:00",
        "can": {
            "view": true,
            "update": false,
            "delete": false
        }
    }
}
```

Contoh untuk list:

```json
{
    "message": "Users retrieved successfully.",
    "data": [
        {
            "id": "2f4f4ad8-5320-4f66-8bc4-e8f5d1b6fcb0",
            "name": "Kaesa",
            "email": "kaesa@example.com",
            "avatar": null,
            "created_at": "2026-03-27T10:15:30+00:00",
            "updated_at": "2026-03-27T10:15:30+00:00",
            "can": {
                "view": true,
                "update": false,
                "delete": false
            }
        }
    ],
    "meta": {
        "pagination_type": "page",
        "current_page": 1,
        "per_page": 10,
        "total": 25,
        "last_page": 3,
        "from": 1,
        "to": 10,
        "has_more_pages": true,
        "can": {
            "create": true
        }
    }
}
```

Dengan ini, frontend tinggal melakukan hal seperti:

- tampilkan tombol edit jika `data.can.update == true`
- tampilkan tombol delete jika `data.can.delete == true`
- tampilkan tombol create di halaman list jika `meta.can.create == true`

## Aturan Typing untuk Flutter

Supaya aman dipakai di Dart yang ketat terhadap tipe data, API ini mengikuti aturan berikut:

- UUID tetap string
- integer tetap number, bukan string
- boolean tetap boolean
- field nullable tetap `null`
- timestamp dikirim sebagai string ISO-8601
- response list dan detail tidak memakai serialisasi model mentah
- semua nilai `can.*` selalu boolean

Artinya, frontend tidak perlu menebak apakah `per_page` itu number atau string.

## Authentication Flow

Alur dasarnya:

1. client login atau register
2. API mengembalikan Sanctum token
3. client menyimpan token
4. request berikutnya mengirim bearer token
5. logout akan mencabut token yang sedang dipakai

Header yang dipakai:

```http
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

## Endpoint Auth

### `POST /api/v1/register`

Body:

```json
{
    "name": "Flutter User",
    "email": "flutter@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "device_name": "pixel-8"
}
```

Contoh response:

```json
{
    "message": "User registered successfully.",
    "data": {
        "user": {
            "id": "2f4f4ad8-5320-4f66-8bc4-e8f5d1b6fcb0",
            "name": "Flutter User",
            "email": "flutter@example.com",
            "avatar": null,
            "created_at": "2026-03-27T10:15:30+00:00",
            "updated_at": "2026-03-27T10:15:30+00:00"
        },
        "token": "1|plainTextTokenExample",
        "token_type": "Bearer",
        "abilities": [
            "profile:read"
        ]
    }
}
```

### `POST /api/v1/login`

Body:

```json
{
    "email": "flutter@example.com",
    "password": "password123",
    "device_name": "pixel-8"
}
```

Contoh response:

```json
{
    "message": "Login successful.",
    "data": {
        "user": {
            "id": "2f4f4ad8-5320-4f66-8bc4-e8f5d1b6fcb0",
            "name": "Flutter User",
            "email": "flutter@example.com",
            "avatar": null,
            "created_at": "2026-03-27T10:15:30+00:00",
            "updated_at": "2026-03-27T10:15:30+00:00"
        },
        "token": "2|plainTextTokenExample",
        "token_type": "Bearer",
        "abilities": [
            "profile:read",
            "users:read"
        ]
    }
}
```

### `GET /api/v1/me`

Butuh ability token `profile:read`.

Contoh response:

```json
{
    "message": "Authenticated user retrieved successfully.",
    "data": {
        "id": "2f4f4ad8-5320-4f66-8bc4-e8f5d1b6fcb0",
        "name": "Flutter User",
        "email": "flutter@example.com",
        "avatar": null,
        "created_at": "2026-03-27T10:15:30+00:00",
        "updated_at": "2026-03-27T10:15:30+00:00"
    }
}
```

### `POST /api/v1/logout`

Contoh response:

```json
{
    "message": "Logout successful."
}
```

## Endpoint User

Endpoint user memakai REST style:

- `GET /api/v1/users`
- `POST /api/v1/users`
- `GET /api/v1/users/{user}`
- `PUT /api/v1/users/{user}`
- `PATCH /api/v1/users/{user}`
- `DELETE /api/v1/users/{user}`

Semua endpoint di atas:

- butuh bearer token Sanctum
- tetap melewati policy/gate
- ability token dicek di **API controller**

Untuk endpoint detail atau mutation user, `data` langsung berisi object user.
Untuk endpoint list user, `data` langsung berisi array user dan metadata pagination ada di top-level `meta`.

## Dual Pagination untuk Mobile

Endpoint list user mendukung dua mode:

- default page pagination
- optional cursor pagination

Query parameter yang dipakai:

- `pagination=page|cursor`
- `per_page`
- `page` hanya untuk page mode
- `cursor` hanya untuk cursor mode

Jika `pagination` tidak dikirim, default-nya adalah:

```text
pagination=page
```

### Page Pagination

Request:

```text
GET /api/v1/users?per_page=10
```

Contoh response:

```json
{
    "message": "Users retrieved successfully.",
    "data": [
        {
            "id": "2f4f4ad8-5320-4f66-8bc4-e8f5d1b6fcb0",
            "name": "Kaesa",
            "email": "kaesa@example.com",
            "avatar": null,
            "created_at": "2026-03-27T10:15:30+00:00",
            "updated_at": "2026-03-27T10:15:30+00:00",
            "can": {
                "view": true,
                "update": false,
                "delete": false
            }
        }
    ],
    "meta": {
        "pagination_type": "page",
        "current_page": 1,
        "per_page": 10,
        "total": 25,
        "last_page": 3,
        "from": 1,
        "to": 10,
        "has_more_pages": true,
        "can": {
            "create": true
        }
    }
}
```

### Cursor Pagination

Request:

```text
GET /api/v1/users?pagination=cursor&per_page=10
```

Contoh response:

```json
{
    "message": "Users retrieved successfully.",
    "data": [
        {
            "id": "2f4f4ad8-5320-4f66-8bc4-e8f5d1b6fcb0",
            "name": "Kaesa",
            "email": "kaesa@example.com",
            "avatar": null,
            "created_at": "2026-03-27T10:15:30+00:00",
            "updated_at": "2026-03-27T10:15:30+00:00",
            "can": {
                "view": true,
                "update": false,
                "delete": false
            }
        }
    ],
    "meta": {
        "pagination_type": "cursor",
        "per_page": 10,
        "next_cursor": "eyJpZCI6IjJmNGY0YWQ4LTUzMjAtNGY2Ni04YmM0LWU4ZjVkMWI2ZmNiMCIsIl9wb2ludHNUb05leHRJdGVtcyI6dHJ1ZX0",
        "prev_cursor": null,
        "has_more_pages": true,
        "can": {
            "create": false
        }
    }
}
```

### Kapan Pilih Page vs Cursor

- pilih **page pagination** jika UI butuh nomor halaman
- pilih **cursor pagination** jika UI mobile lebih fokus ke infinite scroll

Karena keduanya memakai endpoint yang sama, frontend mobile cukup mengganti query parameter tanpa mengubah model item user.

## Cara Coba dengan cURL

### Register

```bash
curl --request POST \
  --url http://filament-starter-kit.test/api/v1/register \
  --header 'Accept: application/json' \
  --header 'Content-Type: application/json' \
  --data '{
    "name": "Flutter User",
    "email": "flutter@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "device_name": "pixel-8"
  }'
```

### Login

```bash
curl --request POST \
  --url http://filament-starter-kit.test/api/v1/login \
  --header 'Accept: application/json' \
  --header 'Content-Type: application/json' \
  --data '{
    "email": "flutter@example.com",
    "password": "password123",
    "device_name": "pixel-8"
  }'
```

### Ambil profile user saat ini

```bash
curl --request GET \
  --url http://filament-starter-kit.test/api/v1/me \
  --header 'Accept: application/json' \
  --header 'Authorization: Bearer YOUR_TOKEN'
```

### List user dengan pagination default

```bash
curl --request GET \
  --url 'http://filament-starter-kit.test/api/v1/users?per_page=5' \
  --header 'Accept: application/json' \
  --header 'Authorization: Bearer YOUR_TOKEN'
```

### List user dengan cursor pagination

```bash
curl --request GET \
  --url 'http://filament-starter-kit.test/api/v1/users?pagination=cursor&per_page=5' \
  --header 'Accept: application/json' \
  --header 'Authorization: Bearer YOUR_TOKEN'
```

## Cara Pakai dari Flutter

Minimal, frontend cukup menyimpan:

- `token`
- `token_type`
- `abilities`

Lalu kirim header:

```text
Authorization: Bearer {token}
Accept: application/json
```

Untuk list users:

- pakai tanpa query `pagination` jika ingin mode default
- pakai `pagination=cursor` jika screen memakai infinite scroll
- baca item list langsung dari `data`
- baca metadata pagination dari `meta`
- baca `meta.pagination_type` agar parsing meta jelas
- baca `meta.can.create` untuk menentukan apakah tombol create ditampilkan
- baca `data[index].can.update` atau `data[index].can.delete` untuk action per row

Untuk detail user:

- baca `data.can.update` untuk tombol edit
- baca `data.can.delete` untuk tombol delete

## Catatan Error di Local vs Production

Jika Anda mencoba endpoint dengan method yang salah saat **local** dan `APP_DEBUG=true`, Laravel masih bisa menampilkan detail exception seperti:

- `exception`
- `file`
- `line`
- `trace`

Ini masih berguna untuk debugging lokal.

Tetapi untuk **production**, pastikan `APP_DEBUG=false`. Dalam kondisi itu, detail sensitif seperti stack trace tidak boleh diekspos ke client.

Artinya, kalau Anda melihat trace saat development lokal, itu masih normal. Yang penting adalah environment production tidak berjalan dengan debug aktif.

## HTTP Status Code yang Dipakai

Starter kit ini memakai status code REST yang umum:

- `200 OK` untuk read, update, logout
- `201 Created` untuk create dan register
- `401 Unauthorized` untuk belum login atau credential salah
- `403 Forbidden` untuk token ability/policy tidak mengizinkan
- `404 Not Found` untuk resource atau route yang tidak ada
- `405 Method Not Allowed` untuk method yang salah pada endpoint yang benar
- `422 Unprocessable Entity` untuk validasi gagal

## Catatan Arsitektur

Supaya struktur project tetap bersih:

- **API controller** menangani orchestration dan cek ability token
- **Form Request** menangani validasi dan authorization yang reusable
- **Action** menangani logika bisnis
- **API Resource** menangani transformasi output
- field `can` dihitung di controller dari token ability + policy, lalu dikirim ke resource

Jadi, action tidak bertugas mengecek ability token.

## Automated API Testing (Bruno)

Untuk memudahkan pengembangan dan integrasi, starter kit ini menyertakan koleksi **Bruno API Client** yang terletak di folder `api-tests/bruno`.

### Fitur Utama:
- **Git-Friendly**: Disimpan dalam format `.bru` yang mudah di-tracking.
- **Automated Auth**: Script login otomatis menyimpan token Sanctum untuk dipakai request berikutnya (`me`, `logout`, dsb).
- **Environment Support**: Mendukung environment `local` dengan variabel `{{base_url}}`.

### Cara Menjalankan:
1. Pakai ekstensi **Bruno** di VS Code.
2. Buka folder `api-tests/bruno`.
3. Pilih environment `local`.
4. Jalankan alur `Login` -> `Get Profile`.

Detail lebih lanjut ada di [api-tests/README.md](https://github.com/lyrihkaesa/filament-starter-kit/blob/main/api-tests/README.md).

## Update 11 April 2026: Post API v1 (Action-Based)

Endpoint baru:

- `GET /api/v1/posts`
- `POST /api/v1/posts`
- `GET /api/v1/posts/{post}`
- `PATCH /api/v1/posts/{post}`
- `DELETE /api/v1/posts/{post}`

Arsitektur:

- Controller API Post memanggil Action domain yang sama dengan Filament:
  - `CreatePostAction`
  - `UpdatePostAction`
  - `DeletePostAction`
- Sinkronisasi `curator_media_usages` berjalan dari Action domain (tanpa observer).

Token abilities:

- `posts:read`
- `posts:create`
- `posts:update`
- `posts:delete`

## Referensi

- Laravel Sanctum: [https://laravel.com/docs/12.x/sanctum](https://laravel.com/docs/12.x/sanctum)
- Laravel API Resources: [https://laravel.com/docs/12.x/eloquent-resources](https://laravel.com/docs/12.x/eloquent-resources)
