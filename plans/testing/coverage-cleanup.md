# Rencana Pembersihan Code Coverage Ignores

Rencana ini bertujuan untuk secara sistematis menguji bagian kode yang saat ini di-ignore, sehingga `@codeCoverageIgnore` bisa dihapus dan coverage tetap 100%.

## Tahap 1: Model Events & Default Values
Target: `CuratorMedia.php` (Events), `User.php` (Anonymize).

1. **Hapus ignore** pada closure `creating` di `CuratorMedia.php`.
2. **Buat test case** yang memicu event ini secara eksplisit (misal: simpan media tanpa UUID/created_by).
3. **Validasi** bahwa field otomatis terisi.

## Tahap 2: Policy & Global Scope
Target: `CuratorMediaPolicy.php`, `CuratorMediaScope.php`.

1. **Hapus ignore** pada method `restore`, `forceDelete`, dll.
2. **Buat Unit Test Policy** di `tests/Unit/Policies/CuratorMediaPolicyTest.php`.
3. **Hapus ignore** pada `CuratorMediaScope`.
4. **Buat Unit Test Scope** yang mengetes kombinasi permission (`View`, `ViewOwn`, `ViewAny`) dengan mocking `Auth` dan `Gate`.

## Tahap 3: Notifications & App Configuration
Target: `RestoreAccountNotification.php`, `AppServiceProvider.php`.

1. **Hapus ignore** pada `toMail`.
2. **Uji isi MailMessage** dengan memanggil method secara manual di Unit Test.
3. **Analisis AppServiceProvider**: Cek apakah konfigurasi `Vite` dan `RateLimiter` benar-benar perlu di-ignore atau bisa dites via middleware test.

## Tahap 4: Filament Custom Logic
Target: `EditProfile.php`.

1. **Pindahkan logika deteksi Browser/OS** ke sebuah *Helper* atau *Value Object* (misal: `app/Data/DeviceInfo.php`).
2. **Tes DeviceInfo secara unit**.
3. **Panggil DeviceInfo** di dalam `EditProfile.php` sehingga bagian yang sulit dites (integrasi Filament) menjadi sangat tipis dan mudah dicover atau biarkan tipis.

---

## Verifikasi Akhir
Setelah setiap tahap, jalankan perintah:
```bash
# Cek coverage 100% tanpa ignore
./vendor/bin/pest --coverage --exactly=100
```
