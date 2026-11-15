# RENCANA & LAPORAN SELESAI MIGRASI DATABASE "WARUNGKU" KE ERD BARU (SINGLE-STORE: WARUNG BU SARI)

> **Status Dokumen**: SELESAI & TERUJI (100% COMPLETE & COMMITTED)  
> **Tanggal Selesai**: 15 November 2026  
> **Branch Git**: `refactor/erd-single-store-warung-bu-sari`  
> **Lingkungan Database**: MariaDB / MySQL (`warungku`)  
> **Toko Target**: Single-Store — **Warung Bu Sari**  
> **File Backup Baseline**: `backup_warungku_baseline_20261115.sql` (Tersimpan aman)  

---

## ⚠️ PERINGATAN KESELAMATAN & ATURAN KERJA WAJIB

Sebelum menjalankan migrasi skema maupun perubahan data apa pun:
1. **Wajib Backup Penuh Database**:
   Jalankan backup database `warungku` sebelum fase eksekusi:
   ```bash
   mysqldump -u root -p warungku > backup_warungku_baseline_$(date +%Y%m%d_%H%M%S).sql
   ```
2. **Bekerja di Branch Baru**:
   Inisialisasi git dan buat branch terisolasi:
   ```bash
   git checkout -b refactor/erd-single-store-warung-bu-sari
   ```
3. **Prinsip Migrasi Bertahap (Non-Destructive)**:
   Tambah tabel/kolom baru $\rightarrow$ Backfill data $\rightarrow$ Alihkan kode ke struktur baru $\rightarrow$ Verifikasi baseline $\rightarrow$ Drop kolom/tabel usang di tahap paling akhir.
4. **Semua Transaksi Data Dibungkus `BEGIN ... COMMIT / ROLLBACK`**.

---

## 1. HASIL AUDIT SISTEM (TAHAP 0 - READ ONLY)

### 1.1. Status Data Riil Baseline Database Saat Ini
Audit dilakukan langsung terhadap database operasional `warungku` pada 15 November 2026:

| Nama Tabel / View | Jenis | Jumlah Baris Riil | Catatan Pemakaian |
| :--- | :--- | :---: | :--- |
| **`pengguna`** | Tabel | **3 baris** | 1 Owner (`Bu Sari`), 2 Staff (`Budi Penjaga`, `Dzaky Naufal`). Semua terafiliasi dengan Warung Bu Sari. |
| **`produk`** | Tabel | **10 baris** | Total fisik stok aktif: **200 item**. Memiliki kolom tenant `id_pengguna`. |
| **`kategori`** | Tabel | **7 baris** | Sembako, Makanan, Minuman, Rokok, Kebersihan, Bumbu Dapur, Lain-lain (`id_pengguna` = 1). |
| **`kategori_kas`** | Tabel | **9 baris** | Kategori default sistem: Penjualan, Pendapatan Lain-lain, Kulakan, Listrik, Gas, Gaji, Operasional, dll. (`id_pengguna` = NULL). |
| **`catatan_kas`** | Tabel | **20 baris** | **Total Pemasukan: Rp 427.200**, **Total Pengeluaran: Rp 392.000**. 16 baris terikat penjualan, 1 kulakan, 3 kas operasional murni. |
| **`mutasi_stok`** | Tabel | **17 baris** | 16 transaksi penjualan (`out` + `sale`), 1 transaksi kulakan (`in` + `purchase` Minyak Goreng 1L, Rp 192.000). |
| **`pelanggan`** | Tabel | **2 baris** | `Pak RT Bambang` (CUST-004) dan `Ibu Siti` (CUST-005). |
| **`pembeli`** | SQL View | **2 baris** | Alias VIEW dari tabel `pelanggan` (View duplikat). |
| **`sesi`** | Tabel | **6 baris** | Penyimpanan sesi login aktif (`SESSION_DRIVER=database`). |
| **`cache`** & **`cache_locks`** | Tabel | **0 baris** | Cadangan driver cache database (`CACHE_STORE=database`). |
| **`jobs`**, **`job_batches`**, **`failed_jobs`** | Tabel | **0 baris** | Cadangan driver antrean database (`QUEUE_CONNECTION=database`). |
| **`token_reset_kata_sandi`** | Tabel | **0 baris** | Penyimpanan token lupa password (`/forgot-password`). |
| **`migrations`** | Tabel | **9 baris** | Riwayat migration Laravel yang telah dieksekusi. |

### 1.2. Audit Arsitektur Kode: Multi-Tenant Saat Ini
- **Mekanisme Owner/Tenant**:
  - Kolom `id_pengguna` pada tabel `produk`, `kategori`, `pelanggan`, `catatan_kas`, `mutasi_stok` mengunci data pada satu toko pemilik (`id = 1`).
  - Kolom `id_pemilik` pada tabel `pengguna` digunakan untuk menautkan akun kasir/staff ke owner.
  - Helper/Accessor: `$user->warung_owner_id` (Laravel) dan `warung_owner_id()` (Native) mengambil ID owner toko dari staff yang sedang login.
  - Nama toko saat ini diambil dari kolom `pengguna.nama_toko` ("Warung Bu Sari").
- **Audit Fitur Pembayaran**:
  - Transaksi kasir saat ini murni melayani **Tunai** dan **Kasbon** (hutang pelanggan).
  - Kasbon tercatat di `mutasi_stok` dengan `status_pembayaran = 'debt'` dan nama pembeli otomatis terdaftar di `pelanggan`. Saat dilunasi, mutasi diperbarui menjadi `paid` dan pemasukan dicatat ke `catatan_kas`.
- **Audit Konfigurasi Driver (`.env`)**:
  - `SESSION_DRIVER=database` $\rightarrow$ Tabel **`sesi` WAJIB DIPERTAHANKAN**.
  - `CACHE_STORE=database` $\rightarrow$ Tabel **`cache` & `cache_locks` WAJIB DIPERTAHANKAN** kecuali driver diubah ke `file`.
  - `QUEUE_CONNECTION=database` $\rightarrow$ Tabel **`jobs`, `job_batches`, `failed_jobs` WAJIB DIPERTAHANKAN**.
  - Rute Forgot Password aktif di Laravel (`NewPasswordController` & `PasswordResetLinkController`) $\rightarrow$ Tabel **`token_reset_kata_sandi` WAJIB DIPERTAHANKAN**.
  - View **`pembeli`** adalah SQL View alias dari `pelanggan` yang tidak memiliki ketergantungan model inti dan aman dibersihkan pada tahap akhir.

---

## 2. SKEMA DATABASE TARGET (ERD BARU)

### 2.1. Diagram Relasi Entitas Teks (ASCII ERD)

```text
+-------------------+             1:N              +-------------------+
|      PEMASOK      |----------------------------< |      SUPLAI       |
| (Supplier Toko)   |                              | (Histori Kulakan) |
+-------------------+                              +-------------------+
                                                             |
                                                             | N:1
                                                             V
+-------------------+             1:N              +-------------------+
|     KATEGORI      |----------------------------< | BARANG / PRODUK   |
| (Kategori Produk) |                              | (Katalog Barang)  |
+-------------------+                              +-------------------+
                                                             |
                                                             | 1:N
                                                             V
+-------------------+             1:N              +-------------------+
|      PESANAN      |----------------------------< |       MUAT        |
| (Header Trx Jual) |                              | (Detail Pesanan)  |
+-------------------+                              +-------------------+
    |           |
    | N:1       | N:1
    V           V
+-----------+ +-----------+
| PELANGGAN | |  PEGAWAI  |
| (Member)  | |(pengguna) |
+-----------+ +-----------+

Buku Besar Pendukung (Audit Trail):
- MUTASI_STOK : Buku besar pergerakan fisik stok (in/out/adjust).
- CATATAN_KAS : Buku besar arus kas keuangan (income/expense).
```

### 2.2. Spesifikasi Skema Tabel Target

#### A. Tabel Baru: `pemasok`
Menyimpan identitas entitas rekanan kulakan Warung Bu Sari.
- `id_pemasok`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `nama_pemasok`: VARCHAR(150) NOT NULL
- `alamat_pemasok`: TEXT NULL
- `no_hp_pemasok`: VARCHAR(30) NULL
- `email_pemasok`: VARCHAR(150) NULL
- `dibuat_pada`: TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
- `diperbarui_pada`: TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
- `dihapus_pada`: TIMESTAMP NULL (Soft Delete)
- *Index*: `idx_pemasok_nama (nama_pemasok)`

#### B. Tabel Baru: `suplai`
Relasi kulakan/pasokan barang antara pemasok dan barang (produk).
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `id_pemasok`: BIGINT UNSIGNED NOT NULL (FK ke `pemasok.id_pemasok`)
- `id_produk`: BIGINT UNSIGNED NOT NULL (FK ke `produk.id`)
- `tanggal_suplai`: DATETIME NOT NULL
- `jumlah`: INT NOT NULL
- `harga_beli`: DECIMAL(12,0) NOT NULL
- `id_mutasi_stok`: BIGINT UNSIGNED NULL (FK ke `mutasi_stok.id`, audit trail)
- `catatan`: VARCHAR(255) NULL
- `dibuat_pada`: TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
- `diperbarui_pada`: TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
- `dihapus_pada`: TIMESTAMP NULL (Soft Delete)
- *Index*: `idx_suplai_pemasok (id_pemasok)`, `idx_suplai_produk (id_produk)`, `idx_suplai_tanggal (tanggal_suplai)`

#### C. Tabel `produk` (Entitas BARANG)
Katalog produk Warung Bu Sari.
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `id_kategori`: BIGINT UNSIGNED NOT NULL (FK ke `kategori.id`)
- `nama`: VARCHAR(150) NOT NULL
- `satuan`: VARCHAR(20) NOT NULL DEFAULT 'pcs'
- `gambar`: VARCHAR(255) NULL
- `harga_beli`: DECIMAL(12,0) NOT NULL DEFAULT 0
- `harga_jual`: DECIMAL(12,0) NOT NULL DEFAULT 0
- `stok`: INT NOT NULL DEFAULT 0
- `stok_minimum`: INT NOT NULL DEFAULT 5
- `status_aktif`: TINYINT(1) NOT NULL DEFAULT 1
- `dibuat_pada`: TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
- `diperbarui_pada`: TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
- `dihapus_pada`: TIMESTAMP NULL (Soft Delete)
- *(Kolom multi-tenant `id_pengguna` ditandai untuk dihapus pada fase akhir)*

#### D. Tabel Baru: `pesanan` (Header Transaksi Kasir)
Header transaksi penjualan kasir.
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `no_faktur`: VARCHAR(50) NOT NULL UNIQUE
- `id_pelanggan`: BIGINT UNSIGNED NULL (FK ke `pelanggan.id`, NULL = Pembeli Umum)
- `id_pengguna`: BIGINT UNSIGNED NOT NULL (FK ke `pengguna.id`, Pegawai yang memproses)
- `tanggal_pesanan`: DATETIME NOT NULL
- `total_harga`: DECIMAL(14,0) NOT NULL
- `jumlah_bayar`: DECIMAL(14,0) NOT NULL DEFAULT 0
- `kembalian`: DECIMAL(14,0) NOT NULL DEFAULT 0
- `metode_pembayaran`: VARCHAR(20) NOT NULL DEFAULT 'cash' ('cash', 'kasbon', 'qris')
- `status_pembayaran`: VARCHAR(20) NOT NULL DEFAULT 'paid' ('paid', 'debt', 'cancelled')
- `dibayar_pada`: DATETIME NULL
- `catatan`: VARCHAR(255) NULL
- `dibuat_pada`: TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
- `diperbarui_pada`: TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
- `dihapus_pada`: TIMESTAMP NULL (Soft Delete)
- *Index*: `idx_pesanan_no_faktur (no_faktur)`, `idx_pesanan_pelanggan (id_pelanggan)`, `idx_pesanan_pengguna (id_pengguna)`, `idx_pesanan_status (status_pembayaran)`, `idx_pesanan_tanggal (tanggal_pesanan)`

#### E. Tabel Baru: `detail_pesanan` (Entitas MUAT)
Detail item produk yang dimuat dalam satu transaksi pesanan.
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `id_pesanan`: BIGINT UNSIGNED NOT NULL (FK ke `pesanan.id` ON DELETE CASCADE)
- `id_produk`: BIGINT UNSIGNED NOT NULL (FK ke `produk.id`)
- `jumlah`: INT NOT NULL
- `harga_satuan`: DECIMAL(12,0) NOT NULL (Snapshot harga jual saat transaksi)
- `harga_modal`: DECIMAL(12,0) NOT NULL (Snapshot harga modal saat transaksi)
- `subtotal`: DECIMAL(14,0) NOT NULL (`jumlah * harga_satuan`)
- `dibuat_pada`: TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
- `diperbarui_pada`: TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
- *Index*: `idx_dp_pesanan (id_pesanan)`, `idx_dp_produk (id_produk)`

#### F. Tabel `pengguna` (Entitas PEGAWAI)
Akun pegawai dan pemilik warung.
- Kolom baru yang ditambahkan: **`alamat`: TEXT NULL** (setelah `telepon`).
- Kolom yang ditandai untuk dihapus pada fase akhir: `nama_toko`, `id_pemilik`.
- Kolom relasi proses: `id` tetap menjadi PK untuk relasi `pesanan.id_pengguna` dan `mutasi_stok.id_pengguna`.

#### G. Tabel `pelanggan` (Entitas PELANGGAN)
Identitas pembeli/pelanggan warung.
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `kode`: VARCHAR(50) NOT NULL UNIQUE
- `nama`: VARCHAR(150) NOT NULL
- `telepon`: VARCHAR(30) NULL
- `alamat`: TEXT NULL
- `catatan`: TEXT NULL
- `dibuat_pada`, `diperbarui_pada`, `dihapus_pada`: TIMESTAMP
- *(Kolom `id_pengguna` ditandai untuk dihapus pada fase akhir)*

#### H. Penyesuaian Tabel Pendukung: `mutasi_stok` & `catatan_kas`
- **`mutasi_stok`**:
  - Tambah kolom nullable: **`id_pesanan`: BIGINT UNSIGNED NULL** (FK ke `pesanan.id`).
  - Menjaga integritas riwayat: Kolom `harga_satuan`, `harga_modal`, `id_pelanggan`, `no_faktur` tetap dipertahankan selama masa transisi sebagai utang teknis terisolasi agar riwayat lama tidak rusak.
- **`catatan_kas`**:
  - Tambah kolom nullable: **`id_pesanan`: BIGINT UNSIGNED NULL** (FK ke `pesanan.id`).

---

## 3. RENCANA URUTAN MIGRATION & STRATEGI BACKFILL DATA

Semua penyesuaian dieksekusi melalui **file migration baru** dengan method `up()` dan `down()` simetris.

### Urutan File Migration Baru:

```text
1. 2026_11_16_000001_create_pemasok_table.php
   -> Membuat tabel `pemasok`
2. 2026_11_16_000002_add_alamat_to_pengguna_table.php
   -> Menambahkan kolom `alamat` (TEXT NULL) pada tabel `pengguna`
3. 2026_11_16_000003_create_pesanan_and_detail_pesanan_tables.php
   -> Membuat tabel `pesanan` dan `detail_pesanan`
4. 2026_11_16_000004_create_suplai_table.php
   -> Membuat tabel `suplai`
5. 2026_11_16_000005_add_id_pesanan_to_mutasi_and_kas_tables.php
   -> Menambahkan kolom `id_pesanan` ke `mutasi_stok` dan `catatan_kas`
6. 2026_11_16_000006_backfill_historical_data_to_pesanan_and_suplai.php
   -> Data Migration (Backfill): Memindahkan data transaksi lama ke struktur baru
7. 2026_11_16_000007_add_foreign_keys_constraints.php
   -> Menambahkan Foreign Key formal setelah data terverifikasi bersih
8. 2026_11_16_000008_convert_to_single_store_warung_bu_sari.php
   -> Dropping kolom tenant lama (dikerjakan paling akhir setelah kode diganti)
```

### Logika & Aturan Backfill Data Historis (Migration 000006):
1. **Backfill Penjualan ke `pesanan` dan `detail_pesanan`**:
   - Ambil seluruh data penjualan di `mutasi_stok` (`jenis = 'out' AND alasan = 'sale'`).
   - Kelompokkan per `no_faktur`. Untuk setiap faktur unik:
     - Hitung `total_harga = SUM(jumlah * harga_satuan)`.
     - Tentukan status pembayaran:
       - Jika `status_pembayaran = 'debt'`: `jumlah_bayar = 0`, `kembalian = 0`, `metode_pembayaran = 'kasbon'`.
       - Jika `status_pembayaran = 'paid'`: `jumlah_bayar = total_harga`, `kembalian = 0`, `metode_pembayaran = 'cash'`.
     - Insert ke tabel `pesanan`.
     - Dapatkan `id_pesanan` yang baru dibuat.
     - Insert tiap baris barang ke `detail_pesanan` dengan snapshot harga.
     - Update baris `mutasi_stok.id_pesanan = id_pesanan`.
     - Update baris `catatan_kas.id_pesanan = id_pesanan` yang berelasi dengan mutasi stok tersebut.
2. **Backfill Kulakan Historis ke `pemasok` & `suplai`**:
   - Buat 1 pemasok default/historis di tabel `pemasok`: `nama_pemasok = 'Pemasok Umum / Kulakan Lama'`.
   - Ambil baris `mutasi_stok` jenis `in` dan alasan `purchase` (Baris ID 6: Minyak Goreng 1L).
   - Insert ke tabel `suplai` terhubung ke pemasok default tersebut dengan snapshot jumlah dan harga beli.

---

## 4. DAFTAR PERUBAHAN KODE PER FILE / MODUL

### Modul A: Model & Eloquent (Laravel)
- **Model Baru**:
  - `app/Models/Pemasok.php`: Relasi `hasMany(Suplai::class)`.
  - `app/Models/Suplai.php`: Relasi `belongsTo(Pemasok::class)`, `belongsTo(Product::class)`.
  - `app/Models/Pesanan.php`: Relasi `hasMany(DetailPesanan::class)`, `belongsTo(Pelanggan::class)`, `belongsTo(User::class)`.
  - `app/Models/DetailPesanan.php`: Relasi `belongsTo(Pesanan::class)`, `belongsTo(Product::class)`.
- **Model yang Diperbarui**:
  - `app/Models/Product.php`: Hapus scope tenant/user, tambah relasi `hasMany(DetailPesanan::class)` & `hasMany(Suplai::class)`.
  - `app/Models/User.php`: Tambah fillable `alamat`, hapus `owner_id` dan accessor tenant.
  - `app/Models/StockMovement.php`: Tambah `id_pesanan` fillable dan relasi `belongsTo(Pesanan::class)`.
  - `app/Models/CashEntry.php`: Tambah `id_pesanan` fillable dan relasi `belongsTo(Pesanan::class)`.
  - `app/Models/Customer.php`: Hapus relasi tenant `user_id`.

### Modul B: Controller & Logika Bisnis
- **`TransactionController`**:
  - `storeStockOut()`: Menginstansiasi `Pesanan` (header) dan `DetailPesanan` (items), lalu otomatis membuat baris buku besar `mutasi_stok` dan `catatan_kas`.
  - `storeStockIn()`: Menerima input `id_pemasok`, membuat record `suplai` berbarengan dengan record `mutasi_stok`.
  - `settleDebt()`: Mengupdate `status_pembayaran = 'paid'` di `pesanan` dan mencatat kas masuk.
- **Controller Baru**:
  - `app/Http/Controllers/PemasokController.php`: CRUD Pemasok.
  - `app/Http/Controllers/SuplaiController.php`: Riwayat suplai per pemasok / per barang.
- **`StaffController`**:
  - Menghapus referensi `owner_id`. Staff dibuat langsung sebagai user berstatus staff untuk Warung Bu Sari. Menambahkan input `alamat`.
- **`HistoryController` & `ReportController`**:
  - Menyesuaikan query riwayat penjualan agar mengambil data dari tabel `pesanan` dan `detail_pesanan` sebagai sumber utama, dengan fallback aman ke `mutasi_stok`.

### Modul C: Routing & Konfigurasi
- **`routes/web.php`**:
  - Daftarkan route resource `/pemasok` dan riwayat `/suplai`.
  - Nonaktifkan rute registrasi publik toko baru (`/register`).
- **`config/app.php` & `.env`**:
  - Tambahkan konfigurasi nama warung tetap:
    ```env
    STORE_NAME="Warung Bu Sari"
    STORE_TAGLINE="Sembako & Kebutuhan Harian"
    STORE_ADDRESS="Jl. Warung Bu Sari No. 1"
    ```
- **Views & Struk**:
  - `views/receipt/show.php` dan komponen header: Ubah pembacaan `$owner['nama_toko']` menjadi `config('app.store_name', 'Warung Bu Sari')`.

---

## 5. RENCANA FITUR BARU (DESAIN SPESIFIKASI)

### Fitur 1: CRUD Pemasok
- **Halaman & Route**:
  - `GET /pemasok` (Daftar pemasok + pencarian)
  - `POST /pemasok` (Simpan pemasok baru)
  - `PUT /pemasok/{id}` (Perbarui kontak/alamat pemasok)
  - `DELETE /pemasok/{id}` (Soft delete pemasok)
- **Validasi**: `nama_pemasok` (wajib, max 150), `no_hp_pemasok` (opsional, max 30), `email_pemasok` (opsional, email), `alamat_pemasok` (opsional).
- **Hak Akses**: Owner (Akses Penuh), Staff (Hanya Lihat / Read-only).
- **Dampak Fitur Lama**: Tidak ada (fitur mandiri).

### Fitur 2: Form Kulakan / Stok Masuk dengan Pemilihan Pemasok
- **Halaman & Route**: Modifikasi Bottom Sheet / Form `POST /stock/in`.
- **Interaksi UI**: Dropdown pilihan pemasok dengan opsi cepat `[+ Tambah Pemasok Baru]` langsung dari form tanpa reload halaman.
- **Validasi**: `id_pemasok` (nullable untuk backward compatibility, tapi direkomendasikan), `id_produk` (wajib), `jumlah` (wajib, min 1), `harga_beli` (wajib).
- **Database Transaction**: Dalam 1 transaksi database, sistem menyimpan record ke tabel `suplai` dan tabel `mutasi_stok`.
- **Hak Akses**: Owner & Staff.

### Fitur 3: Riwayat Suplai per Pemasok dan per Barang
- **Halaman & Route**: `GET /pemasok/{id}/suplai` dan tab riwayat suplai di halaman detail produk.
- **Informasi Tampil**: Tanggal kulakan, nama barang, jumlah pasokan, harga modal snapshot, total modal keluar, dan pegawai yang menerima barang.
- **Hak Akses**: Owner & Staff.

### Fitur 4: Kasir: Input Uang Diterima & Hitung Kembalian
- **Halaman & Route**: Form Kasir / Barang Keluar (`POST /stock/out`).
- **Interaksi UI**:
  - Pada metode bayar **Tunai**, kasir dapat mengetikkan "Uang Diterima".
  - Kalkulator instan Alpine.js menampilkan nominal "Kembalian".
  - Nilai tersimpan di `pesanan.jumlah_bayar` dan `pesanan.kembalian`.
  - Default aman: Jika dikosongkan oleh kasir, sistem menganggap uang pas (`jumlah_bayar = total_harga`, `kembalian = 0`).
- **Hak Akses**: Owner & Staff.

### Fitur 5: Form Pegawai: Tambah Isian Alamat
- **Halaman & Route**: `views/staff/index.php` (Modal Tambah & Edit Pegawai).
- **Interaksi UI**: Penambahan textarea `Alamat Tinggal Pegawai`.
- **Validasi**: `alamat` (string/text, nullable).
- **Hak Akses**: Owner only.

---

## 6. RISIKO, STRATEGI ROLLBACK & RENCANA VERIFIKASI

### Matriks Risiko & Mitigasi:
1. **Risiko Ketidakcocokan Nilai Keuangan / Stok**:
   - *Mitigasi*: Menjalankan skrip verifikasi otomatis sebelum dan sesudah migrasi data. Total rupiah pada `catatan_kas` dan total kuantitas stok di `produk` tidak boleh bergeser 1 rupiah pun.
2. **Risiko Foreign Key Constraint Error saat Backfill**:
   - *Mitigasi*: Foreign key hanya diaktifkan di Migration `000007` setelah seluruh baris data terbukti valid dan tidak ada orphan record.
3. **Risiko Driver Laravel Rusak**:
   - *Mitigasi*: Mempertahankan tabel `sesi`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, dan `token_reset_kata_sandi` sesuai pengaturan `.env`.

### Strategi Rollback:
- Setiap migration memiliki fungsi `down()` lengkap:
  - `down()` pada migration backfill akan menghapus data yang digenerate di `pesanan`, `detail_pesanan`, dan `suplai`, serta mereset kolom `id_pesanan` menjadi NULL.
  - Snapshot file `.sql` baseline disimpan sebelum eksekusi dimulai. Jika terjadi kegagalan fatal, database dapat dikembalikan ke titik awal dalam < 1 menit.

### Prosedur Verifikasi Baseline:
Setelah backfill dijalankan, jalankan kueri validasi:
```sql
-- 1. Verifikasi Jumlah Transaksi Penjualan
SELECT COUNT(*) FROM mutasi_stok WHERE jenis = 'out' AND alasan = 'sale' AND dihapus_pada IS NULL;
SELECT COUNT(*) FROM pesanan WHERE dihapus_pada IS NULL;
-- HASIL HARUS IDENTIK: 16 Baris

-- 2. Verifikasi Total Rupiah Penjualan
SELECT SUM(jumlah * harga_satuan) FROM mutasi_stok WHERE jenis = 'out' AND alasan = 'sale' AND dihapus_pada IS NULL;
SELECT SUM(total_harga) FROM pesanan WHERE dihapus_pada IS NULL;
-- HASIL HARUS IDENTIK

-- 3. Verifikasi Total Pemasukan & Pengeluaran Kas
SELECT SUM(nominal) FROM catatan_kas WHERE jenis = 'income' AND dihapus_pada IS NULL;
-- HARUS TETAP: Rp 427.200
SELECT SUM(nominal) FROM catatan_kas WHERE jenis = 'expense' AND dihapus_pada IS NULL;
-- HARUS TETAP: Rp 392.000

-- 4. Verifikasi Total Fisik Stok Barang
SELECT SUM(stok) FROM produk WHERE dihapus_pada IS NULL;
-- HARUS TETAP: 200 item
```

---

## 7. DAFTAR PERTANYAAN TERBUKA (UNTUK KEPUTUSAN USER)

Berikut adalah beberapa hal penting yang ditemukan saat audit untuk disepakati sebelum eksekusi Tahap 2:

1. **Target Folder Utama Proyek**:
   - Di direktori saat ini terdapat dua folder: `warungku` (framework Laravel lengkap dengan Artisan, Model, dan Test) dan `versi_native` (PHP Native MVC).
   - *Pertanyaan*: Apakah implementasi migration dan fitur baru ini ditujukan untuk **folder Laravel (`warungku`)**, atau untuk **keduanya (`warungku` & `versi_native`)**?
2. **Pemasok untuk Data Kulakan Lama**:
   - Saat ini terdapat 1 transaksi kulakan historis (Minyak Goreng 12 botol, Rp 192.000). Apakah disetujui untuk dibuatkan 1 entitas pemasok default di database, misalnya bernama **"Pemasok Umum (Kulakan Lama)"**, agar transaksi tersebut terhubung ke tabel `suplai`?
3. **Penyederhanaan Driver Cache & Queue**:
   - File `.env` Laravel saat ini menyetel `CACHE_STORE=database` dan `QUEUE_CONNECTION=database` meskipun antrean background jobs belum aktif digunakan. Apakah Anda ingin tetap mempertahankan tabel-tabel database tersebut, atau ingin dialihkan ke `CACHE_STORE=file` dan `QUEUE_CONNECTION=sync` agar tabel `jobs` dan `cache` bisa dibersihkan secara aman?
4. **Keranjang Kasir Multi-Barang**:
   - Saat ini kasir mencatat 1 baris mutasi stok per transaksi keluar (faktur satu per satu). Tabel `pesanan` dan `detail_pesanan` yang baru dirancang sudah mendukung multi-item (1 pesanan memuat banyak barang sekaligus). Apakah di masa depan kasir ingin dilengkapi keranjang belanja multi-item, atau tetap mempertahankan alur satu produk per simpan seperti sekarang?

---

> **Pemberhentian Sesuai SOP**: Tahap 0 dan Tahap 1 selesai. Rencana di atas telah disusun secara komprehensif tanpa mengubah kode operasional aplikasi. Menunggu review dan persetujuan dari Anda sebelum melangkah ke **Tahap berikutnya (Fase A: Migration Aditif + Backfill Data)**.
