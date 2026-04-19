# Code Coverage Ignore Log (2026-04-19)

## Ringkasan Update (Pengurangan Ignore)
- Status awal log ini: 24 file memakai `@codeCoverageIgnoreStart/End`.
- Pengurangan yang sudah dilakukan: 10 file ignore berhasil dilepas.
- Status saat ini: 14 file masih di-ignore.
- Verifikasi akhir: coverage tetap `100.0%` dengan `--exactly=100`.

## File Ignore yang Berhasil Dilepas
- `app/Policies/ActivityPolicy.php`
- `app/Policies/PostPolicy.php`
- `app/Support/Activity/ActivitySubjectType.php`
- `app/Support/Filament/FilamentNavigation.php`
- `app/Http/Requests/Posts/IndexPostRequest.php`
- `app/Http/Requests/Posts/StorePostRequest.php`
- `app/Http/Requests/Posts/UpdatePostRequest.php`
- `app/Http/Requests/Users/IndexUserRequest.php`
- `app/Http/Requests/Users/StoreUserRequest.php`
- `app/Http/Requests/Users/UpdateUserRequest.php`

## Test Yang Ditambahkan / Diperluas Untuk Mengganti Ignore
- Ditambah: `tests/Feature/Policies/ActivityPolicyTest.php`
- Diperluas: `tests/Feature/Policies/PostPolicyTest.php` (own-permission branch + deny branch)
- Ditambah: `tests/Unit/Support/ActivitySubjectTypeTest.php`
- Ditambah: `tests/Unit/Support/FilamentNavigationTest.php`
- Ditambah: `tests/Unit/Http/Requests/Posts/PostRequestAuthorizationTest.php`
- Diperluas: `tests/Unit/Http/Requests/Users/UserRequestAuthorizationTest.php`

## Kenapa 14 File Sisanya Masih Di-ignore
- Dominan adalah area Filament Resource/Page konfigurasi dan controller/resource API yang branch-nya lebih banyak serta sensitif terhadap setup panel/routing.
- Untuk menurunkan ignore berikutnya, perlu set test tambahan yang lebih besar (integration-style) agar semua branch konfigurasi dan fallback route/path benar-benar kena.

## Daftar Lokasi `@codeCoverageIgnore` Tersisa
- `app/Actions/Media/ListCuratorMediaUsagesAction.php:13-68`
- `app/Filament/Curator/Actions/CuratorMediaDeleteAction.php:12-65`
- `app/Filament/Curator/Actions/CuratorMediaDeleteBulkAction.php:15-75`
- `app/Filament/Curator/CustomMediaForm.php:20-69`
- `app/Filament/Curator/MediaResource.php:13-42`
- `app/Filament/Pages/Media/EditMedia.php:16-63`
- `app/Filament/Resources/Activities/ActivityResource.php:32-440`
- `app/Filament/Resources/Posts/PostResource.php:25-122`
- `app/Filament/Resources/Posts/Tables/PostsTable.php:23-96`
- `app/Filament/Resources/Users/UserResource.php:26-129`
- `app/Http/Controllers/Api/V1/AuthController.php:23-165`
- `app/Http/Controllers/Api/V1/PostController.php:27-184`
- `app/Http/Resources/Api/V1/PostCollection.php:14-119`
- `app/Models/Post.php:17-114`

## Validasi Yang Sudah Dijalankan
- Targeted tests baru/diubah: PASS.
- Full coverage run:
  - Command: `php.exe -d xdebug.mode=coverage vendor/bin/pest --coverage --exactly=100`
  - Hasil: PASS, total `100.0%`.
