# Simulasi ERP

Aplikasi web internal berbasis **PHP 8.3**, **Laravel 13**, **Blade**, **Tailwind CSS v4**, dan **PostgreSQL** untuk mensimulasikan operasional rantai pasok terpadu pada satu grup usaha yang terdiri dari tiga perusahaan fiktif di Indonesia:

1. **PT Reka Kemasan Nusantara (`PRODUKSI`)** — Karawang
2. **PT Reka Distribusi Indonesia (`DISTRIBUSI`)** — Bekasi
3. **PT Reka Logistik Indonesia (`LOGISTIK`)** — Cikarang

> Seluruh spesifikasi produk dan skenario bisnis tersimpan di [docs/PRD.md](docs/PRD.md). Status progres antar-sesi dicatat di [docs/PROGRESS.md](docs/PROGRESS.md).

---

## 1. Persyaratan Sistem

- PHP `>= 8.3` dengan ekstensi `pdo_pgsql`, `pgsql`, `bcmath`, `mbstring`, `openssl`, `intl`, `zip`, `xml`
- Composer `>= 2.x`
- Node.js `>= 20.x` & npm
- PostgreSQL `>= 15` (teruji di lingkungan lokal dengan PostgreSQL 18 pada `127.0.0.1:5432`)

---

## 2. Konfigurasi PostgreSQL (Database Baru & Terpisah)

Buat dua database baru khusus untuk **Simulasi ERP** (jangan memakai atau mereset database proyek lain):

```sql
CREATE DATABASE simulasi_erp;
CREATE DATABASE simulasi_erp_test;
```

Salin `.env.example` ke `.env` dan isi kredensial PostgreSQL lokal Anda (jangan pernah menyimpan kredensial asli ke `.env.example` atau Git):

```env
APP_NAME="Simulasi ERP"
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=simulasi_erp
DB_USERNAME=postgres
DB_PASSWORD=KATA_SANDI_LOKAL_ANDA
```

Untuk pengujian integrasi terpisah (`phpunit.xml` sudah mengarahkan `DB_CONNECTION=pgsql` dan `DB_DATABASE=simulasi_erp_test`), buat file `.env.testing` (diabaikan oleh `.gitignore`) berisi `DB_USERNAME` dan `DB_PASSWORD` lokal:

```env
DB_USERNAME=postgres
DB_PASSWORD=KATA_SANDI_LOKAL_ANDA
```

---

## 3. Instalasi, Migrasi, Seeder, dan Build Aset

```powershell
composer install
npm install
npm run build
php artisan key:generate
php artisan migrate
php artisan db:seed
```

Perintah `db:seed` menjalankan:
- `CompanySeeder` — 3 PT (`PRODUKSI`, `DISTRIBUSI`, `LOGISTIK`)
- `DemoUserSeeder` — 12 akun demo khusus lingkungan lokal/testing
- `FoundationMasterSeeder` — Master produk (`Kotak Karton Standar`, `Lembaran Karton`, `Lem Industri`), gudang & rak tiap PT, lot awal, serta saldo & pergerakan stok awal:
  - **300 kotak** `Kotak Karton Standar` (`LOT-KKT-AWAL-001`) milik **PT Reka Distribusi Indonesia** disimpan fisik di **Gudang Utama Logistik Cikarang** (`GDG-CKR-01`, rak `RAK-A01`) yang dikelola oleh **PT Reka Logistik Indonesia**
  - **800 lembar** `Lembaran Karton` (`LOT-KRT-AWAL-001`) dan **5 kg** `Lem Industri` (`LOT-LEM-AWAL-001`) milik **PT Reka Kemasan Nusantara** di **Gudang Bahan & Produksi Karawang** (`GDG-KRW-01`, rak `RAK-BHN-01`)

---

## 4. Cara Membuka Aplikasi Secara Lokal

Jalankan server pengembangan Laravel (dan Vite dev server jika mengubah CSS/JS):

```powershell
php artisan serve
```

Lalu buka `http://127.0.0.1:8000` di peramban.

---

## 5. Akun Demo Lokal (`APP_ENV=local`)

Semua akun demo memakai kata sandi: `password`

| Email | Nama | PT Penugasan | Role |
|---|---|---|---|
| `admin@simulasi-erp.local` | Rama Pratama | 3 PT | Admin Grup |
| `manajemen@simulasi-erp.local` | Dewi Kartika | 3 PT | Manajemen Grup |
| `sales.distribusi@simulasi-erp.local` | Budi Santoso | PT Distribusi | Sales Distribusi |
| `purchasing.distribusi@simulasi-erp.local` | Siti Rahma | PT Distribusi | Purchasing |
| `purchasing.produksi@simulasi-erp.local` | Andi Wijaya | PT Produksi | Purchasing |
| `supervisor.produksi@simulasi-erp.local` | Hendra Kusuma | PT Produksi | Supervisor Produksi |
| `qc.produksi@simulasi-erp.local` | Nadia Putri | PT Produksi | Petugas QC |
| `logistik@simulasi-erp.local` | Teguh Saputra | PT Logistik | Petugas Gudang & Logistik |
| `finance.distribusi@simulasi-erp.local` | Rina Melati | PT Distribusi | Finance |
| `finance.produksi@simulasi-erp.local` | Fajar Nugroho | PT Produksi | Finance |
| `finance.logistik@simulasi-erp.local` | Lestari Wulandari | PT Logistik | Finance |
| `multiperan@simulasi-erp.local` | Dian Permata | PT Produksi & PT Logistik | Purchasing (Produksi) + Finance (Logistik) |

---

## 6. Menjalankan Pengujian

Pastikan database `simulasi_erp_test` sudah dibuat dan `.env.testing` telah diisi kredensial PostgreSQL lokal:

```powershell
# Seluruh unit & feature test (PostgreSQL simulasi_erp_test)
php artisan test

# Khusus Unit Test
php artisan test tests/Unit

# Khusus Feature / Integration Test
php artisan test tests/Feature
```
