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

Jika `data` atau `errors` tidak ada, key tersebut memang sengaja **tidak dikirim**.

Contoh sukses:

```json
{
    "message": "User retrieved successfully.",
    "data": {
        "user": {
            "id": "2f4f4ad8-5320-4f66-8bc4-e8f5d1b6fcb0",
            "name": "Kaesa",
            "email": "kaesa@example.com",
            "avatar": null,
            "created_at": "2026-03-27T10:15:30+00:00",
            "updated_at": "2026-03-27T10:15:30+00:00"
        }
    }
}
```

Contoh validation error:

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": [
            "The email field is required."
        ]
    }
}
```

## Aturan Typing untuk Flutter

Supaya aman dipakai di Dart yang ketat terhadap tipe data, API ini mengikuti aturan berikut:

- UUID tetap string
- integer tetap number, bukan string
- boolean tetap boolean
- field nullable tetap `null`
- timestamp dikirim sebagai string ISO-8601
- response list dan detail tidak memakai serialisasi model mentah

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
        "user": {
            "id": "2f4f4ad8-5320-4f66-8bc4-e8f5d1b6fcb0",
            "name": "Flutter User",
            "email": "flutter@example.com",
            "avatar": null,
            "created_at": "2026-03-27T10:15:30+00:00",
            "updated_at": "2026-03-27T10:15:30+00:00"
        }
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
    "data": {
        "users": [
            {
                "id": "2f4f4ad8-5320-4f66-8bc4-e8f5d1b6fcb0",
                "name": "Kaesa",
                "email": "kaesa@example.com",
                "avatar": null,
                "created_at": "2026-03-27T10:15:30+00:00",
                "updated_at": "2026-03-27T10:15:30+00:00"
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
            "has_more_pages": true
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
    "data": {
        "users": [
            {
                "id": "2f4f4ad8-5320-4f66-8bc4-e8f5d1b6fcb0",
                "name": "Kaesa",
                "email": "kaesa@example.com",
                "avatar": null,
                "created_at": "2026-03-27T10:15:30+00:00",
                "updated_at": "2026-03-27T10:15:30+00:00"
            }
        ],
        "meta": {
            "pagination_type": "cursor",
            "per_page": 10,
            "next_cursor": "eyJpZCI6IjJmNGY0YWQ4LTUzMjAtNGY2Ni04YmM0LWU4ZjVkMWI2ZmNiMCIsIl9wb2ludHNUb05leHRJdGVtcyI6dHJ1ZX0",
            "prev_cursor": null,
            "has_more_pages": true
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
- baca `data.meta.pagination_type` agar parsing meta jelas

## HTTP Status Code yang Dipakai

Starter kit ini memakai status code REST yang umum:

- `200 OK` untuk read, update, logout
- `201 Created` untuk create dan register
- `401 Unauthorized` untuk belum login atau credential salah
- `403 Forbidden` untuk token ability/policy tidak mengizinkan
- `404 Not Found` untuk resource atau route yang tidak ada
- `422 Unprocessable Entity` untuk validasi gagal

## Catatan Arsitektur

Supaya struktur project tetap bersih:

- **API controller** menangani orchestration dan cek ability token
- **Form Request** menangani validasi dan authorization yang reusable
- **Action** menangani logika bisnis
- **API Resource** menangani transformasi output

Jadi, action tidak bertugas mengecek ability token.

## Referensi

- Laravel Sanctum: [https://laravel.com/docs/12.x/sanctum](https://laravel.com/docs/12.x/sanctum)
- Laravel API Resources: [https://laravel.com/docs/12.x/eloquent-resources](https://laravel.com/docs/12.x/eloquent-resources)
