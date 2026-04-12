# Media Action Orchestration (Filament + API Ready)

Dokumen ini menjelaskan pola final agar relasi media Curator tetap konsisten **tanpa observer** dan siap dipakai ulang di API.

## Prinsip Utama

1. Semua mutasi domain yang melibatkan media harus lewat `app/Actions/**`.
2. Sinkronisasi `curator_media_usages` dilakukan eksplisit di Action domain.
3. Observer tidak dipakai untuk sync usage agar flow mudah dilacak.

## Pola Implementasi

### Create

- Action domain membuat record.
- Jika field media ada, panggil `SyncMediaUsageAction`.

Contoh:
- `App\Actions\Posts\CreatePostAction`
- `App\Actions\Users\CreateUserAction`

### Update

- Action domain update record.
- Jika field media berubah (atau dikirim), panggil `SyncMediaUsageAction`.

Contoh:
- `App\Actions\Posts\UpdatePostAction`
- `App\Actions\Users\UpdateUserAction`

### Delete

- Action domain delete record.
- Sebelum delete, panggil `DeleteAllMediaUsagesAction`.

Contoh:
- `App\Actions\Posts\DeletePostAction`

## Integrasi Filament

Filament page/resource tidak melakukan logika media langsung.

Filament hanya memanggil Action domain:

- `CreatePost` -> `CreatePostAction`
- `EditPost` -> `UpdatePostAction`
- `DeleteAction` Post -> `DeletePostAction`

Dengan pola ini, endpoint API nanti tinggal memanggil Action yang sama.

## Audit Pemakaian Media

Fitur “Dipakai Di Mana” di edit media Curator membaca `curator_media_usages` melalui:

- `App\Actions\Media\ListCuratorMediaUsagesAction`

UI:

- `App\Filament\Curator\Actions\CuratorMediaUsagesAction`
- `resources/views/filament/media/used-by-modal.blade.php`
