# Strategi Internasionalisasi & Preferensi User (Web & Mobile)

Dokumen ini menjelaskan strategi sinkronisasi bahasa (locale), zona waktu (timezone), dan tema (theme) antara Laravel (Server), Filament (Web), dan Flutter (Mobile) menggunakan kolom pada tabel `users`.

## Inti Strategi: User-Centric Preferences

Dibandingkan hanya mengandalkan URL prefix atau local storage di device, kita akan menyimpan preferensi utama langsung di tabel `users`. Ini memastikan pengalaman yang konsisten (omnichannel) saat user berpindah dari Web ke App.

### 1. Struktur Tabel `users`
Kita akan menambahkan kolom-kolom berikut agar sistem tahu preferensi user tanpa perlu query tambahan ke tabel lain:

- **`locale`**: (String, default: 'en/id') Menentukan bahasa UI dan pesan API.
- **`timezone`**: (String, default: 'UTC') Memastikan tampilan waktu di Flutter & Filament akurat sesuai lokasi user.
- **`theme`**: (String, default: 'system') Pilihan antara 'light', 'dark', atau 'system'.

---

## Alur Kerja Sinkronisasi

### A. Web (Filament)
1. **Middleware**: Laravel menggunakan middleware `SetUserLocale` yang membaca `auth()->user()->locale`.
2. **Persistence**: Saat user mengganti bahasa di halaman profil Filament, kolom di database langsung diupdate.
3. **Automatic**: UI Filament akan otomatis berubah sesuai bahasa yang dipilih tanpa perlu mengubah URL.

### B. Mobile (Flutter)
1. **Initial Sync**: Saat login, API mengembalikan data profil termasuk `locale`, `timezone`, dan `theme`. Flutter menyimpannya di *Local Storage* (Shared Preferences) agar aplikasi tidak *flicker* saat dibuka kembali.
2. **Request Header**: Flutter mengirimkan header `Accept-Language: id` atau semacamnya pada setiap request API.
3. **Source of Truth**: Jika user ganti HP, setelah login Flutter akan menarik data dari server, sehingga user tidak perlu mengatur ulang bahasa/tema.

---

## Peran Spatie Settings (Global Default)

Meskipun kita menyimpan preferensi di tabel user, kita tetap menggunakan **Spatie Settings** untuk konfigurasi tingkat sistem:
- **`available_locales`**: Daftar bahasa yang didukung aplikasi (misal: `['en', 'id', 'jp']`). Flutter mengambil daftar ini untuk ditampilkan di menu pilihan.
- **`default_locale`**: Digunakan jika user belum login (Guest) atau user baru yang belum memilih bahasa.
- **`default_timezone`**: Standar waktu aplikasi.

---

## Keuntungan Pendekatan Kolom Tabel

1. **API Friendly**: Pesan validasi, notifikasi email, dan push notification otomatis menggunakan bahasa yang benar karena server tahu preferensi user dari database.
2. **Zero URL Complexity**: Tidak perlu pusing dengan `/en/dashboard` atau `/id/dashboard`. URL tetap bersih.
3. **Data Integrity**: Memudahkan pelaporan dan audit (misal: "Berapa banyak user yang menggunakan bahasa Indonesia?").
4. **Time Accuracy**: Dengan menyimpan `timezone`, server bisa mengirim data waktu dalam format UTC, dan Flutter/Web tinggal memformatnya sesuai kolom `timezone` user.

## Implementasi Selanjutnya
1. Migrasi tabel `users` untuk kolom `locale`, `timezone`, dan `theme`.
2. Pembuatan `SetUserLocale` Middleware.
3. Integrasi ke halaman "Edit Profile" di Filament agar user bisa mengubah preferensi ini.
