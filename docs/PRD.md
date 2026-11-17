# Simulasi ERP v1 — Product Requirements Document (PRD)

## 1. Ringkasan Produk

**Simulasi ERP** adalah aplikasi web internal untuk mensimulasikan operasional satu grup usaha terintegrasi yang terdiri dari tiga PT fiktif di Indonesia. Aplikasi ini melacak pesanan pelanggan dari pengecekan stok, pembelian antar-PT, pengadaan bahan baku, produksi dan quality control (QC), perpindahan kepemilikan dan lokasi fisik stok di gudang, pengiriman parsial dengan bukti serah terima, hingga penagihan (invoice) pelanggan dan antar-PT.

Aplikasi ini dibangun khusus untuk satu grup usaha internal (bukan platform SaaS multi-tenant eksternal).

---

## 2. Stack Teknologi & Ketentuan Umum

- **Backend:** PHP 8.3+ dan Laravel 13
- **Rendering Antarmuka:** Server-side rendering menggunakan Blade
- **Styling:** Tailwind CSS (tanpa React atau Vue)
- **Database:** PostgreSQL (database utama aplikasi dan database pengujian integrasi terpisah)
- **Lokalisasi & Konvensi:**
  - Bahasa Indonesia pada antarmuka dan pesan sistem
  - Format mata uang Rupiah (`Rp`)
  - Zona waktu tampilan `Asia/Jakarta` (`WIB`)
- **UI & Keamanan Konfigurasi:**
  - Antarmuka responsif dengan penanda PT aktif yang mudah terlihat
  - `.env.example` bebas kredensial asli; data fiktif dan akun demo khusus lingkungan lokal

---

## 3. Struktur Perusahaan (Tiga PT dalam Grup)

| Perusahaan | Kode | Lokasi | Tanggung Jawab Operasional |
|---|---|---|---|
| **PT Reka Kemasan Nusantara** | `PRODUKSI` | Karawang | Pembelian bahan baku, produksi kotak karton, QC, dan penjualan antar-PT ke PT Distribusi |
| **PT Reka Distribusi Indonesia** | `DISTRIBUSI` | Bekasi | Penjualan ke pelanggan, reservasi stok, pembelian antar-PT, alokasi stok, dan invoice pelanggan |
| **PT Reka Logistik Indonesia** | `LOGISTIK` | Cikarang | Gudang titipan, penerimaan barang, manajemen rak, picking, pengiriman, bukti serah terima, dan tagihan jasa |

### Prinsip Pemisahan Kepemilikan dan Lokasi Fisik

- Setiap PT memiliki hak akses, dokumen transaksi, kepemilikan stok, dan catatan tagihan masing-masing.
- **Lokasi fisik gudang/rak** dan **PT pemilik barang** dicatat secara terpisah.
- Contoh: 300 kotak karton milik **PT Reka Distribusi Indonesia** yang disimpan secara fisik di gudang/rak milik **PT Reka Logistik Indonesia** tetap tercatat sebagai stok milik **PT Reka Distribusi Indonesia**, sementara pengelola gudang fisiknya adalah **PT Reka Logistik Indonesia**.

---

## 4. Pengguna, Keanggotaan, dan Hak Akses (RBAC)

| Role | Kewenangan |
|---|---|
| **Admin Grup** | Mengelola perusahaan, pengguna, keanggotaan PT, dan role. Tidak otomatis boleh memposting transaksi operasional/keuangan |
| **Sales Distribusi** | Membuat pesanan pelanggan serta memantau stok dan pengiriman PT Distribusi |
| **Purchasing** | Membuat Purchase Order (PO) untuk PT tempat pengguna ditugaskan |
| **Supervisor Produksi** | Menyetujui dan menjalankan perintah produksi |
| **Petugas QC** | Mencatat hasil lulus atau gagal (karantina) per lot produksi |
| **Petugas Gudang dan Logistik** | Mengelola penerimaan, penempatan rak, picking, dan pengiriman, **tanpa melihat harga jual pelanggan** |
| **Finance** | Memeriksa dan memposting invoice atau tagihan sesuai kewenangan di PT masing-masing |
| **Manajemen Grup** | Melihat ringkasan eksekutif lintas PT tanpa mengubah transaksi operasional |

### Aturan Penegakan Akses

1. Satu pengguna dapat memiliki role berbeda di beberapa PT.
2. Pergantian PT aktif hanya mengubah konteks kerja saat ini, **bukan** menambah hak akses di luar keanggotaan PT tersebut.
3. Validasi izin dan cakupan PT wajib ditegakkan di server, termasuk untuk akses URL langsung dan permintaan dengan ID milik PT lain.
4. Petugas Logistik hanya boleh melihat data yang diperlukan untuk menangani barang di gudangnya dan **dilarang keras mengakses harga jual pelanggan** (AC-06).
5. Dokumen antar-PT hanya dapat diakses oleh pihak dan role yang berwenang tanpa membuka akses umum ke data PT lain.

---

## 5. Alur Bisnis MVP

### 5.1 Penjualan dan Pembelian Antar-PT
1. Sales Distribusi membuat dan mengonfirmasi pesanan pelanggan.
2. Sistem mereservasi stok siap jual milik PT Distribusi sesuai kuantitas tersedia. Stok terreservasi tidak boleh dialokasikan ulang ke pesanan lain.
3. Purchasing Distribusi membuat PO antar-PT ke PT Produksi untuk selisih kebutuhan yang kurang. Saat PO dikirim, sistem membuat dokumen penjualan internal tertaut di PT Produksi agar penjual internal dapat menerima pesanan tersebut.
4. Sisa kebutuhan pesanan tetap terbuka hingga seluruhnya terpenuhi.

### 5.2 Pengadaan, Produksi, dan QC
1. Supervisor Produksi membuat perintah produksi berdasarkan PO antar-PT. Sistem menghitung kebutuhan material berdasarkan Bill of Materials (BOM) dan menandai kekurangan bahan baku.
2. Purchasing Produksi memesan bahan yang kurang. Stok bahan bertambah saat penerimaan bahan dicatat.
3. Sistem mencatat konsumsi bahan dan hasil produksi per lot. Hasil produksi belum berstatus siap jual sebelum lulus QC.
4. Barang yang lulus QC masuk ke stok siap jual (`available`). Barang yang gagal QC masuk ke status karantina (`quarantine`) dan tidak boleh direservasi, dijual, maupun dikirim.
5. Produksi susulan dapat dibuat untuk sisa PO tanpa membuat ulang pesanan pelanggan.

### 5.3 Gudang, Perpindahan Kepemilikan, dan Pengiriman
1. PT Produksi menerbitkan surat jalan. Barang keluar dari stok siap jual dan masuk status dalam perjalanan (`in_transit`), dengan kepemilikan masih di PT Produksi.
2. Petugas Logistik menerima barang atas nama PT Distribusi. Saat penerimaan disahkan, **hanya kuantitas yang diterima aktual** yang berpindah kepemilikan ke PT Distribusi dan ditempatkan di gudang PT Logistik.
3. Jika kuantitas fisik berbeda dari surat jalan, penerimaan ditahan untuk diperiksa. Setelah disahkan petugas berwenang, hanya jumlah aktual yang menjadi stok PT Distribusi; selisih tetap terbuka dan tidak boleh direservasi atau ditagih.
4. PT Distribusi mengalokasikan stok masuk ke pesanan pelanggan. PT Logistik hanya boleh melakukan picking dan pengiriman dari stok yang telah dialokasikan.
5. Pengiriman parsial diperbolehkan. Pesanan selesai setelah seluruh jumlah diterima pelanggan dan tercatat pada bukti serah terima.

### 5.4 Invoice dan Dashboard Grup
- **Invoice Antar-PT Barang:** PT Produksi menagih PT Distribusi sesuai kuantitas barang yang diterima. Posting mencatat piutang di PT Produksi dan utang di PT Distribusi dengan referensi transaksi yang sama.
- **Invoice Pelanggan:** PT Distribusi menagih pelanggan berdasarkan bukti serah terima. Kuantitas yang sama tidak boleh ditagih dua kali.
- **Invoice Antar-PT Jasa Logistik:** PT Logistik menagih PT Distribusi dengan tarif tetap per pengiriman pelanggan yang selesai, mencatat pasangan piutang dan utang serupa transaksi barang antar-PT.
- **Imutabilitas Dokumen Terposting:** Dokumen terposting tidak boleh diubah atau dihapus langsung. Koreksi menggunakan dokumen pembalik yang merujuk dokumen asal.
- **Dashboard Grup:** Menampilkan status pesanan, kekurangan stok, hasil QC, pengiriman, omzet eksternal, dan nilai transaksi internal secara terpisah. Penjualan barang dan jasa antar-PT tidak dihitung sebagai omzet eksternal.

---

## 6. Skenario Demo Utama

| Parameter | Nilai Simulasi |
|---|---|
| Pesanan pelanggan | 1.000 kotak karton |
| Stok awal PT Distribusi | 300 kotak karton di gudang PT Logistik |
| Stok bahan awal PT Produksi | 800 lembar karton dan 5 kg lem |
| BOM per 1 kotak karton | 1 lembar karton dan 10 gram lem |
| Pembelian tambahan bahan | 3 kg lem |
| Harga barang antar-PT | Rp5.000 per kotak |
| Harga jual pelanggan | Rp7.000 per kotak |
| Tarif jasa logistik | Rp150.000 per pengiriman pelanggan yang selesai |

### Urutan Hasil yang Harus Bisa Direproduksi
1. Reservasi 300 kotak stok awal dan buat PO antar-PT untuk 700 kotak.
2. Sistem mendeteksi kekurangan 2 kg lem untuk memproduksi 700 kotak (butuh 700 lembar karton dan 7 kg lem; tersedia 800 lembar dan 5 kg lem). Setelah pembelian 3 kg lem diterima, produksi dilanjutkan.
3. Produksi menghasilkan 700 kotak. QC meluluskan 650 kotak dan memindahkan 50 kotak ke karantina.
4. Setelah 650 kotak diterima di gudang Logistik, Distribusi menggabungkannya dengan 300 kotak awal. Pengiriman pertama memenuhi 950 kotak dan menyisakan 50 kotak pada pesanan yang sama.
5. Produksi susulan menghasilkan 50 kotak yang lulus QC. Pengiriman kedua memenuhi sisa 50 kotak dan menutup pesanan.
6. Total invoice pelanggan: **Rp7.000.000** (`1.000 × Rp7.000`), invoice barang antar-PT: **Rp3.500.000** (`700 × Rp5.000`), dan tagihan jasa logistik: **Rp300.000** (`2 × Rp150.000`). Dashboard menampilkan omzet eksternal **Rp7.000.000** dan transaksi internal **Rp3.800.000** secara terpisah.

*Catatan: Seluruh nominal adalah angka simulasi tanpa pajak. Seeder menyiapkan data awal, bukan mengisi hasil akhir; angka dashboard dihitung dari transaksi yang dijalankan.*

---

## 7. Aturan Data & Kriteria Penerimaan (Acceptance Criteria)

### Aturan Integritas Data
- Setiap pergerakan stok mencatat produk, lot, kuantitas, PT pemilik, lokasi asal dan tujuan yang relevan, referensi dokumen, dan timestamp. Saldo wajib dapat ditelusuri ke transaksi.
- Status stok dipisahkan secara tegas: `available` (tersedia), `reserved` (direservasi), `in_transit` (dalam perjalanan), dan `quarantine` (karantina). Barang yang sama tidak boleh dihitung sebagai stok milik dua PT sekaligus.
- Reservasi, penerimaan, dan posting berjalan di dalam transaksi database (`DB::transaction`) dengan proteksi idempotensi dan penguncian baris untuk mencegah race condition atau transaksi ganda.
- Audit log mencatat pelaku, PT aktif/terkait, tindakan, waktu, dan dokumen untuk persetujuan, QC, serah terima, posting, dan pembalikan dokumen.

### Matriks Uji Penerimaan MVP

| ID | Skenario Uji | Hasil yang Diharapkan |
|---|---|---|
| **AC-01** | Jalankan skenario lengkap 1.000 kotak | Dua pengiriman selesai dan nilai invoice sesuai simulasi |
| **AC-02** | Coba reservasi 50 kotak gagal QC | Sistem menolak dan stok karantina tetap tercatat |
| **AC-03** | Surat jalan 650 kotak, fisik hanya 648 | Setelah disahkan, hanya 648 menjadi stok Distribusi; selisih 2 tetap tercatat |
| **AC-04** | Dua pesanan mereservasi stok terakhir bersamaan | Total reservasi tidak melebihi stok tersedia |
| **AC-05** | Ulangi penerimaan atau posting invoice | Tidak ada pergerakan stok atau tagihan ganda |
| **AC-06** | Petugas Logistik membuka harga pelanggan melalui URL langsung | Server menolak akses (403) |
| **AC-07** | Buka pesanan setelah pengiriman pertama | Terlihat 950 diterima, 50 tersisa, dan referensi dokumen sesuai akses |
| **AC-08** | Cocokkan dashboard dengan invoice tiap PT | Transaksi internal tidak masuk omzet eksternal |

---

## 8. Batas Lingkup (Out of Scope)

- **Workforce Roadmap (di luar MVP rantai pasok):** Absensi selfie, target kerja, verifikasi tugas, logbook magang, dan payroll tidak boleh diimplementasikan pada MVP ini maupun disalin dari proyek lama.
- **Keuangan & Eksternal di luar MVP:** Retur, buku besar (general ledger), konsolidasi akuntansi penuh, pajak, rekonsiliasi bank, dan integrasi eksternal. Ringkasan grup bersifat laporan manajerial simulasi, bukan laporan keuangan resmi.

---

## 9. Tahapan Implementasi

1. **Tahap 1 — Fondasi:** Login, 3 PT, keanggotaan dan role, pemilihan PT aktif, master produk, gudang, rak, lot, kepemilikan, dan pergerakan stok dasar.
2. **Tahap 2 — Alur Inti:** Pesanan pelanggan, reservasi, PO antar-PT, pengadaan bahan, BOM, produksi, dan QC.
3. **Tahap 3 — Penyelesaian:** Penerimaan dan selisih gudang, pengiriman parsial, bukti serah terima, invoice pelanggan, dan invoice antar-PT.
4. **Tahap 4 — Verifikasi:** Dashboard grup, seeder skenario lengkap, dan uji alur end-to-end dari pesanan sampai invoice melalui antarmuka.
