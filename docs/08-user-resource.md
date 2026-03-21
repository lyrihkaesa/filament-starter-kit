# User Resource - Action Pattern Integration

Dokumen ini menjelaskan bagaimana `UserResource` di Filament diintegrasikan dengan **Action Pattern** untuk menangani operasi Create, Update, dan Delete secara terpisah.

---

## 🏗️ Struktur Action

Semua logika bisnis untuk User disimpan dalam folder `app/Actions/Users/`:

-   `CreateUserAction`: Menangani pembuatan user baru.
-   `UpdateUserAction`: Menangani pembaruan data user.
-   `DeleteUserAction`: Menangani penghapusan user (termasuk soft delete dan anonymization logic).

### Contoh Penggunaan Action

```php
// Pembuatan User
app(CreateUserAction::class)->handle($data);

// Pembaruan User
app(UpdateUserAction::class)->handle($user, $data);

// Penghapusan User
app(DeleteUserAction::class)->handle($user);
```

---

## 🛠️ Integrasi Filament

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

---

## ✅ Keuntungan Menggunakan Action Pattern

1.  **Reusability**: Logika pembuatan user bisa dipanggil dari Filament, API, atau Artisan Command tanpa duplikasi kode.
2.  **Testability**: Kita dapat melakukan Unit Testing pada masing-masing Action secara terisolasi.
3.  **Maintainability**: Memisahkan logika besar dari file Resource/Page Filament yang cenderung cepat membesar.
4.  **Transaction Safety**: Setiap action dibungkus dalam `DB::transaction()` untuk memastikan integritas data.
