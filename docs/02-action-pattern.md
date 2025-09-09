# Action Pattern `make:action` - CQRS

Karena laravel belum ada artisan command untuk membuat action saya buat secara manual terlebih dahulu.
Command `php artisan make:action` adalah perintah kustom untuk membantu developer membuat **Action Class** di folder `app/Actions`, baik untuk kebutuhan **CRUD berbasis model** maupun **custom action**.

---

## ✨ Fitur Utama

-   ✅ Generate action class untuk **Create / Update / Delete**
-   ✅ Custom path (sub-folder) untuk struktur folder yang rapi
-   ✅ Interaktif jika tanpa parameter
-   ✅ Auto-create seluruh CRUD action jika tidak spesifik
-   ✅ Dukungan `--force` untuk overwrite file
-   ✅ Prompt konfirmasi jika file sudah ada dan tidak diberi `--force`

---

## 🛠️ Penggunaan Dasar

### 1. Generate Custom Action

```bash
php artisan make:action
```

> Maka akan muncul prompt:

```
What is the class name? (e.g., Posts/PublishPostAction):
> Posts/PublishPostAction
```

Hasilnya:

```
app/Actions/Posts/PublishPostAction.php
```

---

### 2. Generate CRUD Action untuk Model

```bash
php artisan make:action --model=User
```

Hasil:

```
app/Actions/Users/CreateUserAction.php
app/Actions/Users/UpdateUserAction.php
app/Actions/Users/DeleteUserAction.php
```

---

### 3. Custom Sub-Folder (opsional)

```bash
php artisan make:action --model=User --sub-folder=Access
```

Hasil:

```
app/Actions/Access/CreateUserAction.php
app/Actions/Access/UpdateUserAction.php
app/Actions/Access/DeleteUserAction.php
```

---

### 4. Generate Spesifik Action

```bash
php artisan make:action --model=User --create
php artisan make:action --model=User --update
php artisan make:action --model=User --delete
```

---

### 5. Overwrite File dengan Paksa

```bash
php artisan make:action --model=User --force
```

Tanpa `--force`, jika file sudah ada maka akan muncul prompt:

```
File [app/Actions/Users/CreateUserAction.php] already exists. Overwrite? (yes/no) [no]:
```

---

## 🧩 Stub File

Stub disimpan di:

```
stubs/
├── create-action.stub
├── update-action.stub
├── delete-action.stub
└── custom-action.stub
```

Jika tidak ditemukan, fallback ke `__DIR__.'/../../../stubs/'`.

---

## 🔧 Argument dan Opsi

| Opsi               | Alias | Keterangan                                               |
| ------------------ | ----- | -------------------------------------------------------- |
| `name`             |       | Nama action class (hanya untuk custom action)            |
| `--model=Post`     | `-m`  | Nama model (akan mengenerate action dari model tersebut) |
| `--create`         |       | Hanya generate `Create<Model>Action`                     |
| `--update`         |       | Hanya generate `Update<Model>Action`                     |
| `--delete`         |       | Hanya generate `Delete<Model>Action`                     |
| `--sub-folder=XYZ` | `-s`  | Sub-folder dalam `app/Actions` (opsional)                |
| `--force`          | `-f`  | Overwrite file jika sudah ada                            |

---

## 💡 Contoh Praktik

### Membuat Action `ApproveOrderAction` di folder `Orders`

```bash
php artisan make:action
# Prompt: Orders/ApproveOrderAction
```

### Membuat seluruh CRUD untuk model `Invoice` di `app/Actions/Billing`

```bash
php artisan make:action --model=Invoice --sub-folder=Billing
```

---

## 📁 Struktur Folder Hasil

```
app/
└── Actions/
    ├── Users/
    │   ├── CreateUserAction.php
    │   ├── UpdateUserAction.php
    │   └── DeleteUserAction.php
    └── Posts/
        └── PublishPostAction.php
```

---

## 🧠 Best Practice

-   Gunakan `--sub-folder` untuk struktur domain-based (misal `Billing`, `Auth`, `Access`)
-   Simpan action yang bukan CUD di folder khusus (misal `Posts/PublishPostAction`)
-   Pisahkan validasi & otorisasi ke FormRequest atau Policy untuk menjaga SRP
