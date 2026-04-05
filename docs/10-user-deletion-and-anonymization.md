# Penghapusan & Anonimisasi Pengguna

Dokumen ini menjelaskan pendekatan sistem dalam menangani penghapusan pengguna: Soft-Deletion untuk pemulihan akun dan Anonimisasi untuk kepatuhan privasi (GDPR).

## Ringkasan

Aplikasi membedakan antara penghapusan yang dilakukan oleh pengguna (self-deletion) dan yang dilakukan oleh administrator (admin-deletion/suspension).

### Penghapusan Mandiri (Pemulihan Akun)

- **Pemicu**: Pengguna mengklik "Hapus Akun" di pengaturan Profil.
- **Efek Langsung**: Akun di-soft-delete (`deleted_at` diisi) dan `deleted_by` diisi dengan ID pengguna itu sendiri.
- **Perilaku Login**: Pengguna dapat melihat petunjuk "Pulihkan Akun?" di halaman login.
- **Masa Tenggang**: Pengguna memiliki waktu **30 hari** untuk memulihkan akun mereka sebelum dianonymize secara otomatis.
- **Notifikasi**: Tautan pemulihan dikirim melalui email jika diminta dari halaman login.

### Penghapusan oleh Admin (Penangguhan/Ban)

- **Pemicu**: Administrator menghapus pengguna dari dashboard Filament.
- **Efek Langsung**: Akun di-soft-delete dan `deleted_by` diisi dengan ID Admin.
- **Perilaku Login**: Pengguna diblokir dari login. Mereka **tidak** dapat melihat petunjuk "Pulihkan Akun?". Mereka akan melihat pesan: *"Akun ini telah ditangguhkan atau dihapus oleh administrator. Silakan hubungi dukungan."*
- **Anonimisasi**: Akun yang dihapus oleh admin **tidak** akan dianonymize secara otomatis. Hal ini menjaga data mereka untuk keperluan audit atau potensi pemulihan oleh admin di kemudian hari.

---

## Logika Anonimisasi

Anonimisasi adalah proses pembersihan data pribadi sambil tetap menjaga record untuk integritas database (misalnya, menjaga relasi).

### Cara Kerja

Saat pengguna dianonymize:
1.  **Nama**: Diubah menjadi "Anonymous User".
2.  **Email**: Diubah menjadi email acak yang unik (misalnya, `anonymous_uuid@example.com`).
3.  **Avatar**: Dihapus.
4.  **Role/Permission**: Semua dicabut.
5.  **Anonymized At**: Timestamp `anonymized_at` diisi.

### Kapan Anonimisasi Terjadi

Anonimisasi hanya dipicu untuk pengguna yang **menghapus diri sendiri** melalui dua cara:
1.  **Otomatis (30 hari)**: melalui command `AnonymizeDeletedUsersCommand`.
2.  **Force Delete**: Jika admin mencoba melakukan "Force Delete" pada user yang aslinya menghapus diri sendiri, sistem akan mengintersepsi dan melakukan anonimisasi alih-alih hard-delete.

> [!NOTE]
> Jika admin melakukan force-delete pada pengguna yang **bukan** menghapus diri sendiri (misalnya, dihapus oleh admin sebelumnya), maka record tersebut akan benar-benar dihapus secara permanen (hard-delete) dari database.

---

## Detail Teknis

### Model
- `User::isDeletedBySelf()`
- `User::isDeletedByAdmin()`
- `User::deletedBy()` (Relasi)

### Kolom Database
- `deleted_by` (UUID, nullable): Menyimpan ID orang yang melakukan penghapusan.

### Command
```bash
php artisan app:anonymize-deleted-users
```
Command ini berjalan setiap hari (lihat `bootstrap/app.php`) untuk memproses pengguna yang telah di-soft-delete selama lebih dari 30 hari.
