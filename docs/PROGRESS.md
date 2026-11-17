# Simulasi ERP — Catatan Progres Antar-Sesi

**Tahap Aktif:** Tahap 1 — Fondasi (**Selesai & Terverifikasi di PostgreSQL**)  
**Tahap Berikutnya:** **Alur Inti** (Pesanan pelanggan, reservasi stok, PO antar-PT, pengadaan bahan, BOM, produksi, dan QC) — *belum dimulai hingga diminta lanjut.*

---

## 1. Status Implementasi vs Verifikasi

| Komponen / Fitur | Status Implementasi | Status Verifikasi | Catatan |
|---|---|---|---|
| Proyek Laravel 13 + Blade + Tailwind CSS v4 + PostgreSQL | Selesai | Terverifikasi | `npm run build` sukses (162 ms), `php artisan view:cache` sukses, `route:list` 27 route valid |
| Format Indonesia (`Rp`, kuantitas desimal, `Asia/Jakarta` WIB) | Selesai | Teruji (Unit Test) | `tests/Unit/IndonesianFormatterTest.php` lulus (3 test) |
| Enum Role (8 role), kompatibilitas tipe PT, & aturan visibilitas harga pelanggan | Selesai | Teruji (Unit Test) | `tests/Unit/UserRoleTest.php` lulus (2 test) |
| Migrasi & Seeder Fondasi pada PostgreSQL (`simulasi_erp`) | Selesai | Terverifikasi di PostgreSQL | 13 migrasi sukses; seeder idempoten menghasilkan 3 PT, 12 akun demo, 3 produk, 3 gudang, 8 rak, 3 lot, 3 saldo awal, 3 pergerakan awal |
| Autentikasi login/logout, `EnsureActiveCompany`, & ganti PT aktif (`POST /konteks-pt`) | Selesai | Teruji di PostgreSQL | `tests/Feature/AuthenticationAndActiveCompanyTest.php` lulus di `simulasi_erp_test` |
| Pembatasan akses antar-PT, URL langsung (`403`), & blokir harga jual pelanggan untuk Petugas Logistik (AC-06) | Selesai | Teruji di PostgreSQL | `tests/Feature/CrossCompanyAccessControlTest.php` lulus di `simulasi_erp_test` |
| Pemisahan pemilik stok vs pengelola gudang (300 kotak Distribusi di gudang Logistik) & pindah rak (`StockLedgerService`) | Selesai | Teruji di PostgreSQL | `tests/Feature/OwnershipAndStockLedgerTest.php` lulus di `simulasi_erp_test` |
| Pengelolaan Perusahaan, Pengguna, Keanggotaan/Role, Audit Log, & Ringkasan Lintas PT | Selesai | Teruji di PostgreSQL | `tests/Feature/AdminGroupManagementTest.php` lulus di `simulasi_erp_test` |
| Verifikasi interaktif manual lewat browser lokal | Selesai (kode & UI) | Belum diverifikasi manual di browser | Seluruh template Blade terkompilasi & diuji lewat Feature test HTTP; review UI interaktif di browser diserahkan ke sesi review pengguna |

---

## 2. File dan Modul Terkait (Tahap 1 — Fondasi)

- **Konfigurasi & Dokumentasi:**
  - `.env.example`, `.gitignore`, `phpunit.xml`, `config/app.php`, `config/database.php`
  - [docs/PRD.md](PRD.md), [README.md](../README.md), [docs/PROGRESS.md](PROGRESS.md)
- **Helper & Enum Domain:**
  - `app/Support/IndonesianFormatter.php`
  - `app/Enums/CompanyCode.php`, `CompanyBusinessType.php`, `UserRole.php`, `ProductCategory.php`, `RackZoneType.php`, `LotSourceType.php`, `StockStatus.php`, `StockMovementType.php`
- **Migrasi & Model:**
  - `database/migrations/2026_11_18_000453_add_is_active_to_users_table.php` s.d. `2026_11_18_000502_create_audit_logs_table.php`
  - `app/Models/User.php`, `Company.php`, `CompanyMembership.php`, `Product.php`, `Warehouse.php`, `WarehouseRack.php`, `Lot.php`, `StockBalance.php`, `StockMovement.php`, `AuditLog.php`
  - `database/factories/UserFactory.php`, `CompanyFactory.php`, `ProductFactory.php`, `WarehouseFactory.php`, `WarehouseRackFactory.php`, `LotFactory.php`
- **Service, Middleware, Policy, & Request:**
  - `app/Services/ActiveCompanyContext.php`, `StockLedgerService.php`, `AuditLogService.php`
  - `app/Exceptions/InsufficientStockException.php`
  - `app/Http/Middleware/EnsureActiveCompany.php`
  - `app/Policies/ProductPolicy.php`, `WarehousePolicy.php`, `LotPolicy.php`
  - `app/Http/Requests/Auth/LoginRequest.php`, `SwitchCompanyRequest.php`, `Master/StoreProductRequest.php`, `Master/StoreWarehouseRackRequest.php`, `Inventory/StoreRackTransferRequest.php`, `Admin/StoreUserRequest.php`, `Admin/StoreMembershipRequest.php`
- **Controller, Routes, & Blade Views:**
  - `routes/web.php`
  - `app/Http/Controllers/Auth/AuthenticatedSessionController.php`, `DashboardController.php`, `ActiveCompanyController.php`
  - `app/Http/Controllers/Master/*`, `app/Http/Controllers/Inventory/*`, `app/Http/Controllers/Admin/*`, `app/Http/Controllers/Group/SummaryController.php`
  - `resources/views/layouts/{app,guest}.blade.php`, `resources/views/auth/login.blade.php`, `resources/views/dashboard/index.blade.php`, `resources/views/{master,inventory,admin,group}/**`
- **Seeder & Pengujian:**
  - `database/seeders/CompanySeeder.php`, `DemoUserSeeder.php`, `FoundationMasterSeeder.php`, `DatabaseSeeder.php`
  - `tests/Unit/IndonesianFormatterTest.php`, `UserRoleTest.php`
  - `tests/Feature/AuthenticationAndActiveCompanyTest.php`, `CrossCompanyAccessControlTest.php`, `OwnershipAndStockLedgerTest.php`, `AdminGroupManagementTest.php`

---

## 3. Keputusan Arsitektur Penting

1. **Konteks PT Aktif & RBAC Terikat PT:** Role disimpan di `company_memberships` per `(user_id, company_id, role)` dan dievaluasi terhadap PT aktif di sesi (`EnsureActiveCompany` + `ActiveCompanyContext` scoped singleton). Pergantian PT hanya mengubah konteks ke PT di mana pengguna punya keanggotaan aktif; percobaan mengganti ke PT lain mengembalikan `403`.
2. **Pemisahan Kepemilikan Hukum vs Lokasi Fisik Gudang:** `warehouses.company_id` mencatat PT pengelola gudang fisik, sedangkan `stock_balances.owner_company_id` (serta `from_owner_company_id` / `to_owner_company_id` pada `stock_movements`) mencatat PT pemilik stok. `StockBalance` memakai `bucket_key` unik deterministik dan `CHECK (quantity >= 0)` di PostgreSQL agar saldo tidak pernah ganda atau negatif.
3. **Mutasi Stok Transaksional & Idempotensi:** Seluruh perubahan saldo melalui `StockLedgerService` memakai `DB::transaction`, `lockForUpdate()`, aritmetika desimal presisi `bcmath`, `idempotency_key` unik, dan penulisan `AuditLog` otomatis.
4. **Proteksi Harga Jual Pelanggan:** Kolom/halaman harga jual pelanggan (`/harga-pelanggan` dan field terkait pada produk) hanya terbuka bagi `sales_distribusi` / `finance` pada konteks `DISTRIBUSI`, atau `manajemen_grup`; `petugas_logistik` dan role lain ditolak di server (`403`) dan nilainya tidak dirender di Blade.

---

## 4. Hasil Pengujian Terakhir (18 November 2026)

- **`php artisan migrate` (PostgreSQL `simulasi_erp`):** **LULUS** — seluruh 13 migrasi berhasil dijalankan.
- **`php artisan db:seed` (PostgreSQL `simulasi_erp`):** **LULUS & IDEMPOTEN** — terverifikasi `Companies=3`, `Users=12`, `Products=3`, `Warehouses=3`, `Racks=8`, `Lots=3`, `Balances=3`, `Movements=3` setelah dijalankan dua kali.
- **`php artisan test --compact` (PostgreSQL `simulasi_erp_test`):** **LULUS** — `21 passed, 119 assertions` (8.100 ms), mencakup 5 Unit test dan 16 Feature/Integration test.

---

## 5. Kendala & Pekerjaan Tertunda

- **Kendala:** Tidak ada kendala tersisa pada tahap Fondasi.
- **Pekerjaan Tertunda (Tahap Berikutnya di Luar Fondasi):**
  - **Tahap 2 — Alur Inti:** Pesanan pelanggan, reservasi stok konkuren, PO antar-PT, pengadaan bahan baku, BOM, perintah produksi, dan pencatatan QC lulus/karantina per lot.
  - **Tahap 3 — Penyelesaian:** Penerimaan & selisih gudang, pengiriman parsial, bukti serah terima, invoice pelanggan, dan invoice antar-PT.
  - **Tahap 4 — Verifikasi:** Dashboard grup dan uji end-to-end skenario 1.000 kotak.

---

## 6. Satu Langkah Berikutnya yang Konkret

Setelah review tahap **Fondasi** disetujui dan Anda meminta lanjut, mulai implementasi **Tahap 2 — Alur Inti** (pesanan pelanggan, reservasi stok dengan penguncian baris PostgreSQL `lockForUpdate()`, PO antar-PT, pengadaan bahan baku, BOM, perintah produksi, dan QC per lot).
