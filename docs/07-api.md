# API Sanctum (Default)

> **Catatan:** Starter kit ini menggunakan **Laravel Sanctum** untuk autentikasi API secara default. Saya sengaja tidak memakai JWT sebagai default karena untuk banyak kasus admin panel dan API project Laravel biasa, Sanctum sudah cukup dan jauh lebih sederhana untuk dirawat.

## Kenapa Saya Memilih Sanctum, Bukan JWT

Kalau Anda masih pemula, pertanyaan yang sangat wajar adalah:

> "Kenapa tidak langsung pakai JWT saja?"

Jawaban saya: karena saya tidak ingin starter kit ini memulai dari kompleksitas yang belum tentu dibutuhkan.

## Cara Pikirnya

### Sanctum lebih sederhana untuk kebutuhan umum

Dengan Sanctum, kita bisa memakai token yang disimpan dan dikelola dengan cara yang lebih mudah dipahami. Untuk banyak kebutuhan aplikasi admin, mobile app sederhana, atau integrasi internal, ini sudah sangat cukup.

Kalau user logout, kita tinggal **hapus token Sanctum** yang sedang dipakai.

Itu jauh lebih mudah dijelaskan ke developer baru.

### JWT mendorong pola pikir stateless token

Kalau Anda memilih JWT secara serius, biasanya Anda juga harus siap dengan pola pikir **stateless authentication**.

Masalahnya, begitu masuk ke sana, kompleksitasnya ikut naik:

- perlu memikirkan **access token**
- perlu memikirkan **refresh token**
- perlu memikirkan masa berlaku token
- perlu memikirkan rotasi token
- perlu memikirkan invalidasi token
- logout jadi tidak sesederhana "hapus token dari database"

Untuk project yang memang butuh arsitektur seperti itu, JWT bisa masuk akal. Tetapi untuk starter kit ini, saya sengaja memilih jalur yang lebih simpel dan lebih mudah di-maintain.

## Kenapa Sanctum Cocok untuk Starter Kit Ini

Alasan saya memilih Sanctum:

- solusi resmi dari Laravel
- setup lebih sederhana
- flow logout lebih mudah
- cocok untuk personal access token
- cocok untuk kebutuhan API umum
- lebih mudah dipahami developer pemula

Selain itu, Sanctum juga mendukung pendekatan token yang lebih fleksibel dan "fine-grained", mirip arah pemakaian token modern seperti token personal access ala GitHub.

Artinya, token tidak harus dianggap satu kunci besar untuk semua hal. Kita bisa memikirkan token berdasarkan kemampuan atau kebutuhan tertentu.

## 1. Autentikasi API dengan Sanctum

Laravel Sanctum menyediakan sistem **token-based authentication** yang ringan dan aman. Cara kerja dasarnya adalah:

1. **Client** mengirimkan request login dengan `email` dan `password`
2. Server melakukan validasi lalu mengembalikan **personal access token**
3. Client menyertakan token ini di header setiap request API

```http
Authorization: Bearer {token}
```

### Referensi

- Dokumentasi Laravel Sanctum: [https://laravel.com/docs/12.x/sanctum](https://laravel.com/docs/12.x/sanctum)

## 2. Struktur Response JSON

Semua endpoint API pada starter kit ini sebaiknya mengembalikan **response JSON** dengan format standar berikut:

```json
{
    "message": "Deskripsi singkat response",
    "data": {
        // objek data spesifik endpoint
    },
    "errors": null
}
```

### Penjelasan field

- `message`: deskripsi singkat hasil request
- `data`: objek atau array data utama dari endpoint
- `errors`: detail error jika ada, atau `null` jika tidak ada

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

## 3. Cara Menggunakan Sanctum

### Login / Mendapatkan Token

```http
POST /api/login
Content-Type: application/json

{
  "email": "kaesa@example.com",
  "password": "password123"
}
```

Response akan mengembalikan token yang kemudian disimpan oleh client.

### Mengakses Endpoint yang Dilindungi

```http
GET /api/user
Authorization: Bearer {token}
```

### Logout / Mencabut Token

```http
POST /api/logout
Authorization: Bearer {token}
```

Pada flow Sanctum, logout biasanya cukup berarti:

- ambil token yang sedang dipakai
- hapus token tersebut

Jadi, kita tidak perlu memikirkan mekanisme refresh token seperti pada banyak implementasi JWT.

## Studi Kasus

Bayangkan Anda membuat mobile app sederhana untuk admin operasional.

Alurnya:

1. Admin login dari aplikasi mobile
2. API memverifikasi email dan password
3. API mengembalikan token Sanctum
4. Mobile app memakai token itu untuk request berikutnya
5. Saat logout, token dicabut atau dihapus

Untuk kebutuhan seperti ini, Sanctum sudah sangat cukup.

Kalau Anda memaksa JWT sejak awal, Anda mungkin akan ikut memikirkan:

- access token berumur pendek
- refresh token
- endpoint refresh token
- rotasi token
- revoke strategy

Padahal kebutuhan bisnisnya belum tentu sampai ke sana.

## Sanctum dan Fine-Grained Token

Salah satu hal yang saya suka dari Sanctum adalah kita bisa memikirkan token dengan lebih spesifik.

Misalnya, ke depan Anda ingin membuat token yang hanya boleh:

- membaca data tertentu
- membuat data tertentu
- dipakai untuk integrasi tertentu

Cara pikir seperti ini mirip dengan evolusi personal access token modern yang makin granular, bukan satu token superpower untuk semua kebutuhan.

## Cara Pikir Saya Soal API di Starter Kit

Saya ingin flow API tetap:

- mudah dipahami pemula
- aman secara dasar
- konsisten format response-nya
- mudah dikembangkan ke policy, ability, dan validation layer

Karena itu, untuk starter kit ini saya memilih Sanctum sebagai baseline yang lebih masuk akal.

## Kapan JWT Mungkin Baru Layak Dipertimbangkan

JWT bisa mulai masuk akal jika Anda memang punya kebutuhan yang benar-benar menuntut arsitektur token stateless yang lebih kompleks.

Contohnya:

- banyak external client dengan pola auth tertentu
- arsitektur distributed auth yang memang dirancang untuk itu
- kebutuhan token lifecycle yang lebih kompleks dari sekadar revoke token biasa

Kalau belum ada kebutuhan itu, Sanctum biasanya adalah pilihan yang lebih waras.

## Kesimpulan

Saya tidak memakai JWT sebagai default bukan karena JWT buruk, tetapi karena:

- Sanctum sudah cukup untuk banyak kebutuhan
- flow-nya lebih sederhana
- logout lebih mudah
- developer baru lebih cepat paham
- kita tidak dipaksa masuk ke kompleksitas refresh token vs access token terlalu dini

Untuk starter kit ini, kesederhanaan yang jelas lebih saya utamakan daripada kompleksitas yang "mungkin nanti berguna".
