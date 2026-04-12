# Authorization & Policy Standards

Dokumen ini mendefinisikan standar implementasi otorisasi dan policy dalam Starter Kit ini untuk memastikan skalabilitas dan kemudahan maintenance.

---

## 1. Hierarki Permission (Permission Tiers)

Kita membagi hak akses menjadi 4 level hierarki:

| Level | Role | Scope | Contoh Permission |
| :--- | :--- | :--- | :--- |
| **Level 4** | **Super Admin** | **Global Access** | Punya segalanya via `Gate::before`. |
| **Level 3** | **Admin / Staff** | **Global Management** | `View:Post`, `Delete:User`, `View:Activity`. |
| **Level 2** | **Member (Owner)** | **Ownership Access** | `ViewOwn:Post`, `UpdateOwn:Post`, `Create:Post`. |
| **Level 1** | **Guest / Public** | **Public Visibility** | Berdasarkan status record (misal: `published_at`). |

---

## 2. Naming Convention (Shield Style)

Gunakan format `PascalCase` dengan pemisah titik dua (`:`) agar kompatibel dengan Filament Shield.

- **Global Actions**: `{Action}:{Resource}` (Contoh: `View:Post`, `Create:Post`, `Delete:User`)
- **Owned Actions**: `{Action}Own:{Resource}` (Contoh: `UpdateOwn:Post`, `ViewOwn:CuratorMedia`)

---

## 3. Model Otorisasi

Kita menerapkan dua pola otorisasi tergantung pada kebutuhan resource:

### A. Visibility Matrix Model (Contoh: Media/Curator)
Digunakan ketika sebuah resource memiliki status visibilitas yang eksplisit di database.

| Visibility | Admin | Owner | Logged-in | Guest |
| :--- | :--- | :--- | :--- | :--- |
| **Private** | CRUD | CRUD | **Blocked** | **Blocked** |
| **Member** | CRUD | CRUD | **View Only** | **Blocked** |
| **Public** | CRUD | CRUD | **View Only** | **View Only** |

### B. Simplified Ownership Model (Contoh: Post)
Model yang lebih sederhana tanpa kolom visibilitas, hanya berdasarkan kepemilikan.

- **Admin / Staff**: Dapat melihat dan mengelola **semua** data.
- **Member / User**: Hanya dapat melihat dan mengelola data **milik sendiri**.
- **Anonymous**: Dapat melihat data melalui frontend jika statusnya `Published`.

---

## 4. Pola Implementasi (Best Practice)

### Filament Scoping (PostResource.php)
Gunakan `getEloquentQuery` untuk menyaring data sebelum sampai ke tabel:

```php
public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();
    $user = auth()->user();

    // 1. Admin/Global: Lihat semua
    if ($user->can('View:Post')) {
        return $query;
    }

    // 2. Member/Owner: Lihat punya sendiri
    return $query->where('author_id', $user->id);
}
```

### Policy Logic (PostPolicy.php)
Gunakan logika "Global First, then Ownership" dalam method policy:

```php
public function update(User $user, Post $post): bool
{
    // 1. Cek izin Global
    if ($user->can('Update:Post')) {
        return true;
    }

    // 2. Cek izin Ownership + Kepemilikan data
    return $user->can('UpdateOwn:Post') && $post->author_id === $user->id;
}
```

---

## 5. Seeding Strategy

- **Production**: Hanya jalankan `ShieldSeeder` untuk membangun struktur role dan permission dasar.
- **Local Dev**: Jalankan data seeder (misal: `PostSeeder`, `MediaSeeder`) hanya jika `app()->isLocal()`.
- **Admin Security**: Pastikan role `admin` tidak diberikan izin `Force Delete` di seeder untuk keamanan data produksi.
