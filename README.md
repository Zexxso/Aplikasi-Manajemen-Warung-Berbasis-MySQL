# Warungku - Versi PHP Native

Aplikasi Manajemen Warung (Pencatatan Stok & Keuangan Mobile-First) versi PHP Native murni tanpa dependensi framework ataupun build step (Node.js/Vite).

## 🚀 Cara Menjalankan

### Menggunakan PHP Built-in Server (Paling Cepat & Mudah)
1. Buka terminal (PowerShell / Command Prompt) dan arahkan ke folder ini:
   ```bash
   cd e:\Warungku\versi_native
   ```
2. Jalankan server bawaan PHP:
   ```bash
   php -S localhost:8080 index.php
   ```
3. Buka browser di [http://localhost:8080](http://localhost:8080)

---

### Menggunakan Apache (XAMPP / Laragon / WAMP)
- Letakkan folder atau symlink `versi_native` di dalam `htdocs` atau `www`.
- File `.htaccess` sudah disediakan untuk URL rewriting otomatis.
- Buka browser di `http://localhost/versi_native/`

---

## 🗄️ Koneksi Database
- Aplikasi otomatis membaca file `e:\Warungku\warungku\.env` untuk konfigurasi host, port, nama database, dan kredensial MySQL.
- Default:
  - Host: `127.0.0.1`
  - Port: `3306`
  - Database: `warungku`
  - Username: `root`
  - Password: `(kosong)`

---

## 👤 Akun Pengujian (Sesuai Database Eksisting)

### 1. Akun Pemilik Warung (Owner)
- **Email:** `busari@warungku.id`
- **Nomor HP:** `081234567890`
- **Kata Sandi:** `password123`
- *(Tombol "Gunakan Akun Demo" di halaman login dapat langsung mengisi kredensial ini)*

### 2. Akun Penjaga Toko (Staff)
- **Nomor HP:** `081298765432` / `085703027774`
- **Kata Sandi:** `123456`
- **Akses:** Beranda, Barang, +Catat Transaksi, Riwayat. Halaman Laporan dikunci dengan status khusus staff.

---

## 📱 Fitur-Fitur Utama
1. **Autentikasi Multi-Role:** Login via Email atau No HP + kata sandi, logout, lupa password (khusus owner).
2. **Dashboard:** Filter periode (Hari Ini, Minggu Ini, Bulan Ini), ringkasan laba kotor/bersih, peringatan stok menipis, daftar 5 transaksi terbaru.
3. **Manajemen Barang (`/products`):** Kartu produk, pencarian real-time, chip filter kategori dan stok menipis, form tambah & ubah barang (bottom sheet), soft delete.
4. **Pencatatan Transaksi (`+ Catat`):**
   - **Barang Masuk (Kulakan):** Tambah stok fisik, update harga beli terakhir, catat mutasi stok, otomatis catat pengeluaran kas dengan DB transaction.
   - **Barang Keluar:** Validasi stok fisik tidak boleh minus, potong stok, jika alasan 'Terjual' otomatis catat pemasukan kas.
   - **Catat Kas Manual:** Pemasukan & Pengeluaran kas operasional berformat Rupiah.
   - **Shortcut Tambah Barang Cepat:** Langsung dari modal pemilih barang saat transaksi.
5. **Riwayat Transaksi (`/history`):** Gabungan mutasi stok dan kas yang dikelompokkan berdasarkan tanggal, filter chip, tombol hapus transaksi dengan pengembalian stok & uang (atomic rollback).
6. **Laporan Keuangan (`/reports`):** Grafik batang pemasukan vs pengeluaran, rincian Laba Kotor & Laba Bersih (Pemasukan - HPP - Biaya Operasional), 5 Barang Terlaris, dan proteksi halaman khusus owner.
7. **Kelola Karyawan (`/staff`):** Tambah penjaga toko, aktifkan/nonaktifkan staf, reset kata sandi instan ke `123456` tanpa OTP/email.
8. **Manajemen Pembeli & Pelanggan (`/customers`):**
   - **Pencatatan Otomatis Transaksi:** Setiap penjualan ("Barang Keluar" dengan alasan Terjual) otomatis terhubung ke pembeli.
   - **Mode Cepat (Anonim):** Jika nama dikosongkan (pembeli umum/lewat), sistem otomatis men-generate kode urut `CUST-001`, `CUST-002`, dst.
   - **Mode Langganan / Kenal:** Ketik nama pembeli baru (misal: "Pak RT", "Bu Endang") atau pilih dari daftar langganan tersimpan (autocomplete/chip cepat).
   - **Riwayat Belanja Terperinci (`/customers/{id}`):** Melihat riwayat lengkap barang apa saja yang dibeli pembeli tersebut, tanggal, jam, kuantitas, harga per item, dan total rupiah yang dibelanjakan.
   - **Hubungi Langganan:** Tombol langsung menuju chat WhatsApp dengan nomor pembeli.


