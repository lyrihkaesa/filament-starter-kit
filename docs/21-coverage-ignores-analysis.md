# Analisis Code Coverage Ignores

Dokumen ini mencatat semua bagian kode yang saat ini diabaikan (`@codeCoverageIgnore`) dari perhitungan coverage beserta alasannya dan rencana tindak lanjut.

## Daftar Bagian yang Di-ignore

### 1. `app/Providers/AppServiceProvider.php` (Method `boot`)
- **Status:** Di-ignore (Start-End)
- **Alasan:** Berisi konfigurasi global framework seperti Vite prefetching, pemaksaan HTTPS, dan konfigurasi Rate Limiter. Bagian ini sulit dites secara unit karena efeknya bersifat global pada level infrastruktur/middleware framework.
- **Rencana Analisis:** Cek apakah konfigurasi HTTPS dan Rate Limiter bisa dites menggunakan *Feature Test* yang memicu kondisi tersebut (misal: hit API berkali-kali).

### 2. `app/Query/CuratorMediaScope.php`
- **Status:** Seluruh Class / Bagian Logika
- **Alasan:** Logika scope ini sangat bergantung pada status autentikasi yang kompleks dan kombinasi permission. Saat ini sudah dites secara tidak langsung melalui *Integration Tests* pada resource yang menggunakan media.
- **Rencana Analisis:** Buat *Dedicated Unit Test* untuk Scope ini dengan melakukan mocking pada `Auth::user()` dan berbagai kombinasi permission-nya agar tidak perlu lagi di-ignore.

### 3. `app/Notifications/Auth/RestoreAccountNotification.php` (Method `toMail`)
- **Status:** Di-ignore (Start-End)
- **Alasan:** Isi body email (MailMessage) biasanya hanya berupa teks statis dan URL. Verifikasi pengiriman email umumnya dilakukan di *Integration Test* menggunakan `Notification::fake()`.
- **Rencana Analisis:** Sebenarnya aman untuk tetap di-ignore jika kita sudah mengetes bahwa notifikasi tersebut *terkirim* (`assertSentTo`). Namun, bisa dites dengan memanggil `toMail()` secara manual dan menguji isi propertinya.

### 4. `app/Policies/CuratorMediaPolicy.php` (Method: `restore`, `forceDelete`, dll)
- **Status:** Di-ignore per Method
- **Alasan:** Method-method ini seringkali hanya memanggil `$user->can()` atau mengecek kepemilikan sederhana. Saat ini di-ignore karena fungsionalitasnya sudah tercakup dalam test resource Filament.
- **Rencana Analisis:** Tambahkan *Policy Unit Test* spesifik untuk model `CuratorMedia` guna mencakup semua case (Admin vs Owner vs Other).

### 5. `app/Models/CuratorMedia.php` (Method `booted` - events)
- **Status:** Di-ignore pada closure `creating`
- **Alasan:** Mengatur default UUID, kepemilikan (`created_by`), dan privacy jika kosong. Di-ignore karena dianggap logika boilerplate yang selalu berjalan saat integrasi.
- **Rencana Analisis:** Hapus ignore dan buat test sederhana yang membuat model `CuratorMedia` tanpa atribut tersebut, lalu pastikan atributnya terisi secara otomatis.

### 6. `app/Models/User.php` (Avatar & Booted Events)
- **Status:** Di-ignore (Avatar URL & logic deletion)
- **Alasan:** `getFilamentAvatarUrl` bergantung pada ekosistem Filament. Logika `forceDeleting` untuk anonimisasi user dianggap berisiko tinggi dan sulit dipicu secara konsisten dalam unit test tanpa setup database yang lengkap.
- **Rencana Analisis:** Hapus ignore pada `anonymize()` dan buat test yang memastikan data user berubah menjadi anonim setelah dihapus.

### 7. `app/Models/CuratorMediaUsage.php`
- **Status:** Seluruh Class
- **Alasan:** Model pivot sederhana yang hanya mendefinisikan relasi.
- **Rencana Analisis:** Tetap biarkan di-ignore atau hapus ignore karena pengetesan relasi biasanya sudah tercakup saat mengetes fitur yang menggunakan media.

### 8. `app/Filament/Pages/Auth/EditProfile.php`
- **Status:** Di-ignore (Start-End)
- **Alasan:** Berisi logika kustom untuk profile page Filament, termasuk deteksi browser/OS untuk session management. Sangat bergantung pada state browser asli.
- **Rencana Analisis:** Cek apakah logika deteksi OS/Browser bisa dipisah ke Action/Helper tersendiri agar bisa dites secara unit.

### 9. `app/Filament/Forms/Components/CuratorFileUpload.php`
- **Status:** Seluruh Class (kecuali deklarasi utama)
- **Alasan:** Sebagai sebuah custom form component buatan sendiri yang meluas (extends) dari kelas `FileUpload` bawaan Filament, kelas ini bergantung penuh eksistensinya kepada fitur-fitur Livewire (DOM Hydration/Dehydration). Menangani *mocking* untuk komponen *frontend-heavy* seperti ini di Pest/PHPUnit sangat sulit dan sering menghasilkan error yang rapuh (fragile).
- **Rencana Analisis:** Disarankan untuk tidak menghapus ignore dari sini unless di-test melalui E2E seperti Laravel Dusk.

### 10. `app/Filament/Pages/Auth/Login.php` dan `RestoreAccount.php`
- **Status:** Di-ignore pada bagian eksepsi `TooManyRequestsException` dan *signature fallback* gagal.
- **Alasan:** Mekanisme exception rate-limiter dari package ketiga serta penanganan *invalid signature* yang bersifat *defensive checking* secara programatik susah distimulasi tanpa membuat testing environment menjadi tidak masuk akal lambat/kompleks.
- **Rencana Analisis:** Tetap biarkan di-ignore, ini merupakan boilerplate UI yang sangat bergantung pada environment session & signature handling.

### 11. `app/Models/User.php`
- **Status:** Di-ignore untuk kumpulan helper boolean (`isAnonymous`, `isSoftDeleted`, `isDeletedBySelf`, dll) dan method policy internal model (`canAccessPanel`).
- **Alasan:** Fungsi helper ini isinya satu baris saja dan dites langsung secara terintegrasi bersama fitur `DeleteUserAccountAction` dll. Murni pemecahan kode supaya rapi, bukan logika komputasi rumit.
- **Rencana Analisis:** Tidak ada plan, dibiarkan saja tertulis ignore.

### 12. `app/Actions/Profile/RevokeDeviceAction.php`
- **Status:** Di-ignore pada penentuan label fallback `Unknown Device` jika browser tidak terdeteksi, serta parse date `last_used_at`.
- **Alasan:** Sulit sekali menghapus header HTTP user-agent hingga kosong pada setup Pest untuk trigger `Unknown Device`, begitupun dengan memaksa token tidak memiliki history timestamp bawaan Sanctum.
- **Rencana Analisis:** Karena hanya string fallback defensif, ignore ini dipertahankan selamanya.

### 13. `app/Policies/CuratorMediaPolicy.php`
- **Status:** Di-ignore untuk beberapa method (`create`, `update`, `delete`, dll).
- **Alasan:** Kebanyakan method isinya hanya melempar kembali mengecek role atau mengecek kepemilikan. Policy test khusus dapat menghabiskan waktu tanpa nilai test yang berarti. Fitur di test langsung dalam integrasi `Curator`.
- **Rencana Analisis:** Rencanakan `CuratorMediaPolicyTest` sendiri bila policy berkembang melebihi 1 baris.

---

## Kesimpulan & Tindakan Mendatang

Sebagian besar ignore dilakukan karena ketergantungan pada state global atau framework (Filament/Auth). Strategi utama untuk menghapus ignore ini adalah dengan:
1. **Mocking State:** Menggunakan `Auth::actingAs()` dan mocking `Gate` lebih dalam.
2. **Isolation:** Memindahkan logika kompleks dari Model/Policy ke *Action Classes* yang lebih mudah dites.
3. **Dedicated Testing:** Membuat test file khusus untuk Policy dan Scope yang selama ini hanya dites lewat Feature Test.
