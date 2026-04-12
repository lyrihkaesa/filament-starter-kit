# Media Action Orchestration - Implementation Log

Date: 11 April 2026
Status: Implemented

## Goal

- Semua sinkronisasi relasi media Curator wajib lewat Action Pattern.
- Hindari observer untuk sync usage media.
- Siapkan flow yang reusable untuk Filament dan API.
- Tambah fitur UI untuk melihat media dipakai di mana.

## Implemented Changes

### 1) Post lifecycle via Actions

Added:

- `app/Actions/Posts/CreatePostAction.php`
- `app/Actions/Posts/UpdatePostAction.php`
- `app/Actions/Posts/DeletePostAction.php`

Behavior:

- Create/Update Post otomatis sync `thumbnail_curator_id` ke `curator_media_usages`.
- Delete Post membersihkan usage via `DeleteAllMediaUsagesAction`.

### 2) Filament Post pages/tables now call Actions

Updated:

- `app/Filament/Resources/Posts/Pages/CreatePost.php`
- `app/Filament/Resources/Posts/Pages/EditPost.php`
- `app/Filament/Resources/Posts/Tables/PostsTable.php`

Behavior:

- Create/Edit/Delete row/Delete bulk semuanya lewat Action domain Post.

### 3) Curator “Used By” explorer

Added:

- `app/Actions/Media/ListCuratorMediaUsagesAction.php`
- `app/Filament/Curator/Actions/CuratorMediaUsagesAction.php`
- `resources/views/filament/media/used-by-modal.blade.php`

Updated:

- `app/Filament/Pages/Media/EditMedia.php`

Behavior:

- Tombol `Dipakai Di Mana` pada edit media menampilkan model, record id, field, dan link record jika tersedia.

### 4) Removed implicit cleanup from Post model hook

Updated:

- `app/Models/Post.php`

Behavior:

- Cleanup usage sekarang eksplisit lewat `DeletePostAction` (bukan model observer/hook).

## Test Coverage

Added:

- `tests/Feature/Actions/Posts/PostActionsTest.php`

Updated:

- `tests/Feature/Filament/PostResourceTest.php`
- `tests/Unit/Filament/Curator/CuratorMediaActionsAndTableTest.php`

Result:

- Seluruh test terkait perubahan ini lulus saat dijalankan terfokus.
