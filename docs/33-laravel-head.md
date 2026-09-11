# Laravel Head (Document Head & SEO Management)

Starter Kit ini menggunakan **[laravel/head](https://laravel.com/docs/head)** sebagai solusi resmi untuk mengelola elemen `<head>` dokumen HTML secara lancar (*fluent*), termasuk template judul halaman, deskripsi SEO, OpenGraph metadata, URL kanonikal (*canonical*), direktif robots, dan JSON-LD schema.

---

## Kenapa Menggunakan `laravel/head`

Sebelumnya, manajemen `<head>` di aplikasi Blade/Livewire sering kali berantakan:
- Tag `<title>` ditulis manual dengan conditional string yang rentan inkonsisten.
- Penambahan meta tag OpenGraph dan canonical URL membutuhkan banyak `@stack('head')` atau `@yield('meta')` yang sulit dipelihara.
- Integrasi SPA / `wire:navigate` membutuhkan penanganan sinkronisasi head secara manual.

Dengan `laravel/head`:
1. **Hirarki Metadata Terstruktur**: Otomatis menggabungkan *defaults* aplikasi, *route metadata*, dan *runtime metadata* per-halaman.
2. **Template Judul Pintar**: Jika tidak disetel, judul default tampil apa adanya. Saat halaman menentukan judul spesifik, suffix nama aplikasi (` - AppName`) otomatis ditambahkan.
3. **Mendukung Livewire & Blade**: Cukup satu direktif `@head` di `<head>` layout dokumen.

---

## 1. Konfigurasi Global Defaults (`AppServiceProvider`)

Nilai default untuk seluruh aplikasi didaftarkan pada method `boot()` di `AppServiceProvider`:

```php
use Laravel\Head\Enums\OgType;
use Laravel\Head\Facades\Head;
use Laravel\Head\HeadBuilder;

Head::defaults(function (HeadBuilder $head): void {
    $appName = config()->string('app.name', 'Laravel');

    $head
        ->title($appName, suffix: " - {$appName}")
        ->description('Filament Starter Kit for Laravel with best practices')
        ->canonical()
        ->og(type: OgType::Website, siteName: $appName)
        ->searchableByRobots();
});
```

Dengan konfigurasi di atas:
- Default title: `Filament Starter Kit`.
- Jika halaman anak memanggil `Head::title('Daftar Artikel')`, output title menjadi: `Daftar Artikel - Filament Starter Kit`.
- Canonical URL otomatis mengikuti URL request aktif.
- Direktif robots default disetel `all` (`<meta name="robots" content="all">`).

---

## 2. Pemasangan di Layout Blade

Cukup pasang direktif `@head` di dalam elemen `<head>` pada layout:

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @isset($title)
            @php(\Laravel\Head\Facades\Head::title($title))
        @endisset
        @head

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        {{ $slot }}
    </body>
</html>
```

Layout starter kit yang sudah mengintegrasikan `@head`:
- `resources/views/welcome.blade.php`
- `resources/views/layouts/posts.blade.php`

---

## 3. Menentukan Metadata di Komponen Livewire

Pada komponen Livewire, Anda dapat menyetel metadata dinamis di method `mount()` atau `render()` menggunakan Facade `Head`:

```php
declare(strict_types=1);

namespace App\Livewire\Posts;

use App\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Laravel\Head\Enums\OgType;
use Laravel\Head\Facades\Head;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.posts')]
final class Show extends Component
{
    public Post $post;

    public function mount(Post $post): void
    {
        $this->post = $post->loadMissing(['author', 'thumbnailCurator']);

        $description = Str::limit(strip_tags($this->post->content), 160);

        Head::title($this->post->title)
            ->description($description)
            ->canonical(route('posts.show', $this->post))
            ->og(
                type: OgType::Article,
                title: $this->post->title,
                description: $description,
                url: route('posts.show', $this->post),
                image: $this->post->thumbnailCurator?->url,
            );
    }

    public function render(): View
    {
        return view('livewire.posts.show');
    }
}
```

---

## 4. Menentukan Metadata via Route

Untuk halaman statis atau controller standar, metadata juga bisa didefinisikan langsung pada route:

```php
use Illuminate\Support\Facades\Route;

Route::get('/tentang-kami', AboutController::class)
    ->withHead(
        title: 'Tentang Kami',
        description: 'Pelajari visi dan misi kami.',
    );
```

---

## 5. Menulis Test dengan Pest

Untuk memastikan tag `<head>` ter-render dengan benar, gunakan helper bawaan Laravel `assertSeeHtml()` pada respons HTTP:

```php
declare(strict_types=1);

use App\Models\Post;

it('renders post specific head meta tags on post detail page', function (): void {
    $post = Post::factory()->create([
        'title' => 'Building with Laravel Head',
        'content' => 'Panduan lengkap optimasi SEO head.',
        'published_at' => now(),
    ]);

    $appName = config()->string('app.name', 'Laravel');

    $this->get(route('posts.show', ['post' => $post->slug]))
        ->assertSuccessful()
        ->assertSeeHtml("<title>Building with Laravel Head - {$appName}</title>")
        ->assertSeeHtml('property="og:type" content="article"')
        ->assertSeeHtml('name="description" content="Panduan lengkap optimasi SEO head."');
});
```

Semua pengujian terkait dapat dilihat di `tests/Feature/HeadMetadataTest.php`.
