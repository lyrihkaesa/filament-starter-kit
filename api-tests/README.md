# Living API Documentation & Testing (Bruno)

Folder ini berisi koleksi **[Bruno API Client](https://www.usebruno.com/)** sebagai **Living & Executable API Documentation** sekaligus pengujian kontrak API starter kit ini.

Sebagai alternatif modern dan *Git-friendly* untuk Postman, Bruno menyimpan seluruh request, parameter, skrip autentikasi, dan assertion dalam file teks biasa (`.bru`) yang berada langsung di dalam repositori proyek.

---

## Scramble vs Bruno

- **Dedoc Scramble (`/docs/api`)**: Dokumentasi berbasis browser (Swagger/OpenAPI UI) yang ter-generate otomatis dari kode Laravel. Cocok untuk preview cepat atau dibagikan ke pihak luar.
- **Bruno (`api-tests/bruno`)**: Dokumentasi *executable* langsung di IDE/aplikasi lokal. Developer mobile (Flutter) atau frontend dapat langsung mencoba (*run*), mengubah payload, dan melihat bentuk riil respons JSON tanpa copy-paste token manual.

---

## Struktur Koleksi API v1

Koleksi terletak di `api-tests/bruno/v1` dan terbagi ke beberapa modul:

| Modul | Deskripsi |
| :--- | :--- |
| **`01-Auth`** | Alur registrasi, login (auto bearer token), cek profile (`/me`), dan update profile |
| **`02-Users`** | Manajemen CRUD User (List dengan paginasi, Create, Get detail, Update, Delete) |
| **`03-Uploads`** | Alur unggah berkas mobile/API (Prepare upload, Upload file lokal, Mark uploaded, Get metadata) |
| **`04-Posts`** | Manajemen CRUD Post (List artikel, Create, Get detail, Update, Delete) |
| **`99-Cleanup`** | Logout dan pencabutan (*revoke*) token Sanctum aktif |

---

## Otomatisasi Autentikasi (Token Chaining)

Setiap kali Anda menjalankan request `01-Auth/02-Login`, skrip post-response akan otomatis mengambil token Sanctum dan menyimpannya ke variabel `{{access_token}}`:

```javascript
if (res.getStatus() === 200) {
  const body = res.getBody();
  const token = body.data ? body.data.token : body.token;
  if (token) {
    bru.setVar("access_token", token);
    bru.setEnvVar("access_token", token);
  }
}
```

Semua request di folder lain dikonfigurasi menggunakan `auth: inherit` sehingga otomatis menggunakan token terbaru tersebut tanpa perlu mengisi Authorization header secara manual.

---

## Cara Menggunakan

### 1. Via Visual Studio Code (Rekomendasi)
1. Pasang ekstensi **Bruno** resmi di VS Code.
2. Buka explorer Bruno di sidebar VS Code.
3. Buka folder `api-tests/bruno`.
4. Pilih environment **`local`**.
5. Jalankan `01-Auth -> 02-Login`.
6. Jalankan request lainnya sesuai modul yang ingin diuji.

### 2. Via Aplikasi Desktop Bruno
Buka aplikasi Bruno, pilih **Open Collection**, lalu arahkan ke folder `api-tests/bruno`.

### 3. Via Terminal / CI/CD (`@usebruno/cli`)
Anda dapat menjalankan seluruh skenario pengujian API dari terminal atau pipeline CI/CD:

```bash
# Instalasi CLI
npm install -g @usebruno/cli

# Menjalankan seluruh koleksi secara headless
cd api-tests/bruno
bru run --env local --env-var email=superadmin@example.com --env-var password=password
```

---

## Keamanan & Privasi
- **Zero Cloud Leak**: Data API, URL internal, dan token Anda tersimpan 100% lokal di komputer Anda, tidak pernah dikirim ke cloud pihak ketiga.
- **Environment Secrets**: File rahasia di `environments/` tidak boleh menyimpan password atau kredensial produksi. Gunakan variabel CLI `--env-var` untuk kredensial sensitif saat dijalankan di CI/CD.
