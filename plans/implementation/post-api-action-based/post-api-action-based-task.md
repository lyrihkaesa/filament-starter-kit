# Post API Action-Based - Implementation Log

Date: 11 April 2026  
Status: Implemented

## Objective

Menyamakan alur Filament dan API untuk modul Post agar semuanya lewat Action Pattern (tanpa observer untuk media usage sync).

## Implemented

### New API Controller

- `app/Http/Controllers/Api/V1/PostController.php`

REST endpoints:

- `index`
- `store`
- `show`
- `update`
- `destroy`

### New Requests

- `app/Http/Requests/Posts/IndexPostRequest.php`
- `app/Http/Requests/Posts/StorePostRequest.php`
- `app/Http/Requests/Posts/UpdatePostRequest.php`

### New API Resources

- `app/Http/Resources/Api/V1/PostResource.php`
- `app/Http/Resources/Api/V1/PostCollection.php`

### Routes

Updated:

- `routes/api/v1.php`  
  Added `Route::apiResource('posts', PostController::class);`

### Token Abilities

Updated:

- `app/Http/Controllers/Api/V1/AuthController.php`

Added ability mapping:

- `posts:read`
- `posts:create`
- `posts:update`
- `posts:delete`

### Action Reuse

Post API memakai Action domain yang sama dengan Filament:

- `CreatePostAction`
- `UpdatePostAction`
- `DeletePostAction`

Sehingga sinkronisasi `curator_media_usages` terjadi konsisten di semua channel.

## Testing

Added:

- `tests/Feature/Api/V1/PostApiTest.php`

Validated scenarios:

- list posts + metadata/can flags
- create post + usage sync
- update thumbnail + usage resync
- delete post + usage cleanup

Test suite terkait API (`AuthApiTest`, `UserApiTest`, `PostApiTest`) passing.
