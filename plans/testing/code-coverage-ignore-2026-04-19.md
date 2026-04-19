# Code Coverage Ignore Log (2026-04-19)

## Ringkasan Update (Pengurangan Ignore)
- Status awal log ini: 24 file memakai `@codeCoverageIgnoreStart/End`.
- Batch 1: berhasil lepas 10 file.
- Batch 2 (lanjutan terbaru): berhasil lepas 3 file tambahan.
- Total berhasil dilepas: 13 file.
- Status saat ini: 11 file masih di-ignore.
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
- `app/Http/Controllers/Api/V1/PostController.php`
- `app/Http/Resources/Api/V1/PostCollection.php`
- `app/Models/Post.php`

## Test Yang Ditambahkan / Diperluas Untuk Mengganti Ignore
- Ditambah: `tests/Feature/Policies/ActivityPolicyTest.php`
- Diperluas: `tests/Feature/Policies/PostPolicyTest.php` (own-permission branch + deny branch)
- Ditambah: `tests/Unit/Support/ActivitySubjectTypeTest.php`
- Ditambah: `tests/Unit/Support/FilamentNavigationTest.php`
- Ditambah: `tests/Unit/Http/Requests/Posts/PostRequestAuthorizationTest.php`
- Diperluas: `tests/Unit/Http/Requests/Users/UserRequestAuthorizationTest.php`
- Diperluas: `tests/Feature/Api/V1/PostApiTest.php` (cursor pagination + show success + show forbidden)
- Ditambah: `tests/Unit/Models/PostModelTest.php` (autofill author pada `creating` hook)
- Ditambah: `tests/Unit/Http/Controllers/Api/V1/PostControllerTest.php` (fallback branch `collectionItems`)

## Kenapa 11 File Sisanya Masih Di-ignore
- Sisa file didominasi konfigurasi Filament Resource/Page/Action yang cukup besar dan memiliki banyak branch konfigurasi UI/behavior.
- Pengurangannya masih bisa dilanjutkan, tetapi butuh penambahan integration test Filament yang lebih banyak agar branch callback/action/table/schema benar-benar terpicu semua.

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

## Validasi Yang Sudah Dijalankan
- Targeted tests baru/diubah: PASS.
- Full coverage run:
  - Command: `php.exe -d xdebug.mode=coverage vendor/bin/pest --coverage --exactly=100`
  - Hasil: PASS, total `100.0%`.
