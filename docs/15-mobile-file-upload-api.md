# Mobile File Upload API (Flutter <-> Laravel)

Dokumen ini menjelaskan alur kerja (workflow) untuk mengunggah file dari aplikasi Mobile (Flutter) ke server Laravel menggunakan **Pre-Signed URL** serta cara menghubungkannya dengan model utama.

---

## Perbandingan Parameter Request
Untuk mendukung fitur "Upload Baru" sekaligus "Pilih dari Gallery (Media Lama)", API menggunakan dua parameter berbeda:

1.  **`avatar_upload_id`**: Digunakan saat Flutter selesai melakukan upload file baru melalui jalur Pre-signed URL. Berisi UUID dari tabel `temporary_uploads`.
2.  **`avatar_media_id`**: Digunakan saat user memilih foto yang sudah pernah diunggah sebelumnya (Gallery). Berisi ID dari tabel `curator_media`.

---

## Alur Kerja (Step-by-Step)

### 1. Tahap Persiapan (Prepare)
Flutter meminta URL upload.
- **Endpoint:** `POST /api/v1/uploads/prepare`
- **Respons:** Mendapatkan `upload_id` dan `upload_url`.

### 2. Tahap Unggah Fisik (Direct Upload)
Flutter melakukan `PUT` binary file ke `upload_url`.

### 3. Tahap Penandaan Selesai (Mark Uploaded)
Flutter memanggil `POST /api/v1/uploads/{upload_id}/mark-uploaded`.

### 4. Tahap Finalisasi (Penyimpanan ke Model)
Saat menyimpan data (misal: Update Profile), Flutter mengirimkan salah satu ID di atas.

**Contoh Payload Request:**
```json
{
  "name": "Budi",
  "avatar_upload_id": "9b1e... (UUID dari tahap 1)"
}
```

---

## Implementasi Backend (Action Pattern)

Sesuai dengan **Action Pattern** di proyek ini, Action tidak boleh menerima instance `Request`. Controller bertugas mengekstrak data dari request dan meneruskannya ke Action dalam bentuk array `$data`.

### Logika di Controller (`AuthController.php`)
```php
public function update(UpdateProfileRequest $request, UpdateProfileAction $action)
{
    // Controller mengekstrak data dari request
    $data = $request->validated();

    // Jalankan action dengan array data
    $user = $action->handle(auth()->user(), $data);

    return new UserResource($user);
}
```

### Logika di Action (`UpdateProfileAction.php`)
Action akan mendeteksi apakah ada `upload_id` yang perlu difinalisasi menjadi Media Curator.

```php
public function handle(User $user, array $data): User
{
    // 1. Cek apakah ada upload baru yang harus difinalisasi
    if (!empty($data['avatar_upload_id'])) {
        // Panggil Action lain untuk konversi TemporaryUpload -> Curator Media
        $media = $this->finalizeUploadAction->handle([
            'upload_id' => $data['avatar_upload_id'],
            'user_id' => $user->id,
        ]);
        
        $data['avatar_id'] = $media->id;
    } 
    // 2. Jika user memilih dari media yang sudah ada
    elseif (!empty($data['avatar_media_id'])) {
        // Validasi kepemilikan media bisa dilakukan di FormRequest atau di sini
        $data['avatar_id'] = $data['avatar_media_id'];
    }

    // 3. Update model user dengan avatar_id (ID Curator) yang sudah fix
    $user->update(Arr::only($data, ['name', 'avatar_id', 'locale', 'timezone']));

    return $user;
}
```

---

## Keuntungan Bagi Developer
1. **CLI Ready:** Action ini bisa dipanggil dari Command Line atau Seeder dengan mudah karena hanya butuh array.
2. **Flexible UI:** User bisa upload foto baru atau sekadar mengganti avatar dengan foto lama (seperti fitur di Facebook/Google).
3. **Decoupled:** Logika konvensional file biner tidak mengotori logika update profile user.
