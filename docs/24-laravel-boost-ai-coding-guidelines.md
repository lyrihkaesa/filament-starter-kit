# Laravel Boost AI Coding Guidelines

Dokumen ini menggantikan panduan AI umum yang lama karena saya tidak ingin isinya bentrok atau ambigu dengan:

- [`GEMINI.md`](https://github.com/lyrihkaesa/filament-starter-kit/blob/main/GEMINI.md)
- aturan dan skill di folder [` .agents`](https://github.com/lyrihkaesa/filament-starter-kit/tree/main/.agents)

Fokus dokumen ini bukan membahas "semua AI tool secara umum", tetapi menjelaskan bagaimana **Laravel Boost** dipakai di starter kit ini dan bagaimana cara berpikir saat bekerja dengan bantuan AI di dalam project.

## Apa Itu Laravel Boost

Laravel Boost adalah package yang membantu AI coding assistant memahami konteks project Laravel dengan lebih baik.

Di project ini, Boost dipakai supaya AI tidak bekerja secara buta. AI diberi konteks tentang:

- versi package yang terpasang
- konvensi project
- skill yang tersedia
- aturan kerja yang relevan untuk Laravel ecosystem

Tujuannya sederhana: output AI harus lebih tepat, lebih relevan dengan codebase, dan lebih kecil peluangnya melenceng dari pola starter kit ini.

## File yang Terkait dengan Boost di Project Ini

### `boost.json`

File [`boost.json`](https://github.com/lyrihkaesa/filament-starter-kit/blob/main/boost.json) adalah konfigurasi ringkas untuk Boost di project ini.

Contoh isinya saat ini:

```json
{
    "agents": [
        "gemini"
    ],
    "guidelines": true,
    "herd_mcp": true,
    "mcp": true,
    "nightwatch_mcp": false,
    "packages": [
        "filament/filament"
    ],
    "sail": false,
    "skills": [
        "livewire-development",
        "pest-testing",
        "tailwindcss-development"
    ]
}
```

### `GEMINI.md`

File [`GEMINI.md`](https://github.com/lyrihkaesa/filament-starter-kit/blob/main/GEMINI.md) berisi guideline utama yang dibaca agent seperti Gemini ketika bekerja dengan Laravel Boost.

Di sana ada aturan penting seperti:

- versi Laravel dan package yang dipakai
- skill yang harus diaktifkan
- aturan test
- aturan struktur project
- aturan penggunaan Laravel way

### Folder `.agents`

Folder [` .agents`](https://github.com/lyrihkaesa/filament-starter-kit/tree/main/.agents) menyimpan skill dan aturan tambahan yang dipakai agent.

Contohnya:

- `livewire-development`
- `pest-testing`
- `tailwindcss-development`

Artinya, kalau Anda meminta AI mengerjakan tugas tertentu, AI tidak hanya mengandalkan "pengetahuan umum", tetapi juga bisa diarahkan oleh skill yang memang dibuat untuk domain tersebut.

## Cara Kerja Laravel Boost di Project Ini

Secara sederhana, alurnya seperti ini:

1. AI membaca konteks project
2. Boost memberi tahu package dan versi yang terpasang
3. Boost mengaktifkan guideline yang relevan
4. AI mengikuti aturan dari `GEMINI.md` dan skill yang tersedia
5. AI menghasilkan saran atau perubahan kode yang lebih selaras dengan starter kit ini

Jadi Boost bukan "alat yang menulis kode sendirian", tetapi lapisan konteks yang membantu AI bekerja lebih tepat.

## Kenapa Ini Penting

Tanpa konteks seperti ini, AI sering menghasilkan hal-hal seperti:

- contoh kode yang tidak cocok dengan versi Laravel
- pola yang tidak sesuai dengan struktur project
- saran testing yang tidak sesuai dengan Pest
- implementasi UI yang tidak cocok dengan Filament atau Livewire project ini
- style coding yang bertabrakan dengan kebiasaan tim

Dengan Boost, AI lebih tahu:

- project ini Laravel 12
- Filament yang dipakai versi 5
- Livewire yang dipakai versi 4
- testing memakai Pest
- starter kit ini memakai Action Pattern dengan `handle()`

## Cara Berpikir Saat Menggunakan Boost

Boost membantu AI, tetapi tidak menggantikan tanggung jawab developer.

Jadi cara pakainya sebaiknya seperti ini:

### 1. Gunakan AI sebagai akselerator, bukan sumber kebenaran terakhir

Kalau AI memberi saran kode, tetap review:

- apakah logic-nya benar
- apakah mengikuti pola starter kit
- apakah authorization sudah tepat
- apakah ada test yang perlu ditambah

### 2. Beri konteks tugas dengan jelas

Semakin jelas konteks yang Anda berikan, semakin baik hasil Boost + AI.

Contoh konteks yang bagus:

- model apa yang sedang dikerjakan
- flow apa yang ingin dibangun
- apakah ini Filament, API, atau action class
- apakah butuh test

### 3. Pahami lapisan tanggung jawab

Misalnya:

- Filament Action untuk UI trigger
- Action Pattern untuk business logic
- FormRequest untuk validation dan authorization
- Policy untuk akses

Boost membantu AI lebih peka terhadap pola ini, tetapi tetap harus dijaga oleh developer.

## Skill yang Aktif di Project Ini

Berdasarkan `boost.json`, skill yang saat ini dipakai adalah:

- `livewire-development`
- `pest-testing`
- `tailwindcss-development`

### Apa artinya

Kalau AI mengerjakan task terkait Livewire, testing Pest, atau Tailwind, hasilnya seharusnya lebih baik karena ada instruksi tambahan yang spesifik untuk domain itu.

## Contoh Cara Kerja Nyata

### Studi kasus 1: Membuat test Pest

Kalau Anda meminta AI menulis test untuk action atau feature, Boost akan membantu memberi konteks bahwa project ini memakai:

- Pest
- Laravel 12
- pola testing yang sesuai project

Lalu skill `pest-testing` ikut membantu mempersempit gaya solusi yang diberikan.

### Studi kasus 2: Bekerja dengan Livewire atau Filament

Kalau task menyangkut Livewire, agent tidak seharusnya asal memberi solusi ala React atau Vue. Boost dan skill project membantu AI tetap berada di jalur yang sesuai dengan stack sebenarnya.

### Studi kasus 3: Styling dengan Tailwind

Kalau task menyangkut Tailwind, skill terkait akan membantu AI tetap fokus pada utility class dan struktur frontend yang sesuai konteks project ini.

## Hubungan Boost dengan `GEMINI.md`

`GEMINI.md` adalah dokumen aturan utama yang sangat penting untuk Boost di project ini.

Di file itu dijelaskan hal-hal seperti:

- package dan versi
- aturan penggunaan Laravel way
- aturan testing
- aturan struktur aplikasi
- aturan penggunaan skill

Jadi kalau Anda bertanya "Boost itu kerjanya dari mana?", salah satu jawabannya adalah:

> Boost bekerja dengan membawa konteks project, lalu guideline seperti `GEMINI.md` membantu mengarahkan perilaku agent.

## Hubungan Boost dengan `.agents`

Kalau `GEMINI.md` adalah guideline utama, maka `.agents` adalah tempat skill dan aturan tambahan yang lebih spesifik.

Dengan kata lain:

- `boost.json` = konfigurasi ringkas fitur Boost
- `GEMINI.md` = aturan utama agent
- `.agents` = skill dan aturan pendukung yang lebih spesifik

Ketiganya saling melengkapi.

## Apa yang Perlu Developer Lakukan

Kalau Anda memakai Boost di project ini, beberapa kebiasaan yang saya sarankan:

- tetap tulis permintaan dengan jelas
- tetap review hasil AI
- tetap cek apakah hasilnya sesuai dengan Action Pattern project ini
- tetap pastikan test ada jika perubahan menyentuh behavior penting

Boost meningkatkan kualitas konteks, tetapi kualitas hasil akhir tetap bergantung pada cara kita memakainya.

## Kesimpulan

Laravel Boost di starter kit ini berfungsi sebagai lapisan konteks untuk AI coding assistant supaya:

- lebih paham stack yang dipakai
- lebih paham aturan project
- lebih konsisten dengan skill dan guideline yang tersedia

Dokumen ini sengaja dibuat spesifik ke Boost agar tidak tumpang tindih dengan dokumen aturan agent lain. Jadi kalau developer ingin memahami "kenapa AI di project ini bisa lebih terarah", file inilah titik awalnya.
