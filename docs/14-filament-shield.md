# 🛡️ Panduan Filament Shield

Filament Shield adalah alat (package) yang memudahkan kita mengelola **Role** (Peran) dan **Permission** (Izin) di aplikasi Filament. Paket ini dibuat di atas [Spatie Permission](https://spatie.be/docs/laravel-permission).

## 💡 Konsep Dasar (Untuk Pemula)

Sebelum masuk ke teknis, pahami dua istilah ini:
1.  **Role (Peran)**: Jabatan seseorang (contoh: `Admin`, `Member`, `Editor`).
2.  **Permission (Izin)**: Apa yang bisa dilakukan jabatan tersebut (contoh: `menghapus user`, `melihat laporan`).

## 🚀 Cara Kerja Otomatis

Hebatnya Filament Shield adalah ia bisa mendeteksi Resource, Halaman (Page), dan Widget Anda secara otomatis.

### 1. HasRoles Trait
Pastikan model `User` Anda memiliki "kemampuan" untuk mengelola role.
```php
// app/Models/User.php
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles; // Wajib ditambahkan!
}
```

### 2. Deteksi Otomatis
Setiap kali Anda membuat Resource baru (misalnya `ProductResource`), Shield akan menyiapkan izin standar:
- `view_any`: Bisa melihat daftar data.
- `create`: Bisa menambah data.
- `update`: Bisa mengubah data.
- `delete`: Bisa menghapus data.

> [!TIP]
> Jalankan perintah `php artisan shield:generate --all` untuk menyinkronkan Resource baru Anda ke dalam database agar muncul di halaman kelola Role.

## ⚙️ Pengaturan di `filament-shield.php`

Beberapa pengaturan penting yang perlu Anda tahu:
- **`super_admin`**: Peran tertinggi yang punya akses ke mana saja.
- **`panel_user`**: Peran dasar bagi siapa saja yang boleh masuk ke dashboard Filament.
- **`exclude`**: Jika ada Resource atau Page yang tidak ingin Anda beri batasan akses, masukkan ke sini.

> [!NOTE]
> Secara default, aplikasi ini menggunakan gaya penulisan **PascalCase** untuk izin (contoh: `ViewAnyUser`), tapi ini bisa diubah di file konfigurasi.
