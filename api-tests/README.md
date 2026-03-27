# API Testing Strategy

Strategi pengetesan API di proyek ini menggunakan **Bruno API Client**. 

## Rationale Folder Structure
File tes diletakkan di `api-tests/bruno` untuk memisahkan alat pengujian dari kode sumber aplikasi. Struktur ini memungkinkan kita menambahkan alat lain (seperti K6 untuk load testing atau Playwright untuk E2E) di bawah folder `api-tests` tanpa mengotori root directory.

## Cara Menjalankan Tes (CI/CD)
Meskipun saat ini diutamakan penggunaan via GUI (VS Code Extension), Bruno dapat dijalankan di terminal/CI menggunakan `@usebruno/cli`.

### Instalasi CLI
```bash
npm install -g @usebruno/cli
```

### Menjalankan Koleksi
```bash
# Dari folder api-tests/bruno/
bru run --env local --env-var email=superadmin@example.com --env-var password=password
```

## Tips Keamanan
- File di `api-tests/bruno/environments/` yang mengandung rahasia (seperti password asli) sebaiknya tidak di-commit ke Git.
- Gunakan variabel `{{variable_name}}` untuk menjaga fleksibilitas antar environment.
- Bruno menyimpan data secara lokal, sehingga tidak ada data API Anda yang terkirim ke cloud pihak ketiga.
