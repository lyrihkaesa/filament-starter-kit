# Rencana Fitur Starter Kit

Dokumen ini merangkum dua hal:
1. **Apa yang bisa di-improve selanjutnya** (prioritas next).
2. **Fitur apa saja yang sudah tersedia saat ini** (baseline saat ini).

## 1) Next Improvement (Atas)

### 1. Audit Trail / Activity Log
**Kenapa penting:**
- Saat aplikasi mulai dipakai tim, kita butuh jejak perubahan data (`siapa`, `kapan`, `apa yang berubah`).
- Membantu debugging issue production dan kebutuhan compliance.

**Ruang lingkup awal:**
- Log perubahan pada modul inti: `User`, `Role`, `Post`, dan media.
- Tampilkan log di Filament (table + filter actor/tanggal/modul).

### 2. Multi-tenancy Ready (Team/Tenant Scope)
**Kenapa penting:**
- Membuka use case SaaS B2B dari starter kit yang sama.
- Mengurangi risiko kebocoran data antar tenant sejak desain awal.

**Ruang lingkup awal:**
- Tambah `tenant_id`/`team_id` pada entitas utama.
- Scope query default + policy berbasis tenant.
- Seeder + test isolasi data antar tenant.

### 3. OpenAPI Contract + Contract Test
**Kenapa penting:**
- API sudah ada (`api/v1`), tapi kontrak formal mempercepat integrasi mobile/frontend.
- Mengurangi regression response shape saat refactor.

**Ruang lingkup awal:**
- Publish spec OpenAPI untuk auth, users, uploads.
- Test validasi response terhadap schema utama.

### 4. Feature Flags + Dynamic Settings
**Kenapa penting:**
- Rollout fitur bisa bertahap tanpa deploy berkali-kali.
- Memudahkan matikan fitur bermasalah secara cepat.

**Ruang lingkup awal:**
- Settings page di Filament.
- Flag global (dan opsional per tenant).

### 5. Security Hardening Pack
**Kenapa penting:**
- Starter kit sering dipakai sebagai fondasi production, jadi security baseline harus kuat dari awal.

**Ruang lingkup awal:**
- 2FA untuk akun admin.
- Rate limit granular endpoint sensitif (login, upload prepare, restore flow).
- Penguatan validasi upload dan lifecycle signed URL.

### 6. Generator Modul v2 (Scaffolding Lengkap)
**Kenapa penting:**
- Konsistensi arsitektur akan lebih terjaga jika boilerplate penting dibuat otomatis.
- Menghemat waktu onboarding developer baru.

**Ruang lingkup awal:**
- Upgrade `make:starter-resource` agar menghasilkan:
  - Resource + Form + Table + Pages
  - Action class CRUD
  - Form Request
  - Policy dasar
  - Test starter (Feature + Unit)

## 2) Fitur Yang Sudah Ada (Bawah)

Berikut baseline fitur yang saat ini sudah tersedia di starter kit:

1. **Arsitektur Action Pattern**
- Mutasi data dipisah ke `app/Actions/*` agar UI layer tetap tipis.

2. **Filament Admin Panel**
- Resource utama seperti `User` dan `Post` sudah ada.
- Halaman auth Filament (login/register/restore/edit profile) sudah tersedia.

3. **Role & Permission**
- Integrasi Filament Shield + seeder role/permission.
- Policy untuk modul inti sudah ada dan dites.

4. **API v1 + Sanctum**
- Endpoint auth, users, upload dengan struktur `routes/api/v1.php`.
- Resource response API (`UserResource`, `UserCollection`) sudah disiapkan.

5. **Strategi Upload File**
- Dukungan flow upload (prepare, mark uploaded, get upload).
- Integrasi Curator + tracking usage media.

6. **User Lifecycle & Privacy**
- Soft delete dan alur anonymization/restoration account.
- Aksi profile seperti update password dan revoke session/device.

7. **UUID-first Design**
- Migrasi dan model sudah mengarah ke UUID sebagai pondasi entity.

8. **Quality Toolchain Lengkap**
- Pest, Pint, PHPStan (Larastan), Rector sudah terintegrasi.
- Script `composer test`, `test:types`, `test:lint`, `test:refactor` tersedia.

9. **Testing Coverage Luas**
- Feature test, unit test, policy test, browser test sudah ada.
- Arsitektur test dan coverage flow sudah terdokumentasi.

10. **Operational Support**
- Spatie Laravel Backup sudah terpasang.
- Debugbar tersedia untuk observasi query/performance saat development.

11. **Starter Commands**
- Command custom seperti `make:starter-resource`, `make:action`, dan cleanup upload sudah tersedia.

12. **Dokumentasi Berlapis**
- Dokumentasi dari intro, arsitektur, API, upload, testing, sampai deployment sudah lengkap di `docs/`.

---

## Rekomendasi Prioritas Implementasi

Jika ingin bertahap dan paling terasa dampaknya:
1. **Audit Trail** (impact operasional cepat).
2. **Generator Modul v2** (impact ke kecepatan development harian).
3. **OpenAPI Contract** (impact ke stabilitas integrasi API).
