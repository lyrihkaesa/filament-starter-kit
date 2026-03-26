# Action Pattern `make:action` - CQRS

Salah satu pondasi terpenting di starter kit ini adalah **Action Pattern**.

Kalau Anda baru belajar Laravel, anggap Action Pattern sebagai cara untuk memindahkan logika bisnis keluar dari controller, keluar dari Filament page, dan keluar dari tempat-tempat yang seharusnya fokus pada input/output.

Saya juga membuat command `php artisan make:action` karena Laravel belum menyediakan generator bawaan yang sesuai dengan pola kerja saya.

## Kenapa Dokumen Ini Penting

Di Laravel + Filament, kata "action" bisa membingungkan karena ada dua istilah yang mirip tetapi tugasnya berbeda:

1. **Action Pattern** di `app/Actions/...`
2. **Filament Action** seperti `Action::make()`, `DeleteAction::make()`, `CreateAction::make()`

Banyak developer pemula mencampuradukkan dua hal ini. Karena itu, dokumen ini wajib menjelaskan perbedaannya dengan jelas.

## Apa Itu Action Pattern

Action Pattern adalah class PHP yang menangani **satu tugas bisnis** dengan jelas.

Contoh:

- `CreateUserAction`
- `UpdateUserAction`
- `DeleteUserAction`
- `ApproveOrderAction`
- `PublishPostAction`

Biasanya action pattern diletakkan di:

```text
app/Actions/
```

dan method utamanya di starter kit ini adalah:

```php
handle(...)
```

## Apa Itu Filament Action

Filament Action adalah action milik UI Filament. Biasanya dipakai untuk:

- tombol di header
- tombol di tabel
- tombol submit kecil di page
- bulk action
- modal action

Contoh:

```php
Action::make('logoutOtherBrowserSessions')
DeleteAction::make()
CreateAction::make()
EditAction::make()
```

Filament Action berada di lapisan **interface / presentasi**, bukan lapisan business logic inti.

## Perbedaan Action Pattern vs Filament Action

| Aspek | Action Pattern | Filament Action |
| --- | --- | --- |
| Lokasi | `app/Actions/...` | File Filament page/resource/table |
| Tujuan | Menjalankan business logic | Menyediakan interaksi UI |
| Dipakai ulang dari API/CLI/Job | Ya | Tidak |
| Bergantung pada Filament | Tidak | Ya |
| Cocok untuk transaksi bisnis | Ya | Hanya sebagai pemicu/orchestrator |
| Method umum | `handle()` | `Action::make(...)->action(...)`, `->using(...)` |

## Cara Pikir yang Benar

### Filament Action = tombol atau trigger UI

Ia mewakili "apa yang user klik".

### Action Pattern = pekerjaan bisnis yang sesungguhnya

Ia mewakili "apa yang sistem kerjakan".

Kalau dua hal ini dicampur, biasanya resource atau page akan cepat menjadi tebal dan sulit dipakai ulang.

## Analogi Sederhana

Bayangkan Anda punya tombol "Hapus User" di panel admin.

- **Filament Action** adalah tombol hapus yang muncul di UI
- **DeleteUserAction** adalah class yang benar-benar menjalankan proses hapus user

Artinya:

- Filament Action menerima klik
- Action Pattern menjalankan logika bisnis

## Kenapa saya suka pattern ini

Karena saya ingin:

- logika create/update/delete tidak tersebar
- kode mudah dipakai ulang dari Filament, API, command, atau job
- tanggung jawab file lebih jelas
- testing lebih mudah

## Perbedaan Cara Pikir

Tanpa Action Pattern, developer biasanya melakukan ini:

- validasi di controller
- mutasi model di controller
- upload file di controller
- kirim notifikasi di controller
- transaksi database di controller
- bahkan logic delete atau suspend user langsung ditulis di Filament page

Hasilnya controller atau Filament page jadi tebal, susah dites, dan susah dicari saat ada bug.

Dengan Action Pattern, alurnya berubah:

1. UI menerima input
2. UI atau request melakukan validasi dan authorization
3. Action mengerjakan proses bisnis
4. UI menampilkan hasil

Saya sengaja memakai method `handle()` di action supaya pola pemanggilannya konsisten di seluruh project.

## Kapan Pakai Action Pattern

Gunakan Action Pattern jika:

- logika bisnis perlu dipakai ulang dari lebih dari satu tempat
- operasi bukan sekadar klik UI, tetapi benar-benar mutasi data atau proses domain
- Anda ingin logic mudah dites
- operasi melibatkan transaksi, side effect, notifikasi, upload, atau update banyak model

Contoh yang cocok:

- create user
- update user
- delete user
- approve order
- publish post
- reset password user

## Kapan Pakai Filament Action

Gunakan Filament Action jika:

- Anda butuh tombol atau action di halaman Filament
- Anda ingin menampilkan modal, konfirmasi, atau trigger UI
- action itu hidup di tabel, header, form, atau page Filament

Contoh yang cocok:

- tombol `DeleteAction::make()`
- tombol `Action::make('savePassword')`
- tombol bulk action di table
- action custom di halaman profile Filament

## Kapan Pakai Keduanya Bersamaan

Inilah pola yang paling sering saya pakai.

Gunakan:

- **Filament Action** sebagai pemicu dari UI
- **Action Pattern** sebagai pelaksana business logic

Ini biasanya adalah kombinasi terbaik.

## Contoh Nyata: Pakai Action Pattern di Halaman Filament Resource

Misalnya untuk halaman create user:

```php
protected function handleRecordCreation(array $data): Model
{
    return app(CreateUserAction::class)->handle($data);
}
```

Dan untuk edit user:

```php
protected function handleRecordUpdate(Model $record, array $data): Model
{
    return app(UpdateUserAction::class)->handle($record, $data);
}
```

Di sini:

- halaman Filament tetap mengurus alur UI
- business logic create/update dipindahkan ke action pattern

## Contoh Nyata: Filament Delete Action Memanggil Action Pattern

Di halaman edit atau tabel Filament, pola yang saya suka adalah seperti ini:

```php
DeleteAction::make()
    ->using(fn (User $record, DeleteUserAction $deleteAction) => $deleteAction->handle($record)),
```

Di sini jelas sekali perannya:

- `DeleteAction::make()` adalah Filament Action
- `DeleteUserAction` adalah Action Pattern

Jadi, tombol delete tetap milik Filament, tetapi proses hapus user yang sebenarnya tetap dipusatkan di `app/Actions/Users/DeleteUserAction.php`.

## Contoh Nyata: Filament Custom Action

Untuk action UI custom, misalnya tombol logout browser lain di halaman profile, bentuk Filament Action bisa seperti ini:

```php
Action::make('logoutOtherBrowserSessions')
    ->action(function (array $data, LogoutOtherBrowserSessionsAction $action): void {
        $action->handle($data['password']);
    })
```

Pola ini bagus karena:

- tombol dan modal tetap ditangani Filament
- logic logout tetap hidup di action pattern

## Contoh Pemakaian di Controller API

Di API controller, Action Pattern biasanya dipanggil setelah authorization dan validation selesai.

Contoh:

```php
public function store(StoreUserRequest $request): JsonResponse
{
    $user = app(CreateUserAction::class)->handle($request->validated());

    return response()->json([
        'message' => 'User berhasil dibuat',
        'data' => [
            'user' => $user,
        ],
        'errors' => null,
    ], 201);
}
```

Di sini urutannya:

1. `StoreUserRequest` mengurus authorization dan validation
2. `CreateUserAction` mengurus business logic
3. controller mengurus response JSON

## Contoh Pemakaian di Controller API untuk Update

```php
public function update(UpdateUserRequest $request, User $user): JsonResponse
{
    $user = app(UpdateUserAction::class)->handle($user, $request->validated());

    return response()->json([
        'message' => 'User berhasil diperbarui',
        'data' => [
            'user' => $user,
        ],
        'errors' => null,
    ]);
}
```

## Contoh Pemakaian di Controller API untuk Delete

```php
public function destroy(User $user): JsonResponse
{
    app(DeleteUserAction::class)->handle($user);

    return response()->json([
        'message' => 'User berhasil dihapus',
        'data' => null,
        'errors' => null,
    ]);
}
```

## Studi Kasus 1: Modul User

Anda ingin membuat modul user yang bisa dipakai dari:

- Filament admin panel
- endpoint API
- command impor data

Kalau logika create user ditaruh di masing-masing tempat, Anda akan menulis hal yang sama berulang kali:

- hash password
- set role default
- kirim notifikasi
- simpan avatar

Dengan `CreateUserAction`, semua itu bisa dipusatkan:

```php
app(CreateUserAction::class)->handle($data);
```

Filament, API, dan command cukup memanggil action yang sama.

## Studi Kasus 2: Aksi Bisnis Non-CRUD

Contoh lain adalah `ApproveOrderAction`.

Ini bukan operasi CRUD biasa, tetapi tetap sangat cocok jadi action pattern karena:

- punya satu tujuan jelas
- dapat dipakai dari banyak tempat
- mudah dites
- tidak tergantung ke Filament

Kalau Anda butuh tombol approve di panel admin, maka:

- tombol approve dibuat dengan Filament Action
- proses approve tetap dijalankan oleh `ApproveOrderAction`

## Fitur Utama Command

- Generate action class untuk **Create / Update / Delete**
- Custom path atau sub-folder untuk struktur folder yang rapi
- Interaktif jika tanpa parameter
- Auto-create seluruh CRUD action jika tidak spesifik
- Dukungan `--force` untuk overwrite file
- Prompt konfirmasi jika file sudah ada dan tidak diberi `--force`

## Penggunaan Dasar

### 1. Generate Custom Action

```bash
php artisan make:action
```

Maka akan muncul prompt:

```text
What is the class name? (e.g., Posts/PublishPostAction):
> Posts/PublishPostAction
```

Hasilnya:

```text
app/Actions/Posts/PublishPostAction.php
```

### 2. Generate CRUD Action untuk Model

```bash
php artisan make:action --model=User
```

Hasil:

```text
app/Actions/Users/CreateUserAction.php
app/Actions/Users/UpdateUserAction.php
app/Actions/Users/DeleteUserAction.php
```

### 3. Custom Sub-Folder

```bash
php artisan make:action --model=User --sub-folder=Access
```

Hasil:

```text
app/Actions/Access/CreateUserAction.php
app/Actions/Access/UpdateUserAction.php
app/Actions/Access/DeleteUserAction.php
```

### 4. Generate Spesifik Action

```bash
php artisan make:action --model=User --create
php artisan make:action --model=User --update
php artisan make:action --model=User --delete
```

### 5. Overwrite File dengan Paksa

```bash
php artisan make:action --model=User --force
```

Tanpa `--force`, jika file sudah ada maka akan muncul prompt konfirmasi.

## Stub File

Stub disimpan di:

```text
stubs/
├── create-action.stub
├── update-action.stub
├── delete-action.stub
└── custom-action.stub
```

Jika tidak ditemukan, command akan menggunakan fallback stub bawaan dari path internal command.

## Argument dan Opsi

| Opsi | Alias | Keterangan |
| --- | --- | --- |
| `name` | - | Nama action class, dipakai untuk custom action |
| `--model=Post` | `-m` | Nama model yang akan dipakai untuk generate action CRUD |
| `--create` | `-c` | Hanya generate `Create<Model>Action` |
| `--update` | `-u` | Hanya generate `Update<Model>Action` |
| `--delete` | `-d` | Hanya generate `Delete<Model>Action` |
| `--sub-folder=XYZ` | `-s` | Sub-folder dalam `app/Actions` |
| `--force` | `-f` | Overwrite file jika sudah ada |

## Struktur Folder Hasil

```text
app/
└── Actions/
    ├── Users/
    │   ├── CreateUserAction.php
    │   ├── UpdateUserAction.php
    │   └── DeleteUserAction.php
    └── Posts/
        └── PublishPostAction.php
```

## Best Practice

- Gunakan `--sub-folder` untuk struktur domain-based seperti `Billing`, `Auth`, atau `Access`
- Simpan action yang bukan CUD di folder yang menjelaskan domain-nya
- Pisahkan validasi dan otorisasi ke FormRequest, Policy, atau Filament action
- Gunakan `handle()` secara konsisten agar pola class mudah dikenali
- Jika action memodifikasi beberapa model sekaligus, pertimbangkan `DB::transaction()`
- Pakai Filament Action untuk UI trigger, dan Action Pattern untuk business logic

## Kesalahan Umum Developer Pemula

### 1. Menaruh authorization di dalam Action Pattern

Saya tidak menyarankan ini. Action harus fokus pada logika bisnis. Authorization sebaiknya dilakukan oleh pemanggil.

### 2. Menaruh semua logika di Filament Resource

Ini cepat di awal, tapi menyulitkan ketika logic mulai dipakai ulang.

### 3. Mengira Filament Action dan Action Pattern itu sama

Ini kesalahan yang sangat umum.

- Filament Action = lapisan UI
- Action Pattern = lapisan business logic

### 4. Membuat action terlalu generik

Contoh yang kurang bagus:

- `SaveUserAction`
- `ProcessDataAction`

Nama seperti ini sering terlalu kabur. Saya lebih suka nama yang jelas tujuannya:

- `CreateUserAction`
- `UpdateUserAction`
- `AssignRoleToUserAction`

## Kesimpulan

Kalau disederhanakan:

- **Filament Action** dipakai untuk interaksi di panel admin
- **Action Pattern** dipakai untuk pekerjaan bisnis yang sesungguhnya
- Dalam banyak kasus, keduanya dipakai bersama

Action Pattern adalah salah satu cara utama saya menjaga starter kit ini tetap rapi saat project bertambah besar. Kalau Anda paham file ini, Anda akan jauh lebih mudah memahami file dokumentasi lain di starter kit ini.
