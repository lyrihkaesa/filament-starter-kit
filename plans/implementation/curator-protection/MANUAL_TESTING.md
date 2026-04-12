# Manual Testing Guide - Media Protection System

Ikuti langkah-langkah di bawah ini untuk memverifikasi bahwa sistem proteksi media dan kebijakan keamanan berjalan dengan benar di lingkungan lokal atau staging Anda.

---

## 🚀 0. Persiapan Data (Seeding)

Sebelum memulai pengujian, Anda bisa menjalankan seeder khusus yang telah saya buat untuk menyiapkan user post, dan media dengan role yang berbeda:

```bash
php artisan db:seed --class=ManualTestingSeeder
```

**Data yang akan dibuat:**
- **Super Admin**: `superadmin@example.com` (Post & Media)
- **Admin**: `admin@example.com` (Post & Media)
- **Member**: `member@example.com` (Post & Media)
- *Password default untuk semua user di atas adalah: `password`*

---

## 📸 1. Proteksi Penghapusan Media (Curator)

### Skenario A: Media Sedang Digunakan (Blokir)
1.  Masuk ke panel admin sebagai user yang **TIDAK** memiliki izin `DeleteUsed:CuratorMedia`.
2.  Buka menu **Posts** dan buat/edit sebuah post, lalu pasang gambar pada field **Thumbnail**.
3.  Simpan post tersebut.
4.  Buka menu **Media** (Curator).
5.  Cari gambar yang tadi Anda pasang di post.
6.  **Verifikasi**: 
    -   Tombol hapus (sampah) pada baris tabel harusnya **nonaktif/disabled**.
    -   Klik tombol edit (mata/pensil) pada media tersebut. Di bagian bawah (subheading), harus muncul pesan peringatan: *"Deletion disabled: This media is currently in use..."*.

### Skenario B: Bypass dengan Izin Khusus
1.  Masuk ke menu **Peran/Roles** (Filament Shield).
2.  Edit peran yang sedang Anda gunakan (misal: `super_admin`).
3.  Cari bagian **Media Curator** (Curator Media).
4.  Centang izin **Delete Used** dan **Force Delete Used**.
5.  Simpan peran tersebut.
6.  Kembali ke menu **Media**.
7.  Cari gambar yang sedang dipakai tadi.
8.  **Verifikasi**: Tombol hapus sekarang harusnya **aktif**. Anda harus bisa menghapus media tersebut meskipun sedang dipakai di post.

---

## 🔄 2. Sinkronisasi Penggunaan Otomatis

1.  Buka database (atau gunakan TablePlus/phpMyAdmin).
2.  Buka tabel `curator_media_usages`.
3.  Di Filament, buat sebuah **Post** baru dan pilih gambar.
4.  **Verifikasi**: Sebuah record baru harus muncul otomatis di tabel `curator_media_usages` dengan `model_type = post`.
5.  Hapus gambar dari post tersebut (set ke kosong) dan simpan.
6.  **Verifikasi**: Record di tabel `curator_media_usages` untuk post tersebut harusnya terhapus secara otomatis.
7.  Hapus post tersebut.
8.  **Verifikasi**: Semua record penggunaan media terkait post tersebut harusnya bersih dari database.

---

## 🔐 3. Proteksi Role & Ownership

### Skenario C: Proteksi Super Admin
1.  Masuk ke menu **Peran/Roles**.
2.  Cari peran bernama `super_admin`.
3.  Coba klik tombol **Ubah** atau **Hapus**.
4.  **Verifikasi**: 
    -   Tindakan ini harusnya dilarang (Error 403 atau tombol tidak berfungsi). 
    -   *Policy* `RolePolicy` akan secara hardcoded menolak perubahan pada role ini untuk mencegah kerusakan sistem.

### Skenario D: Post Ownership (Kepemilikan)
1.  Buat dua user: **User A** dan **User B**. Berikan keduanya role `member` yang memiliki izin `UpdateOwn:Post` dan `DeleteOwn:Post` (tapi **TIDAK** punya `Update:Post` atau `Delete:Post`).
2.  Login sebagai **User A**. Buat sebuah post.
3.  Login sebagai **User B**. 
4.  Coba akses post milik **User A** melalui URL atau menu.
5.  **Verifikasi**: **User B** tidak boleh melihat tombol edit/hapus untuk post milik **User A**, dan jika mencoba akses URL edit secara paksa, harus muncul error **403 Forbidden**.

---

## 🛠 Troubleshooting Tip
Jika izin tidak berubah setelah Anda mencentang di UI Shield, jalankan perintah ini di terminal:
```bash
php artisan shield:generate --all
```
Dan pastikan cache permission dibersihkan:
```bash
php artisan permission:cache-reset
```
