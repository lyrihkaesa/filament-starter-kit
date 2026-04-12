# Curator Media Delete Guard - Implementation Log

## Context

Permintaan:
- Tambah test upload media Curator dan test delete media Curator via Pest Browser.
- Cegah user biasa menghapus media yang masih dipakai.
- Tombol delete harus disabled jika media masih digunakan.
- Admin / permission khusus boleh override.
- Action Pattern harus tetap netral permission (authorization bukan di `app/Actions/**`).

## Implemented

### Authorization Boundary

- Refactor `DeleteCuratorMediaAction` agar tidak melakukan permission check.
- Authorization dipusatkan di:
  - `CuratorMediaPolicy`
  - Filament Curator actions (`CuratorMediaDeleteAction`, `CuratorMediaDeleteBulkAction`)

### Delete In-Use Rule

- Default: media in-use tidak bisa dihapus.
- Override: boleh dihapus jika punya permission khusus atau global.
- Permission tambahan:
  - `DeleteUsed:CuratorMedia`
  - `ForceDeleteUsed:CuratorMedia`

### UI Guard

- Tombol delete pada halaman edit media disabled untuk user tanpa override.
- Row delete / bulk delete di media table menolak item in-use untuk user tanpa override.
- Warning/tooltip ditampilkan agar alasan block jelas.

### Curator Config

- Set edit page Curator ke page custom project:
  - `resource.pages.edit = App\Filament\Pages\Media\EditMedia`

## Tests

### Added

- `tests/Browser/CuratorMediaManagementTest.php`

### Updated

- `tests/Unit/Policies/CuratorMediaPolicyTest.php`
- `tests/Unit/Filament/Curator/CuratorMediaActionsAndTableTest.php`
- `tests/Feature/CuratorMediaTest.php`
- `tests/Feature/Filament/CuratorMediaResourceTest.php`
- `tests/Feature/MediaUsageTest.php`

### Result

- Semua test terkait skenario ini: **passed**.

## Files Changed (Implementation)

- `app/Actions/Media/DeleteCuratorMediaAction.php`
- `app/Filament/Curator/Actions/CuratorMediaDeleteAction.php`
- `app/Filament/Curator/Actions/CuratorMediaDeleteBulkAction.php`
- `app/Policies/CuratorMediaPolicy.php`
- `config/curator.php`

## Files Changed (Rules & Docs)

- `.agents/rules/action-pattern.md`
- `.agents/rules/roles-permissions.md`
- `docs/12-filament-curator.md`
- `docs/13-curator-privacy-and-tracking.md`
- `docs/28-curator-delete-authorization-boundary.md`
