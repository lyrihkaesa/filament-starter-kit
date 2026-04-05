# User Resource - Action Pattern Integration

Dokumen ini menjelaskan bagaimana `UserResource` di Filament diintegrasikan dengan **Action Pattern** untuk menangani operasi Create, Update, dan Delete secara terpisah.

Ini adalah salah satu contoh paling penting di starter kit karena biasanya dari sinilah developer mulai memahami bagaimana saya ingin Filament dan logika bisnis saling bekerja sama.

## Kenapa `UserResource` Dijadikan Contoh

Karena modul user hampir selalu ada di admin panel, dan modul ini cukup kompleks untuk menunjukkan pola yang saya sukai:

- create user
- update user
- delete user
- policy
- role dan permission
- notifikasi atau side effect tambahan

Kalau pola di `UserResource` sudah dipahami, Anda biasanya akan lebih mudah meniru pendekatan yang sama untuk `ProductResource`, `OrderResource`, dan resource lain.

## Struktur Action

Semua logika bisnis untuk User disimpan dalam folder `app/Actions/Users/`:

- `CreateUserAction`: menangani pembuatan user baru
- `UpdateUserAction`: menangani pembaruan data user
- `DeleteUserAction`: menangani penghapusan user

### Contoh Penggunaan Action

```php
// Pembuatan User
app(CreateUserAction::class)->handle($data);

// Pembaruan User
app(UpdateUserAction::class)->handle($user, $data);

// Penghapusan User
app(DeleteUserAction::class)->handle($user);
```

## Kenapa Bukan Langsung di Resource

Kalau semua logic diletakkan langsung di `UserResource` atau halaman-halamannya, biasanya file akan cepat membesar dan sulit dipakai ulang.

Contoh masalah yang sering muncul:

- API juga butuh create user
- import command juga butuh create user
- business rule create user berubah

Kalau logic ada di action, Anda cukup ubah satu tempat.

## Integrasi Filament

### 1. Create Page (`CreateUser.php`)

Kita meng-override method `handleRecordCreation` untuk menggunakan action class:

```php
protected function handleRecordCreation(array $data): Model
{
    return app(CreateUserAction::class)->handle($data);
}
```

### 2. Edit Page (`EditUser.php`)

Kita meng-override method `handleRecordUpdate` untuk pembaruan:

```php
protected function handleRecordUpdate(Model $record, array $data): Model
{
    return app(UpdateUserAction::class)->handle($record, $data);
}
```

Dan untuk tombol hapus di header:

```php
DeleteAction::make()
    ->using(fn (User $record, DeleteUserAction $deleteAction) => $deleteAction->handle($record)),
```

### 3. List Page / Table (`UsersTable.php`)

Untuk aksi hapus di baris tabel:

```php
DeleteAction::make()
    ->using(fn (User $record, DeleteUserAction $deleteAction) => $deleteAction->handle($record)),
```

## Studi Kasus

Bayangkan aturan bisnis user Anda seperti ini:

- saat user dibuat, password harus di-hash
- role default perlu diberikan
- avatar default mungkin perlu di-set
- notifikasi welcome mungkin perlu dikirim

Kalau logic ini tersebar di:

- halaman create Filament
- API controller
- import command

maka Anda akan duplikasi kode. Dengan `CreateUserAction`, semua logic itu tinggal dipusatkan.

Filament cukup menjadi pemanggil:

```php
return app(CreateUserAction::class)->handle($data);
```

## Keuntungan Menggunakan Action Pattern

1. **Reusability**: logika pembuatan user bisa dipanggil dari Filament, API, atau Artisan Command tanpa duplikasi kode.
2. **Testability**: kita dapat menulis test untuk masing-masing action secara terisolasi.
3. **Maintainability**: logika besar tidak menumpuk di Resource/Page Filament.
4. **Transaction Safety**: action dapat dibungkus dalam `DB::transaction()` jika perlu.

## Catatan Penting

- Action di sini menggunakan method `handle()`, bukan `execute()`
- Authorization sebaiknya tetap dilakukan oleh policy, FormRequest, atau layer pemanggil
- Resource bertugas mengorkestrasi, bukan menjadi pusat semua business logic

## Cara Meniru Pola Ini untuk Resource Lain

Jika Anda ingin membuat `ProductResource`, cara berpikirnya sama:

1. Buat `CreateProductAction`
2. Buat `UpdateProductAction`
3. Buat `DeleteProductAction`
4. Hubungkan page Filament ke action tersebut

Dengan pola ini, semua resource di project akan terasa konsisten.
