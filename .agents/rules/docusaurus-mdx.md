---
trigger: model_decision
description: when creating, modifying, or reviewing markdown or documentation files in docs/ or articles/ (Docusaurus and MDX compliance)
---

# Pedoman Penulisan Dokumentasi Markdown untuk Docusaurus (MDX Compliance)

Dokumen ini adalah aturan wajib saat membuat atau mengedit file dokumentasi (`.md` / `.mdx`) agar kompatibel dengan compiler Docusaurus (MDX v2/v3) dan tidak menyebabkan build error.

---

## 1. Aturan Karakter Kurung Sudut (`<` dan `>`)
Compiler MDX menganggap kurung sudut buka `<` sebagai awal dari komponen tag JSX/HTML.

### A. Simbol Panah
* ❌ **JANGAN:** Menulis panah teks biasa seperti `<->`, `<-`, atau `->` di luar code block.
* ✅ **GUNAKAN:** Simbol panah Unicode atau bungkus dalam backtick:
  * `↔` atau `` `<->` `` (dua arah)
  * `→` atau `` `->` `` (ke kanan)
  * `←` atau `` `<-` `` (ke kiri)

### B. Placeholder / Variabel Generik
* ❌ **JANGAN:** Menulis placeholder telanjang seperti `<id>`, `<uuid>`, `<nama_tabel>`.
* ✅ **GUNAKAN:** Bungkus selalu dengan backtick (*inline code*):
  * Gunakan `` `<id>` `` atau `` `<uuid>` ``
  * Atau gunakan huruf kapital: `[ID]`, `[UUID]`

### C. Operator Perbandingan Matematika
* ❌ **JANGAN:** `jika nilai < 10 dan > 0`
* ✅ **GUNAKAN:** `` `nilai < 10` `` atau gunakan entitas HTML: `&lt;` dan `&gt;`

---

## 2. Aturan Kurung Kurawal (`{` dan `}`)
Di MDX, kurung kurawal di luar code block dianggap sebagai kode JavaScript (*expression evaluator*).

* ❌ **JANGAN:** Menulis format URL atau placeholder telanjang seperti:
  * `POST /api/v1/uploads/{id}/mark-uploaded` (di luar backtick)
* ✅ **GUNAKAN:** Bungkus selalu dalam backtick:
  * `` `POST /api/v1/uploads/{id}/mark-uploaded` ``
  * Atau jika dalam teks biasa, beri escape: `\{id\}`

---

## 3. Komentar Dokumen
* ❌ **JANGAN:** Menggunakan komentar HTML standar:
  * `<!-- Komentar ini akan menyebabkan error MDX -->`
* ✅ **GUNAKAN:** Komentar gaya JSX:
  * `{/* Komentar MDX yang valid */}`

---

## 4. Tag HTML / Elemen Void Harus Self-Closing
Jika menulis tag HTML langsung di dalam Markdown:
* ❌ **JANGAN:** `<br>`, `<img>`, `<hr>`, `<input>`
* ✅ **GUNAKAN:** `<br />`, `<img src="..." />`, `<hr />`

---

## 5. Blok Kode (Code Blocks)
* Semua kode contoh (PHP, Bash, JSON, Flutter/Dart) **harus selalu dibungkus** dengan *triple backticks* dan menyertakan identifier bahasa:
  ````markdown
  ```php
  public function handle(array $data): User
  {
      // ...
  }
  ```
  ````
* Karakter khusus di dalam blok kode (seperti `<->`, `{...}`, `<T>`) **100% aman** dan tidak akan diproses oleh parser JSX MDX.

---

## Ringkasan Cepat (Cheat Sheet)

| Yang Ingin Ditulis | ❌ Salah (Bikin Error) | ✅ Benar (Aman MDX) |
| :--- | :--- | :--- |
| Panah alur | `Flutter <-> Laravel` | `Flutter ↔ Laravel` atau `` `Flutter <-> Laravel` `` |
| Placeholder path | `/users/{id}/profile` | `` `/users/{id}/profile` `` |
| Tipe data generik | `Collection<User>` | `` `Collection<User>` `` |
| Kurang dari / lebih dari | `skor < 50` | `` `skor < 50` `` atau `skor &lt; 50` |
| Baris baru HTML | `<br>` | `<br />` |
| Komentar draft | `<!-- todo -->` | `{/* todo */}` |
