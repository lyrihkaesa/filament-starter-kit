# `make:starter-resource`

Dokumen ini menjelaskan command kustom `make:starter-resource` yang ada di starter kit ini.

Command ini saya buat karena saya tidak ingin setiap developer harus mengulang wiring yang sama setiap kali membuat Filament resource baru. Tujuan utamanya adalah menyesuaikan generator resource dengan pola arsitektur starter kit ini, terutama **Action Pattern**.

## Kenapa Tidak Cukup Pakai `make:filament-resource`

Command bawaan Filament memang bagus untuk membuat struktur awal resource. Tetapi untuk kebutuhan starter kit ini, ada beberapa kekurangan:

- create page belum otomatis terhubung ke `Create...Action`
- edit page belum otomatis terhubung ke `Update...Action`
- struktur schema dan table belum mengikuti pola stub yang saya siapkan
- developer harus mengulang wiring manual terus-menerus

Karena itu saya membuat command sendiri.

## Signature Command

Berdasarkan implementasi yang ada saat ini, signature command-nya adalah:

```bash
php artisan make:starter-resource {model} {--view} {--soft-deletes} {--force}
```

## Lokasi Implementasi

Command berada di:

```text
app/Console/Commands/MakeStarterResource.php
```

Stub berada di:

```text
stubs/starter-kit/resource/
```

## File yang Dihasilkan

Secara umum, command ini menghasilkan struktur seperti berikut untuk model `User`:

```text
app/Filament/Resources/Users/UserResource.php
app/Filament/Resources/Users/Pages/CreateUser.php
app/Filament/Resources/Users/Pages/EditUser.php
app/Filament/Resources/Users/Pages/ListUsers.php
app/Filament/Resources/Users/Pages/ViewUser.php
app/Filament/Resources/Users/Schemas/UserForm.php
app/Filament/Resources/Users/Schemas/UserInfolist.php
app/Filament/Resources/Users/Tables/UsersTable.php
app/Actions/Users/CreateUserAction.php
app/Actions/Users/UpdateUserAction.php
app/Actions/Users/DeleteUserAction.php
```

## Cara Kerja Umum

Saat Anda menjalankan:

```bash
php artisan make:starter-resource User
```

command akan:

1. Menentukan nama model dan bentuk plural-nya
2. Membuat folder resource Filament sesuai struktur starter kit
3. Menjalankan `make:action` untuk action terkait model
4. Mengisi file resource dari stub yang sudah disiapkan

## Integrasi dengan Action Pattern

Bagian paling penting dari command ini adalah wiring ke action.

Tujuannya supaya halaman create dan edit Filament langsung menggunakan pola starter kit, misalnya:

```php
protected function handleRecordCreation(array $data): Model
{
    return app(CreateUserAction::class)->handle($data);
}
```

dan:

```php
protected function handleRecordUpdate(Model $record, array $data): Model
{
    return app(UpdateUserAction::class)->handle($record, $data);
}
```

Perhatikan bahwa saya mempertahankan method `handle()` karena itu memang pola yang saya gunakan di seluruh starter kit.

## Opsi yang Tersedia

### `--view`

Digunakan ketika Anda ingin resource juga memiliki halaman view/read-only.

### `--soft-deletes`

Digunakan jika model menggunakan soft delete dan Anda ingin stub resource menyesuaikan query-nya.

### `--force`

Digunakan untuk menimpa file yang sudah ada.

## Studi Kasus

Bayangkan Anda sedang membuat modul `Product`.

Tanpa command ini, Anda biasanya perlu:

- generate resource
- buat create page
- buat edit page
- buat list page
- buat table
- buat form schema
- buat infolist
- buat action create/update/delete
- hubungkan semuanya manual

Dengan command ini, beban itu berkurang drastis. Anda tinggal fokus pada:

- field form
- kolom table
- business rule di action

## Cara Pikir di Balik Command Ini

Saya ingin generator membantu menghasilkan struktur yang "memaksa ke arah yang benar".

Artinya:

- developer baru tidak perlu menebak pola project
- struktur resource lebih konsisten
- action pattern lebih mudah diadopsi
- copy-paste wiring berkurang

Ini salah satu contoh bagaimana saya lebih memilih sedikit boilerplate generator daripada boilerplate manual berulang.

## Hal yang Perlu Anda Lengkapi Setelah Generate

Command ini tidak otomatis menyelesaikan semua hal. Setelah file dibuat, biasanya Anda masih perlu:

- melengkapi field `form`
- melengkapi `table`
- melengkapi `infolist`
- mengisi logic sebenarnya di action
- menulis test jika resource punya perilaku khusus

## Catatan

- Jangan modifikasi file `vendor/` untuk menyesuaikan generator
- Jika butuh perubahan struktur output, ubah stub dan command starter kit
- Anggap hasil generate sebagai pondasi, bukan hasil final production-ready
