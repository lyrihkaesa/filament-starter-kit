# Audit Trail (Activity Log)

Starter Kit ini menggunakan **Spatie Laravel Activitylog v5** sebagai engine pendata aktivitas (audit trail). Fitur ini telah ditingkatkan untuk menggunakan implementasi **Native Filament Resource** guna fleksibilitas dan kompatibilitas jangka panjang dengan Laravel 12.

## Fitur Utama

- **Pelacakan Otomatis Model**: Mencatat event `created`, `updated`, dan `deleted` pada model yang aktif.
- **Native Activity Resource**: Dashboard pusat untuk melihat seluruh log aktivitas di `/app/activities`.
- **Filtered Timeline**: Tombol "Activities" pada tabel user memungkinkan Anda melihat log spesifik untuk user tersebut dengan satu klik.
- **Batching & Events**: Mendukung sistem batching v5 untuk melacak rangkaian perubahan yang berkaitan.

## Cara Penggunaan untuk Model Baru

Jika Anda ingin mengaktifkan Audit Trail pada model baru (contoh: `Post`):

### 1. Tambahkan Trait di Model
Gunakan trait `HasActivity` dari Spatie:

```php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\HasActivity;

class Post extends Model
{
    use HasActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logUnguarded() // Mencatat semua field yang diubah
            ->logOnlyDirty()  // Hanya catat field yang benar-benar berubah
            ->dontLogEmptyChanges(); // Jangan buat log jika tidak ada perubahan
    }
}
```

### 2. Tambahkan Link di Tabel Resource
Di file konfigurasi tabel (contoh: `PostsTable.php`), tambahkan action untuk memfilter log:

```php
use App\Filament\Resources\Activities\ActivityResource;
use Filament\Tables\Actions\Action;

Action::make('activities')
    ->label(__('Activities'))
    ->icon('heroicon-o-clock')
    ->url(fn (Post $record): string => ActivityResource::getUrl('index', [
        'tableFilters[subject_type][value]' => get_class($record),
        'tableSearch' => $record->id,
    ])),
```

## Dashboard Global
Anda dapat mengakses seluruh log aktivitas melalui menu **Activities** di sidebar panel admin. Log ini mencakup informasi mengenai:
- **Log Time**: Kapan aktivitas terjadi.
- **Causer**: Siapa yang melakukan perubahan (User atau System).
- **Event**: Jenis aktivitas (created, updated, deleted).
- **Subject**: Model apa yang diubah.
- **Description**: Detail singkat mengenai perubahan tersebut.

## Referensi
- [Spatie Activitylog v5 Documentation](https://spatie.be/docs/laravel-activitylog/v5/introduction)
