# Integrasi Policy & Action

Panduan ini menjelaskan cara tepat menggunakan **Laravel Policy** di dalam dashboard Filament, API, dan pola pemrograman **Action Pattern**.

Ini adalah salah satu dokumen paling penting untuk memahami cara pikir starter kit ini, karena di sinilah terlihat batas tanggung jawab antara:

- authorization
- validasi
- business logic

Kalau batas ini jelas, project akan jauh lebih mudah dirawat.

## Otorisasi Berlapis

Otorisasi harus dilakukan di baris terdepan aplikasi agar data tetap aman.

Saya tidak suka action yang diam-diam melakukan auth check internal, karena itu membuat action jadi sulit dipakai ulang dan sulit dipahami.

## 1. Filament UI Actions

Filament secara otomatis akan mengecek Policy jika ada. Misalnya, `UserResource` akan mengecek `UserPolicy@viewAny`, `create`, `update`, dan `delete`.

Untuk tombol kustom, gunakan `authorize()` atau `can()`:

```php
use Filament\Actions\Action;

Action::make('suspendUser')
    ->authorize('suspend', $record) // Mengacu ke UserPolicy@suspend
    ->action(fn (User $record) => $record->suspend())
```

### Kenapa ini bagus

Karena aturan akses tetap terlihat di layer UI yang memanggil aksi tersebut. Jadi developer yang membaca tombol itu langsung tahu bahwa ada otorisasi yang terlibat.

## 2. API Layer: FormRequest

Untuk API, cek hak akses **sebelum** aturan validasi data dijalankan. Gunakan method `authorize()` di dalam FormRequest.

```php
// app/Http/Requests/StoreUserRequest.php
public function authorize(): bool
{
    // Cek apakah user boleh membuat user baru
    return $this->user()->can('create', User::class);
}

public function rules(): array
{
    // Aturan validasi dijalankan SETELAH authorize() mengembalikan true
    return [
        'email' => 'required|email|unique:users',
        'role' => 'required',
    ];
}
```

### Kenapa `authorize()` penting

Karena request yang tidak berhak sebaiknya ditolak secepat mungkin. Tidak ada gunanya memvalidasi detail field kalau user-nya sendiri memang tidak punya izin melakukan aksi tersebut.

## Pola "Action Pattern" yang Bersih

Action Pattern di starter kit ini adalah class yang membungkus satu tugas bisnis saja, misalnya `UpdateUserPassword`.

> Aturan penting: Action harus **auth-agnostic**.

Artinya, Anda **tidak boleh** menaruh `Gate::authorize()` atau pengecekan policy di dalam action. Kenapa?

1. **Reusability**: action bisa dipanggil dari console command atau job, di mana mungkin tidak ada user yang sedang login
2. **Kejelasan tanggung jawab**: authorization adalah tugas pemanggil, bukan tugas business logic

## Cara Penulisan yang Benar

Kirimkan data atau object yang dibutuhkan ke action, lalu asumsikan pemanggil sudah melakukan authorization.

```php
// app/Actions/Users/UpdateUserPassword.php
final class UpdateUserPassword
{
    public function handle(User $user, string $newPassword): void
    {
        // Langsung eksekusi logika bisnisnya saja.
        $user->update(['password' => $newPassword]);
    }
}
```

Perhatikan bahwa starter kit ini memakai `handle()`, bukan `execute()`.

## Di Sisi Pemanggil

Misalnya di controller:

```php
public function update(UpdatePasswordRequest $request, User $user, UpdateUserPassword $action)
{
    // FormRequest@authorize sudah mengecek Policy.
    $action->handle($user, $request->validated('password'));

    return response()->noContent();
}
```

## Studi Kasus

Bayangkan Anda punya fitur ubah password yang bisa dipanggil dari:

- halaman profil
- endpoint API
- command reset password massal

Kalau action mengandung auth check internal, flow CLI atau job akan jadi aneh. Tetapi kalau action hanya fokus ke business logic, semua lapisan pemanggil tetap bisa memakainya dengan jelas.

## Ringkasan Cara Pikir

- **Policy** untuk authorization
- **FormRequest** atau UI layer untuk gerbang akses awal
- **Action** untuk business logic
- **Controller / Filament page** untuk orkestrasi

Dengan cara ini, kode biasanya lebih bersih, lebih mudah didebug, dan lebih mudah dites.
