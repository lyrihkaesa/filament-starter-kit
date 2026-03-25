# 🛡️ Integrasi Policy & Action

Panduan ini menjelaskan cara tepat menggunakan **Laravel Policy** di dalam dashboard Filament, API, dan pola pemrograman "Action Pattern".

## 🛡️ Otorisasi Berlapis

Otorisasi (pengecekan hak akses) harus dilakukan di baris terdepan aplikasi agar data tetap aman.

### 1. Filament UI Actions
Filament secara otomatis akan mengecek Policy (jika ada). Misalnya, `UserResource` akan mengecek `UserPolicy@viewAny`, `create`, `update`, dan `delete`.

Untuk tombol kustom (Action), gunakan method `authorize()` atau `can()`:
```php
use Filament\Actions\Action;

Action::make('suspendUser')
    ->authorize('suspend', $record) // Mengacu ke UserPolicy@suspend
    ->action(fn (User $record) => $record->suspend())
```

### 2. API Layer: FormRequest
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

---

## ⚡ Pola "Action Pattern" yang Bersih

Action Pattern (gaya Nuno Maduro) adalah kelas yang membungkus satu tugas bisnis saja (contoh: `UpdateUserPassword`).

> [!IMPORTANT]
> **Aturan Penting: Action Harus "Auth-Agnostic" (Bebas dari Pengecekan Auth).**

Anda **tidak boleh** menaruh `Gate::authorize()` atau pengecekan Policy di dalam kelas Action. Kenapa?
1.  **Reusability**: Action bisa dipanggil dari konsol (Artisan command) atau antrean (Job) di mana tidak ada "user yang sedang login".
2.  **Kepastian**: Action harus berjalan andal. Pengecekan hak akses adalah tanggung jawab si **pemanggil** (Controller, Filament Page, atau FormRequest).

### Cara Penulisan yang Benar
Kirimkan data (seperti objek User) langsung ke Action, dan asumsikan pemanggilnya sudah melakukan otorisasi.

```php
// app/Actions/Users/UpdateUserPassword.php
final class UpdateUserPassword
{
    public function execute(User $user, string $newPassword): void
    {
        // Langsung eksekusi logika bisnisnya saja.
        $user->update(['password' => $newPassword]);
    }
}
```

**Di Sisi Pemanggil (Misalnya Controller):**
```php
public function update(UpdatePasswordRequest $request, User $user, UpdateUserPassword $action)
{
    // FormRequest@authorize sudah mengecek Policy.
    $action->execute($user, $request->validated('password'));

    return response()->noContent();
}
```

> [!TIP]
> Dengan cara ini, kode Anda jadi bersih, mudah didebug, dan bisa dipakai di mana saja (Controller, CLI, Job).
