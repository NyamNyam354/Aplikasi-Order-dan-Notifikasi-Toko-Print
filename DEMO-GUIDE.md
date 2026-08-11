# Panduan Demo - PrintShop

Aplikasi PrintShop adalah sistem manajemen pesanan cetak online. Pelanggan dapat mengunggah dokumen untuk dicetak, dan admin dapat mengelola seluruh proses pesanan dari awal hingga selesai.

---

## Tips dan Catatan

### Prerequisites

Sebelum menjalankan aplikasi, pastikan sudah terinstall:
- **PHP 8.3** atau lebih tinggi
- **Composer**
- **Node.js** dan **NPM**

### Setup Pertama Kali

Jalankan perintah berikut untuk setup otomatis (install dependencies, .env, key, migrate, npm install, build):

```bash
composer setup
```

### Menjalankan Aplikasi

#### Opsi 1: Satu Perintah (Recommended)

Jalankan semua service sekaligus (server, queue, logs, vite):

```bash
composer dev
```

Perintah ini akan menjalankan 4 service secara bersamaan:
- `php artisan serve` - Server Laravel (http://localhost:8000)
- `php artisan queue:listen` - Antrian background job
- `php artisan pail` - Log monitoring
- `npm run dev` - Vite dev server (hot reload CSS/JS)

#### Opsi 2: Manual (2 Terminal)

Buka 2 terminal terpisah, lalu jalankan:

**Terminal 1 - Server Laravel:**
```bash
php artisan serve
```

**Terminal 2 - Vite (CSS/JS):**
```bash
npm run dev
```

> **Catatan:** `npm run dev` wajib dijalankan agar CSS Tailwind dan JavaScript berfungsi dengan benar.

### Setup Database

Jika database belum di-setup atau ingin reset dari awal:

```bash
php artisan migrate:fresh --seed
```

Perintah ini akan:
- Menghapus semua data yang ada
- Membuat tabel baru dari awal
- Menjalankan seeder untuk membuat akun admin dan customer test

### Jenis File yang Diizinkan

| Ekstensi | Tipe |
|----------|------|
| PDF | Dokumen PDF |
| DOC | Microsoft Word (lama) |
| DOCX | Microsoft Word |
| PPT | Microsoft PowerPoint (lama) |
| PPTX | Microsoft PowerPoint |
| XLS | Microsoft Excel (lama) |
| XLSX | Microsoft Excel |
| JPG | Gambar JPEG |
| JPEG | Gambar JPEG |
| PNG | Gambar PNG |

**Batas ukuran file:** 100 MB

### Error Umum

| Pesan Error | Penyebab | Solusi |
|-------------|----------|--------|
| "Email atau password salah" | Email/password tidak sesuai | Periksa kembali kredensial |
| "Akun ini bukan admin" | Login customer di halaman admin | Gunakan halaman login customer |
| "Akun ini bukan customer" | Login admin di halaman customer | Gunakan halaman login admin |
| "File wajib diupload" | Tidak ada file yang dipilih | Pilih file terlebih dahulu |
| "Ekstensi file tidak diizinkan" | Format file tidak didukung | Gunakan format yang diizinkan |
| "Ukuran file maksimal 100MB" | File terlalu besar | Kompres atau gunakan file lebih kecil |
| "Akses Ditolak" | Mencoba akses halaman yang bukan miliknya | Login dengan akun yang sesuai |

### Format Nomor Pesanan

Format: `ORD-YYYYMMDD-NNNN`
- `ORD-`: Prefix tetap
- `YYYYMMDD`: Tanggal pembuatan (contoh: 20260811)
- `NNNN`: Nomor urut 4 digit (contoh: 0001, 0002, dst)

Contoh: `ORD-20260811-0001`, `ORD-20260811-0002`

### Layout Aplikasi

| Layout | Digunakan Oleh |
|--------|----------------|
| Guest | Halaman login dan registrasi |
| App (Customer) | Semua halaman customer (dashboard, pesanan, notifikasi) |
| Admin | Semua halaman admin (dashboard, pesanan) |

### Navigasi Customer

```
PrintShop | Dashboard | Riwayat | Notifikasi [badge] | User Dropdown (Logout)
```

### Navigasi Admin (Sidebar)

```
PrintShop Admin
  - Dashboard
  - Pesanan
---
[Nama Admin] [Admin]
  - Logout
```

---

## Link Akses

| Halaman | URL |
|---------|-----|
| Login Customer | `http://localhost:8000/login` |
| Registrasi Customer | `http://localhost:8000/register` |
| Login Admin | `http://localhost:8000/admin/login` |

---

## Akun Login

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@printshop.com` | `password` |
| Customer | `customer@example.com` | `password` |

---

## Overview Fitur

### Fitur Customer

| Fitur | Deskripsi |
|-------|-----------|
| Dashboard | Menampilkan ringkasan jumlah pesanan per status + 5 pesanan terakhir |
| Upload Pesanan | Unggah file dokumen (PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, JPG, PNG) maks 100MB dengan catatan opsional |
| Riwayat Pesanan | Daftar seluruh pesanan dengan status, pagination 15 per halaman |
| Detail Pesanan | Melihat detail pesanan + timeline status |
| Notifikasi | Melihat notifikasi perubahan status pesanan, tandai sudah dibaca |

### Fitur Admin

| Fitur | Deskripsi |
|-------|-----------|
| Dashboard | Statistik pesanan hari ini, minggu ini, bulan ini, total customer + jumlah per status |
| Daftar Pesanan | Seluruh pesanan dengan pencarian, filter status, dan sorting |
| Detail Pesanan | Melihat detail + mengunduh file + mengubah status (Proses/Selesai/Batalkan) |
| Download File | Mengunduh file yang diunggah customer |

---

## Status Workflow Pesanan

```
PENDING -------> PROCESSING -------> COMPLETED
   |
   +-----------> CANCELLED
```

| Status | Label | Keterangan |
|--------|-------|------------|
| Pending | Menunggu | Pesanan baru diterima, belum diproses |
| Processing | Diproses | Admin sedang memproses pesanan |
| Completed | Selesai | Pesanan selesai diproses |
| Cancelled | Dibatalkan | Pesanan dibatalkan (dengan alasan) |

### Aturan Transisi

| Dari | Ke | Aksi |
|------|----|------|
| Pending | Processing | Admin klik "Mulai Proses" |
| Pending | Cancelled | Admin klik "Batalkan" + isi alasan |
| Processing | Completed | Admin klik "Selesaikan" |
| Completed | (terminal) | Tidak bisa diubah lagi |
| Cancelled | (terminal) | Tidak bisa diubah lagi |

---

## Panduan Demo Step-by-Step

### Skenario 1: Registrasi -> Upload Pesanan -> Admin Proses -> Selesai

#### Langkah 1: Registrasi Akun Customer

1. Buka `http://localhost:8000/register`
2. Isi form:
   - **Nama:** Budi Santoso
   - **Email:** budi@example.com
   - **Password:** secret123
   - **Konfirmasi Password:** secret123
3. Klik **"Daftar"**
4. Akan dialihkan ke halaman login dengan pesan sukses

#### Langkah 2: Login sebagai Customer

1. Buka `http://localhost:8000/login`
2. Masukkan email: `budi@example.com`
3. Masukkan password: `secret123`
4. Klik **"Login"**
5. Akan dialihkan ke dashboard customer (kosong karena belum ada pesanan)

#### Langkah 3: Upload Pesanan

1. Klik **"+ Upload Pesanan"** di dashboard
2. **Upload file:**
   - Drag and drop file ke area upload, atau
   - Klik **"Klik untuk memilih"** untuk browse file
   - Contoh: unggah file PDF bernama `dokumen.pdf`
3. **Tambah catatan (opsional):**
   - Tulis: `Tolong cetak warna, 2 rangkap`
4. Klik **"Kirim Pesanan"**
5. Akan dialihkan ke halaman detail pesanan
   - Nomor pesanan: `ORD-YYYYMMDD-0001`
   - Status: **Pending** (badge kuning)
   - Timeline menunjukkan "Pesanan Diterima"

#### Langkah 4: Cek Notifikasi Customer

1. Klik **"Notifikasi"** di navbar
2. Terdapat notifikasi: **"Pesanan Diterima"**
   - Isi: "Pesanan ORD-YYYYMMDD-0001 telah diterima dan akan segera kami proses."
3. Klik **"Tandai dibaca"** untuk menandai sudah dibaca

#### Langkah 5: Login sebagai Admin

1. Logout dari akun customer (klik nama user -> Logout)
2. Buka `http://localhost:8000/admin/login`
3. Masukkan email: `admin@printshop.com`
4. Masukkan password: `password`
5. Klik **"Login"**
6. Dashboard admin menampilkan:
   - Hari Ini: 1
   - Pending: 1
   - Total Customer: 1 (atau lebih)

#### Langkah 6: Admin Melihat Pesanan

1. Klik kartu **"Pending"** di dashboard, atau klik **"Pesanan"** di sidebar
2. Tabel pesanan muncul dengan pesanan dari Budi Santoso
3. Klik **"Detail"** untuk melihat detail pesanan

#### Langkah 7: Admin Mengunduh File

1. Di halaman detail pesanan, klik **"Download File"**
2. File akan terunduh ke komputer Anda

#### Langkah 8: Admin Memproses Pesanan

1. Di halaman detail pesanan, klik **"Mulai Proses"**
2. Konfirmasi dialog: "Mulai memproses pesanan ORD-YYYYMMDD-0001?"
3. Klik **OK**
4. Status berubah menjadi **Diproses** (badge biru)
5. Customer menerima notifikasi: "Pesanan Diproses"

#### Langkah 9: Admin Menyelesaikan Pesanan

1. Klik **"Selesaikan"**
2. Konfirmasi dialog: "Selesaikan pesanan ORD-YYYYMMDD-0001?"
3. Klik **OK**
4. Status berubah menjadi **Selesai** (badge hijau)
5. Waktu selesai tercatat
6. Customer menerima notifikasi: "Pesanan Selesai"

#### Langkah 10: Customer Memverifikasi Status

1. Login kembali sebagai customer
2. Dashboard menampilkan:
   - Total Pesanan: 1
   - Selesai: 1
3. Klik pesanan di tabel "Pesanan Terakhir"
4. Detail pesanan menampilkan timeline lengkap: Diterima -> Diproses -> Selesai
5. Halaman notifikasi menampilkan 3 notifikasi (Diterima, Diproses, Selesai)

---

### Skenario 2: Admin Membatalkan Pesanan

#### Langkah 1: Customer Mengirim Pesanan

1. Login sebagai customer
2. Upload pesanan baru (ikuti Langkah 3 di Skenario 1)
3. Status pesanan: **Pending**

#### Langkah 2: Admin Membatalkan

1. Login sebagai admin
2. Buka detail pesanan yang berstatus Pending
3. Klik **"Batalkan"**
4. Modal dialog muncul dengan textarea "Alasan Pembatalan"
5. Isi alasan: `File tidak sesuai format yang diminta`
6. Klik **"Batalkan Pesanan"**
7. Status berubah menjadi **Dibatalkan** (badge merah)
8. Alasan pembatalan ditampilkan di halaman detail

#### Langkah 3: Customer Melihat Pembatalan

1. Login sebagai customer
2. Buka detail pesanan
3. Alasan pembatalan ditampilkan dalam kotak merah
4. Timeline menunjukkan status "Dibatalkan"
5. Notifikasi diterima: "Pesanan Dibatalkan. Alasan: File tidak sesuai format yang diminta"

---

### Skenario 3: Pencarian dan Filter Pesanan (Admin)

#### Langkah 1: Mencari Pesanan

1. Login sebagai admin
2. Buka halaman **"Pesanan"**
3. Ketik nomor pesanan atau nama file di kolom pencarian
4. Klik **"Cari"**
5. Hasil pencarian muncul sesuai query

#### Langkah 2: Filter berdasarkan Status

1. Pilih status dari dropdown filter (Pending / Diproses / Selesai / Dibatalkan)
2. Klik **"Cari"**
3. Hanya pesanan dengan status tersebut yang ditampilkan

#### Langkah 3: Sorting

1. Pilih opsi sorting dari dropdown:
   - **Terbaru** (default): Pesanan terbaru di atas
   - **Terlama**: Pesanan paling lama di atas
   - **Status**: Urutkan berdasarkan status
   - **Customer**: Urutkan berdasarkan nama customer A-Z
2. Klik **"Cari"**
