# AI Implementation Guide

## Online Print Order Management System

### Laravel 13 + MySQL + Responsive Web

---

# TABLE OF CONTENTS

1. [Project Overview](#project-overview)
2. [Development Principles](#development-principles)
3. [Architecture & File Structure](#architecture--file-structure)
4. [User Roles & Permissions](#user-roles--permissions)
5. [Authentication](#authentication)
6. [Database Design](#database-design)
7. [Storage & File Management](#storage--file-management)
8. [File Validation Rules](#file-validation-rules)
9. [Order Status Flow](#order-status-flow)
10. [Service Classes](#service-classes)
11. [Policy & Authorization](#policy--authorization)
12. [Middleware](#middleware)
13. [Form Request Validation](#form-request-validation)
14. [Customer Flow](#customer-flow)
15. [Admin Flow](#admin-flow)
16. [Notification System](#notification-system)
17. [Jobs & Queue](#jobs--queue)
18. [Search, Filter & Sort](#search-filter--sort)
19. [Security](#security)
20. [Error Handling](#error-handling)
21. [Edge Cases (Comprehensive)](#edge-cases-comprehensive)
22. [Blade Views & UI](#blade-views--ui)
23. [Routes](#routes)
24. [Testing](#testing)
25. [Code Quality](#code-quality)
26. [Performance & Optimization](#performance--optimization)
27. [Logging & Monitoring](#logging--monitoring)
28. [Deployment Checklist](#deployment-checklist)
29. [Future Ready](#future-ready)
30. [Final Requirement](#final-requirement)

---

# PROJECT OVERVIEW

Bangun sebuah aplikasi web responsive menggunakan **Laravel 13** dan **MySQL**.

Aplikasi digunakan oleh sebuah usaha jasa print.

**Customer dapat:**

- Login
- Upload file
- Memberikan catatan
- Melihat status pesanan
- Mendapat notifikasi ketika pesanan selesai

**Admin dapat:**

- Login
- Melihat seluruh pesanan
- Download file customer
- Mengubah status pesanan
- Mengirim notifikasi bahwa pesanan selesai

Website harus mobile responsive.

Jangan menggunakan SPA.

Gunakan Laravel Blade.

Gunakan TailwindCSS.

---

# DEVELOPMENT PRINCIPLES

Seluruh kode harus mengikuti:

- SOLID Principle
- Clean Architecture
- Laravel Best Practice
- Repository Pattern jika diperlukan
- Service Class untuk business logic
- Form Request Validation
- Policy untuk authorization
- Queue untuk pengiriman notifikasi
- Storage Laravel untuk file upload
- Eloquent ORM
- Database Transaction untuk operasi penting

Tidak boleh ada business logic di Controller.

**Controller hanya:**

- Validasi (via Form Request)
- Memanggil Service
- Return Response

---

# ARCHITECTURE & FILE STRUCTURE

## Directory Tree

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php                    (abstract base)
│   │   ├── Auth/
│   │   │   ├── RegisterController.php
│   │   │   └── LoginController.php
│   │   ├── Customer/
│   │   │   ├── DashboardController.php
│   │   │   ├── OrderController.php
│   │   │   └── NotificationController.php
│   │   └── Admin/
│   │       ├── Auth/
│   │       │   └── LoginController.php
│   │       ├── DashboardController.php
│   │       └── OrderController.php
│   ├── Middleware/
│   │   ├── EnsureIsCustomer.php
│   │   ├── EnsureIsAdmin.php
│   │   └── EnsureIsGuest.php
│   └── Requests/
│       ├── RegisterRequest.php
│       ├── LoginRequest.php
│       ├── StoreOrderRequest.php
│       ├── AdminLoginRequest.php
│       ├── CancelOrderRequest.php
│       └── MarkNotificationReadRequest.php
├── Models/
│   ├── User.php                              (extend - tambah role)
│   ├── Order.php                             (new)
│   └── Notification.php                      (extend - customisasi)
├── Services/
│   ├── OrderService.php
│   ├── NotificationService.php
│   └── FileService.php
├── Policies/
│   ├── OrderPolicy.php
│   └── NotificationPolicy.php
├── Jobs/
│   └── SendNotificationJob.php
├── Enums/
│   ├── OrderStatus.php
│   └── UserRole.php
├── Constants/
│   └── OrderConstant.php
└── Providers/
    └── AppServiceProvider.php

database/
├── migrations/
│   ├── xxxx_xx_xx_000000_add_role_to_users_table.php
│   ├── xxxx_xx_xx_000001_create_orders_table.php
│   └── xxxx_xx_xx_000002_create_notifications_table.php
├── seeders/
│   ├── DatabaseSeeder.php                    (update)
│   └── AdminUserSeeder.php                   (new)
└── factories/
    ├── UserFactory.php                       (update - tambah role state)
    └── OrderFactory.php                      (new)

resources/
├── views/
│   ├── layouts/
│   │   ├── app.blade.php                     (customer layout)
│   │   ├── admin.blade.php                   (admin layout)
│   │   └── guest.blade.php                   (guest layout)
│   ├── components/
│   │   ├── alert.blade.php
│   │   ├── badge.blade.php
│   │   ├── button.blade.php
│   │   ├── card.blade.php
│   │   ├── loading.blade.php
│   │   ├── modal.blade.php
│   │   ├── pagination.blade.php
│   │   ├── search-input.blade.php
│   │   ├── status-badge.blade.php
│   │   ├── toast.blade.php
│   │   └── empty-state.blade.php
│   ├── auth/
│   │   ├── login.blade.php
│   │   ├── register.blade.php
│   │   └── forgot-password.blade.php
│   ├── customer/
│   │   ├── dashboard.blade.php
│   │   ├── orders/
│   │   │   ├── index.blade.php
│   │   │   ├── create.blade.php
│   │   │   └── show.blade.php
│   │   └── notifications/
│   │       └── index.blade.php
│   ├── admin/
│   │   ├── auth/
│   │   │   └── login.blade.php
│   │   ├── dashboard.blade.php
│   │   └── orders/
│   │       ├── index.blade.php
│   │       └── show.blade.php
│   ├── errors/
│   │   ├── 403.blade.php
│   │   ├── 404.blade.php
│   │   ├── 419.blade.php
│   │   ├── 422.blade.php
│   │   └── 500.blade.php
│   └── welcome.blade.php
├── css/
│   └── app.css
└── js/
    ├── app.js
    ├── bootstrap.js
    └── upload.js                             (upload handler)

routes/
└── web.php

tests/
├── Feature/
│   ├── Auth/
│   │   ├── RegisterTest.php
│   │   ├── LoginTest.php
│   │   └── LogoutTest.php
│   ├── Customer/
│   │   ├── DashboardTest.php
│   │   ├── OrderUploadTest.php
│   │   ├── OrderHistoryTest.php
│   │   ├── OrderDetailTest.php
│   │   └── NotificationTest.php
│   ├── Admin/
│   │   ├── DashboardTest.php
│   │   ├── OrderListTest.php
│   │   ├── OrderDetailTest.php
│   │   ├── OrderProcessingTest.php
│   │   ├── OrderCompleteTest.php
│   │   ├── OrderCancelTest.php
│   │   └── DownloadTest.php
│   ├── Authorization/
│   │   ├── CustomerAccessTest.php
│   │   └── AdminAccessTest.php
│   ├── EdgeCase/
│   │   ├── UploadEdgeCaseTest.php
│   │   ├── ConcurrentAccessTest.php
│   │   └── FileIntegrityTest.php
│   └── Notification/
│       ├── NotificationCreatedTest.php
│       └── NotificationReadTest.php
└── Unit/
    ├── Services/
    │   ├── OrderServiceTest.php
    │   ├── NotificationServiceTest.php
    │   └── FileServiceTest.php
    └── Enums/
        └── OrderStatusTest.php
```

## Dependency Flow

```
Controller -> Form Request (validasi)
           -> Service (business logic)
           -> Policy (authorization)
           -> Model (database)
           -> View (return response)

Service -> Model (database operations)
        -> FileService (file operations)
        -> NotificationService (notification)
        -> Job (queue async)

Job -> NotificationService (create notification)
```

## Naming Conventions

- Model: singular, PascalCase (`Order`, `User`)
- Controller: plural, PascalCase (`OrderController`)
- Service: singular, PascalCase + "Service" suffix (`OrderService`)
- Policy: singular, PascalCase + "Policy" suffix (`OrderPolicy`)
- Migration: snake_case, timestamp prefix
- View: snake_case, dot notation (`customer.orders.index`)
- Route name: dot notation (`customer.orders.index`)
- URL: kebab-case (`/admin/orders/{order}`)

---

# USER ROLES & PERMISSIONS

Hanya terdapat dua role: `customer` dan `admin`.

## Enum Definition

```php
// app/Enums/UserRole.php
enum UserRole: string
{
    case CUSTOMER = 'customer';
    case ADMIN = 'admin';
}
```

## Customer

**Permission:**

- Register
- Login
- Logout
- Upload file
- Melihat histori pesanan sendiri
- Melihat status pesanan
- Melihat detail pesanan sendiri
- Download invoice (optional)
- Menerima notifikasi
- Menandai notifikasi sebagai sudah dibaca

**Tidak boleh:**

- Mengakses admin dashboard atau admin routes
- Melihat pesanan orang lain
- Mengubah status pesanan
- Download file pesanan orang lain
- Melihat data customer lain
- Mengakses file storage langsung
- Melakukan operasi CRUD pada data customer lain

## Admin

**Permission:**

- Login
- Logout
- Melihat seluruh pesanan
- Download file customer
- Mengubah status pesanan (Pending->Processing, Processing->Completed, Pending->Cancelled)
- Mengirim notifikasi
- Melihat dashboard statistik
- Search pesanan
- Filter pesanan
- Sort pesanan
- Pagination

**Tidak boleh:**

- Register melalui halaman register
- Upload file sebagai customer
- Melihat catatan password customer
- Menghapus file dari storage tanpa alasan
- Mengubah data customer
- Melihat pesanan yang sudah dihapus

Admin dibuat menggunakan Seeder.

---

# AUTHENTICATION

Gunakan Laravel Breeze.

## Middleware

Buat 3 middleware:

- `EnsureIsCustomer` - hanya untuk customer
- `EnsureIsAdmin` - hanya untuk admin
- `EnsureIsGuest` - hanya untuk guest (belum login)

Role checking wajib di setiap middleware.

## Registration

### POSITIVE CASE

- Customer mengisi form register (name, email, password, confirm password)
- Semua field valid
- Email belum terdaftar
- Password minimal 8 karakter
- Password dan confirm password cocok
- User dibuat dengan role `customer`
- Password di-hash menggunakan bcrypt
- Redirect ke halaman login dengan success message "Registrasi berhasil. Silakan login."

### NEGATIVE CASE

| Field | Kondisi | Error Message |
|-------|---------|---------------|
| name | kosong | "Nama wajib diisi" |
| name | mengandung angka | "Nama hanya boleh mengandung huruf dan spasi" |
| name | > 255 karakter | "Nama maksimal 255 karakter" |
| email | kosong | "Email wajib diisi" |
| email | format tidak valid | "Format email tidak valid" |
| email | sudah terdaftar | "Email sudah digunakan" |
| password | kosong | "Password wajib diisi" |
| password | < 8 karakter | "Password minimal 8 karakter" |
| password | hanya spasi | "Password minimal 8 karakter" |
| confirm_password | kosong | "Konfirmasi password wajib diisi" |
| confirm_password | tidak cocok | "Konfirmasi password tidak cocok" |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| Email case insensitive (`User@Example.com`) | treat sebagai email yang sama |
| Email dengan karakter spesial (`user+tag@example.com`) | diterima |
| Register dengan JavaScript disabled | form tetap bisa disubmit (server-side validation) |
| Concurrent registration dengan email yang sama | hanya satu yang berhasil (unique constraint) |
| User mencoba inject role `admin` melalui request | diabaikan, selalu role `customer` |
| User mencoba inject field tambahan (`role`, `id`, `created_at`) | diabaikan oleh `$fillable` |
| Password sangat panjang (> 72 bytes) | diterima, bcrypt handle |
| Password mengandung unicode/emoji | diterima |
| Nama mengandung karakter CJK/Arabic | diterima (huruf dan spasi) |
| Nama hanya 1 karakter | diterima |
| Email dengan IP address (`user@[192.168.1.1]`) | ditolak oleh validasi email |
| User submit form berkali-kali dengan cepat | hanya 1 user dibuat |

## Login

### POSITIVE CASE

- Customer mengisi email dan password
- Credentials benar
- User adalah customer
- Login berhasil
- Session dibuat di database
- Remember token di-set jika "Remember Me" dicentang
- Redirect ke dashboard customer

### NEGATIVE CASE

| Kondisi | Error Message |
|---------|---------------|
| Email tidak terdaftar | "Email atau password salah" |
| Password salah | "Email atau password salah" |
| Email kosong | "Email wajib diisi" |
| Password kosong | "Password wajib diisi" |
| Email format tidak valid | "Format email tidak valid" |
| Login gagal 5 kali berturut-turut | "Terlalu banyak percobaan. Coba lagi dalam X menit" |

**PENTING:** Pesan error harus sama untuk email tidak terdaftar dan password salah. Jangan specifikan mana yang salah untuk mencegah user enumeration.

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| Admin coba login melalui halaman customer | error "Akun ini adalah admin. Silakan login di halaman admin" |
| Customer coba login melalui halaman admin | error "Akun ini adalah customer. Silakan login di halaman customer" |
| User sudah login lalu akses halaman login | redirect ke dashboard |
| Session expired | redirect ke login dengan message "Session expired. Silakan login kembali" |
| Login dengan cookies disabled | error handler, tampilkan instruksi |
| Multiple login dari device berbeda | semua session valid (kecuali logout) |
| User mencoba brute force password | rate limiting aktif, 5 attempt per menit per IP |
| User login dengan "Remember Me" | session lifetime lebih lama (30 hari) |
| User login tanpa "Remember Me" | session lifetime 120 menit |
| Session conflict (2 login bersamaan) | keduanya valid, session terpisah |
| User login lalu password diubah admin | session tetap valid sampai expiry |

## Logout

### POSITIVE CASE

- User klik logout
- Session dihapus dari database
- Cookie dihapus
- CSRF token di-regenerate
- Redirect ke halaman login atau home

### NEGATIVE CASE

| Kondisi | Behavior |
|---------|----------|
| Session sudah expired | redirect ke login (tidak error) |
| User klik logout berkali-kali | tidak error, redirect ke login |
| Database error saat logout | session tetap dihapus client-side |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| Logout di satu tab | semua tab yang lain juga logout (session dihapus di server) |
| User logout lalu klik browser back | redirect ke login |
| User logout lalu submit form | 419 Page Expired (CSRF token sudah tidak valid) |
| User logout dari mobile | desktop juga logout (shared session) |
| User logout saat ada upload berjalan | upload dibatalkan, file cleanup |

---

# DATABASE DESIGN

## users

| Column | Type | Nullable | Default | Notes |
|--------|------|----------|---------|-------|
| id | bigint | NO | auto | primary key |
| name | varchar(255) | NO | | required |
| email | varchar(255) | NO | | unique, required |
| email_verified_at | timestamp | YES | null | nullable |
| password | varchar(255) | NO | | hashed (bcrypt) |
| role | varchar(20) | NO | 'customer' | 'customer' or 'admin' |
| remember_token | varchar(100) | YES | null | |
| created_at | timestamp | NO | | |
| updated_at | timestamp | NO | | |

**Indexes:**
- Primary: `id`
- Unique: `email`

**Constraints:**
- `email` unique di database level
- `role` hanya boleh 'customer' atau 'admin' (validate di application level)
- `password` selalu hashed (bcrypt via Laravel `Hash::make()`)
- `email_verified_at` tidak wajib (nullable)

**Migration Notes:**
- Tambah kolom `role` ke tabel `users` yang sudah ada
- Default value: 'customer'
- Semua user yang sudah ada otomatis menjadi 'customer'

## orders

| Column | Type | Nullable | Default | Notes |
|--------|------|----------|---------|-------|
| id | bigint | NO | auto | primary key |
| user_id | bigint | NO | | foreign key -> users.id (cascade delete) |
| order_number | varchar(50) | NO | | unique, auto generated |
| original_filename | varchar(255) | NO | | nama file asli customer |
| stored_filename | varchar(255) | NO | | nama file random di storage |
| file_size | bigint | NO | | dalam bytes |
| mime_type | varchar(100) | NO | | MIME type file |
| notes | text | YES | null | catatan customer |
| status | varchar(20) | NO | 'pending' | 'pending', 'processing', 'completed', 'cancelled' |
| cancel_reason | text | YES | null | wajib saat status = cancelled |
| completed_at | timestamp | YES | null | waktu pesanan selesai |
| created_at | timestamp | NO | | waktu pesanan dibuat |
| updated_at | timestamp | NO | | waktu terakhir diupdate |

**Indexes:**
- Primary: `id`
- Unique: `order_number`
- Index: `user_id` (untuk query by customer)
- Index: `status` (untuk filter by status)
- Index: `created_at` (untuk sorting)
- Composite: `user_id, status` (untuk dashboard stats)

**Constraints:**
- `user_id` harus exist di users table (foreign key)
- `status` hanya boleh salah satu dari enum
- `order_number` harus unique dan auto-generated
- `file_size` harus positif
- `mime_type` harus valid
- `cancel_reason` wajib diisi saat status = cancelled

**Order Number Format:** `ORD-YYYYMMDD-XXXX`

Contoh: `ORD-20260704-0001`

XXXX = urutan 4 digit, reset setiap hari

## notifications

| Column | Type | Nullable | Default | Notes |
|--------|------|----------|---------|-------|
| id | bigint | NO | auto | primary key |
| user_id | bigint | NO | | foreign key -> users.id (cascade delete) |
| order_id | bigint | YES | null | foreign key -> orders.id (set null on delete) |
| type | varchar(255) | NO | | class notifikasi Laravel |
| title | varchar(255) | NO | | judul notifikasi |
| message | text | NO | | isi notifikasi |
| data | json | YES | null | data tambahan |
| is_read | boolean | NO | false | status baca |
| read_at | timestamp | YES | null | waktu dibaca |
| created_at | timestamp | NO | | |
| updated_at | timestamp | NO | | |

**Indexes:**
- Primary: `id`
- Index: `user_id` (untuk query by user)
- Index: `order_id` (untuk query by order)
- Index: `is_read` (untuk filter unread)
- Composite: `user_id, is_read` (untuk badge count)

**Constraints:**
- `user_id` harus exist
- `order_id` harus exist (nullable untuk system notification)
- `title` dan `message` tidak kosong
- `is_read` harus boolean

---

# STORAGE & FILE MANAGEMENT

## Penyimpanan

Semua file upload disimpan pada:

```
storage/app/private/orders/
```

Tidak boleh disimpan di public.

Gunakan Storage Laravel dengan disk `private`.

## Disk Configuration

```php
// config/filesystems.php tambah:
'private' => [
    'driver' => 'local',
    'root' => storage_path('app/private'),
    'permissions' => [
        'file' => [
            'public' => false,
        ],
        'directory' => [
            'public' => false,
        ],
    ],
],
```

## Penamaan File

Nama file harus di-random menggunakan `Str::uuid()`.

Format: `{uuid}.{extension}`

Contoh: `2f91a88f-83a4-4d1b-b5c2-7e3d8f9a1b2c.pdf`

Original filename tetap disimpan di database (kolom `original_filename`).

## FileService Specification

```php
// app/Services/FileService.php
class FileService
{
    // Upload file ke storage, return stored_filename
    public function upload(UploadedFile $file): string;

    // Download file dari storage, return file contents
    public function download(string $storedFilename): StreamedResponse;

    // Cek apakah file ada di storage
    public function exists(string $storedFilename): bool;

    // Hapus file dari storage
    public function delete(string $storedFilename): bool;

    // Generate signed URL untuk download
    public function getSignedUrl(string $storedFilename, int $expiration = 60): string;
}
```

## Upload

### POSITIVE CASE

1. Customer upload file PDF 5MB
2. File valid (extension & MIME type sesuai)
3. File size <= 100MB
4. `FileService::upload()` dipanggil
5. File disimpan di `storage/app/private/orders/` dengan nama UUID
6. Database record dibuat via `OrderService::createOrder()`
7. Order number auto-generated (ORD-YYYYMMDD-XXXX)
8. Status default: `pending`
9. Success message ditampilkan
10. Redirect ke order detail

### NEGATIVE CASE

| Kondisi | Error Message | Action |
|---------|---------------|--------|
| File extension tidak diizinkan | "Ekstensi file tidak diizinkan" | reject, tidak ada upload |
| MIME type tidak sesuai | "Tipe file tidak valid" | reject, tidak ada upload |
| File size > 100MB | "Ukuran file maksimal 100MB" | reject, tidak ada upload |
| File kosong (0 bytes) | "File tidak boleh kosong" | reject, tidak ada upload |
| Tidak ada file yang diupload | "File wajib diupload" | reject, tidak ada upload |
| Catatan > 1000 karakter | "Catatan maksimal 1000 karakter" | reject, tidak ada upload |
| Storage penuh | "Gagal menyimpan file" | rollback, hapus file, error |
| Database gagal insert | "Gagal membuat pesanan" | rollback, hapus file, error |
| Session expired | redirect ke login | tidak ada file/database record |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| File dengan nama sangat panjang (> 255 karakter) | gunakan stored filename, original filename di-truncate di UI |
| File dengan karakter spesial pada nama (`@#$%^&*()`) | diterima, original filename disimpan apa adanya |
| Customer upload file yang sama 2 kali | diterima sebagai 2 order berbeda |
| Customer refresh browser saat upload sedang berjalan | gunakan idempotency token untuk cegah duplicate |
| Double click tombol upload | hanya 1 order yang dibuat (disable button setelah klik pertama) |
| Upload file tepat 100MB | diterima |
| Upload file 100.1MB | ditolak |
| Upload file 1 byte | ditolak (file kosong) |
| Concurrent upload dari 2 tab berbeda | 2 order berbeda dibuat |
| File extension .jpg tapi MIME type image/png | ditolak (MIME mismatch) |
| File extension .doc tapi sebenarnya .exe yang di-rename | ditolak berdasarkan MIME type detection |
| Network putus saat upload | file tidak tersimpan, tidak ada database record, user bisa retry |
| Upload dengan JavaScript disabled | form tetap bisa disubmit (progress bar tidak berfungsi) |
| File yang corrupt | tetap disimpan, admin yang mengecek |
| Upload dari mobile dengan koneksi lambat | timeout lebih lama, tampilkan loading indicator |
| Upload file dengan nama mengandung emoji | original filename disimpan, displayed dengan benar |
| Upload dari browser yang tidak support drag-and-drop | form input tetap berfungsi |
| Upload file dengan ukuran tepat batas (100.00 MB) | diterima |
| Upload file dengan spasi di nama | original filename disimpan apa adanya |
| Upload file dengan unicode filename | disimpan dengan benar |

---

# FILE VALIDATION RULES

## Allowed Extensions

```
pdf, doc, docx, ppt, pptx, xls, xlsx, jpg, jpeg, png
```

## Maximum Size

100 MB (104857600 bytes)

## Rejected Extensions (Blacklist)

```
exe, bat, apk, zip, rar, iso, dll, php, js, html, svg, sh, cmd, com, vbs, msi, scr, pif, reg, inf
```

## MIME Type Validation

Selain extension, wajib cek MIME type menggunakan `finfo_file()` atau Laravel's `mimetypes` rule.

### Allowed MIME Types

| Extension | MIME Type |
|-----------|-----------|
| pdf | application/pdf |
| doc | application/msword |
| docx | application/vnd.openxmlformats-officedocument.wordprocessingml.document |
| ppt | application/vnd.ms-powerpoint |
| pptx | application/vnd.openxmlformats-officedocument.presentationml.presentation |
| xls | application/vnd.ms-excel |
| xlsx | application/vnd.openxmlformats-officedocument.spreadsheetml.sheet |
| jpg, jpeg | image/jpeg |
| png | image/png |

### POSITIVE CASE

- File PDF dengan extension .pdf dan MIME application/pdf -> diterima
- File DOCX dengan extension .docx dan MIME application/vnd.openxmlformats-officedocument.wordprocessingml.document -> diterima
- File JPG dengan extension .jpg dan MIME image/jpeg -> diterima
- File PNG dengan extension .png dan MIME image/png -> diterima

### NEGATIVE CASE

- File dengan extension .exe -> ditolak meskipun MIME valid
- File dengan extension .pdf tapi MIME application/x-executable -> ditolak
- File dengan extension .jpg tapi MIME text/plain -> ditolak
- File dengan extension .doc tapi MIME application/x-msdownload -> ditolak

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| File .doc yang sebenarnya .exe yang di-rename | MIME type detection akan menolak |
| File dengan double extension (`file.pdf.exe`) | ditolak (check extension terakhir) |
| File tanpa extension | ditolak |
| File dengan extension uppercase (`.PDF`, `.JPG`) | diterima (case insensitive) |
| File yang sangat kecil (1-10 bytes) tapi extension valid | ditolak sebagai file kosong |
| File dengan MIME type ambiguous | gunakan `file` command sebagai fallback |
| File dengan extension .jfif | ditolak (tapi bisa ditambahkan sebagai .jpg alternatif) |
| File .heic/.heif | ditolak (belum diizinkan) |
| File dengan corrupt header | tetap diterima, admin cek |
| File 0 bytes dengan extension valid | ditolak |

---

# ORDER STATUS FLOW

## Enum Definition

```php
// app/Enums/OrderStatus.php
enum OrderStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    // Label untuk UI
    public function label(): string;
    public function color(): string;  // untuk badge
    public function icon(): string;   // untuk icon
}
```

## Allowed Transitions

```
Pending -> Processing
Pending -> Cancelled
Processing -> Completed
```

## Forbidden Transitions

```
Completed -> Pending (REJECTED)
Completed -> Processing (REJECTED)
Completed -> Cancelled (REJECTED)
Cancelled -> Pending (REJECTED)
Cancelled -> Processing (REJECTED)
Cancelled -> Completed (REJECTED)
Pending -> Completed (REJECTED - harus via Processing dulu)
Processing -> Pending (REJECTED)
Processing -> Cancelled (REJECTED - hanya bisa cancel dari Pending)
```

## State Validation

Gunakan State Validation pattern di `OrderService`.

```php
public function changeStatus(Order $order, OrderStatus $newStatus, ?string $reason = null): Order
{
    $this->validateTransition($order->status, $newStatus);
    // ... update logic
}

private function validateTransition(OrderStatus $current, OrderStatus $new): void
{
    $allowed = match ($current) {
        OrderStatus::PENDING => [OrderStatus::PROCESSING, OrderStatus::CANCELLED],
        OrderStatus::PROCESSING => [OrderStatus::COMPLETED],
        default => [],
    };

    if (!in_array($new, $allowed)) {
        throw new InvalidStatusTransitionException($current, $new);
    }
}
```

### POSITIVE CASE

- Admin mengubah dari Pending ke Processing -> berhasil, status berubah, updated_at diupdate
- Admin mengubah dari Processing ke Completed -> berhasil, status berubah, completed_at diisi, notifikasi dikirim
- Admin mengubah dari Pending ke Cancelled -> berhasil, status berubah, cancel_reason wajib diisi, notifikasi dikirim

### NEGATIVE CASE

| Transisi | Error Message |
|----------|---------------|
| Completed -> Pending | "Pesanan yang sudah selesai tidak dapat diubah statusnya" |
| Completed -> Processing | "Pesanan yang sudah selesai tidak dapat diubah statusnya" |
| Completed -> Cancelled | "Pesanan yang sudah selesai tidak dapat dibatalkan" |
| Cancelled -> Pending | "Pesanan yang sudah dibatalkan tidak dapat diubah statusnya" |
| Cancelled -> Processing | "Pesanan yang sudah dibatalkan tidak dapat diubah statusnya" |
| Cancelled -> Completed | "Pesanan yang sudah dibatalkan tidak dapat diselesaikan" |
| Pending -> Completed (skip Processing) | "Pesanan harus diproses terlebih dahulu" |
| Processing -> Pending | "Status pesanan tidak dapat dikembalikan" |
| Processing -> Cancelled | "Pesanan yang sedang diproses tidak dapat dibatalkan" |
| Cancel tanpa alasan | "Alasan pembatalan wajib diisi" |
| Alasan < 10 karakter | "Alasan pembatalan minimal 10 karakter" |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| Admin mengubah status di 2 tab bersamaan | gunakan optimistic locking (cek `updated_at` sebelum update) |
| Admin klik tombol status berkali-kali dengan cepat | disable button, cek status terbaru sebelum update |
| Status diubah tepat saat customer melihat detail | customer melihat status terbaru (setelah refresh) |
| Order dalam status Processing terlalu lama (> 7 hari) | admin dashboard menampilkan warning "Lama diproses" |
| Admin mengubah status, lalu langsung refresh | status terbaru ditampilkan |
| Concurrent update dari 2 admin | yang pertama berhasil, yang kedua dapat error "Status sudah berubah oleh admin lain" |
| Admin coba ubah status yang sama | tidak ada perubahan (no-op), success message |
| Status diubah tepat tengah malam | `completed_at` sesuai timestamp server |
| Admin ubah status via API langsung | tetap melalui Service, authorization dicek |

---

# SERVICE CLASSES

## OrderService

```php
// app/Services/OrderService.php

class OrderService
{
    public function __construct(
        private FileService $fileService,
        private NotificationService $notificationService,
    ) {}

    // Generate order number format: ORD-YYYYMMDD-XXXX
    public function generateOrderNumber(): string;

    // Buat order baru (upload + database)
    public function createOrder(User $user, UploadedFile $file, ?string $notes): Order;

    // Ubah status order dengan validasi transisi
    public function changeStatus(Order $order, OrderStatus $newStatus, ?string $reason = null): Order;

    // Proses order (Pending -> Processing)
    public function processing(Order $order): Order;

    // Selesaikan order (Processing -> Completed)
    public function complete(Order $order): Order;

    // Batalkan order (Pending -> Cancelled)
    public function cancel(Order $order, string $reason): Order;

    // Get all orders dengan search, filter, sort
    public function getOrders(?string $search = null, ?string $status = null, ?string $sort = null): LengthAwarePaginator;

    // Get orders milik user tertentu
    public function getUserOrders(User $user, ?string $status = null): LengthAwarePaginator;

    // Get dashboard stats untuk admin
    public function getDashboardStats(): array;

    // Get dashboard stats untuk customer
    public function getCustomerDashboardStats(User $user): array;
}
```

## NotificationService

```php
// app/Services/NotificationService.php

class NotificationService
{
    // Kirim notifikasi ke user
    public function send(User $user, string $title, string $message, ?Order $order = null): void;

    // Kirim notifikasi via queue (async)
    public function queue(User $user, string $title, string $message, ?Order $order = null): void;

    // Tandai notifikasi sebagai sudah dibaca
    public function markAsRead(Notification $notification): void;

    // Tandai semua notifikasi user sebagai sudah dibaca
    public function markAllAsRead(User $user): void;

    // Get unread count untuk badge
    public function getUnreadCount(User $user): int;

    // Get all notifications untuk user
    public function getNotifications(User $user, int $perPage = 15): LengthAwarePaginator;
}
```

## FileService

```php
// app/Services/FileService.php

class FileService
{
    private string $disk = 'private';
    private string $directory = 'orders';

    // Upload file ke storage
    public function upload(UploadedFile $file): string;

    // Download file dari storage
    public function download(string $storedFilename): StreamedResponse;

    // Cek apakah file ada
    public function exists(string $storedFilename): bool;

    // Hapus file dari storage
    public function delete(string $storedFilename): bool;

    // Get file size
    public function getSize(string $storedFilename): int;

    // Get MIME type
    public function getMimeType(string $storedFilename): ?string;

    // Generate signed URL
    public function getSignedUrl(string $storedFilename, int $expirationMinutes = 60): string;

    // Validate file sebelum upload
    public function validate(UploadedFile $file): array;  // return errors
}
```

---

# POLICY & AUTHORIZATION

## OrderPolicy

```php
// app/Policies/OrderPolicy.php

class OrderPolicy
{
    // Admin bisa akses semua order
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role === UserRole::ADMIN) {
            return true;
        }
        return null;
    }

    // Customer bisa view order sendiri
    public function view(User $user, Order $order): bool;

    // Customer bisa create order
    public function create(User $user): bool;

    // Admin bisa update status order
    public function updateStatus(User $user, Order $order): bool;

    // Admin bisa download file order
    public function download(User $user, Order $order): bool;

    // Admin bisa cancel order
    public function cancel(User $user, Order $order): bool;

    // Admin bisa process order
    public function process(User $user, Order $order): bool;

    // Admin bisa complete order
    public function complete(User $user, Order $order): bool;
}
```

## NotificationPolicy

```php
// app/Policies/NotificationPolicy.php

class NotificationPolicy
{
    // User bisa view notifikasi sendiri
    public function view(User $user, Notification $notification): bool;

    // User bisa mark as read notifikasi sendiri
    public function markAsRead(User $user, Notification $notification): bool;

    // User bisa mark all sebagai read
    public function markAllAsRead(User $user): bool;
}
```

### POSITIVE CASE

- Customer hanya bisa lihat pesanan sendiri -> authorization policy aktif
- Admin bisa lihat semua pesanan -> policy `before()` return true
- Guest tidak bisa akses halaman yang dilindungi -> redirect ke login
- Customer bisa lihat notifikasi sendiri -> policy aktif
- Admin bisa download file order -> policy aktif

### NEGATIVE CASE

| Kondisi | Behavior |
|---------|----------|
| Customer coba lihat pesanan orang lain | 403 Forbidden |
| Customer coba akses admin dashboard | 403 Forbidden |
| Admin coba akses customer dashboard | 403 Forbidden |
| User tidak login coba akses protected page | redirect ke login (401) |
| Customer coba update status order | 403 Forbidden |
| Customer coba download file order orang lain | 403 Forbidden |
| Customer coba mark notifikasi orang lain sebagai read | 403 Forbidden |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| Authorization dicek di middleware DAN policy | double protection, tetap aman |
| Policy menggunakan `before()` method untuk admin bypass | admin selalu authorized |
| Authorization error ditampilkan dengan pesan informatif | "Anda tidak memiliki akses ke halaman ini" |
| Policy dicek di Service (bukan hanya Controller) | konsisten di semua layer |
| User role berubah saat sedang online | policy menggunakan data terbaru dari database |

---

# MIDDLEWARE

## EnsureIsCustomer

```php
// app/Http/Middleware/EnsureIsCustomer.php

class EnsureIsCustomer
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        if ($request->user()->role !== UserRole::CUSTOMER) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini');
        }

        return $next($request);
    }
}
```

## EnsureIsAdmin

```php
// app/Http/Middleware/EnsureIsAdmin.php

class EnsureIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->route('admin.login');
        }

        if ($request->user()->role !== UserRole::ADMIN) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini');
        }

        return $next($request);
    }
}
```

## EnsureIsGuest

```php
// app/Http/Middleware/EnsureIsGuest.php

class EnsureIsGuest
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            // Redirect berdasarkan role
            if ($request->user()->role === UserRole::ADMIN) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('customer.dashboard');
        }

        return $next($request);
    }
}
```

## Registration di bootstrap/app.php

```php
->withMiddleware(function (Middleware $middleware): void
{
    $middleware->alias([
        'customer' => EnsureIsCustomer::class,
        'admin' => EnsureIsAdmin::class,
        'guest' => EnsureIsGuest::class,
    ]);
})
```

### POSITIVE CASE

- Customer login -> middleware allow, akses customer routes
- Admin login -> middleware allow, akses admin routes
- Guest tidak login -> redirect ke login

### NEGATIVE CASE

| Kondisi | Behavior |
|---------|----------|
| Customer coba akses admin route | 403 Forbidden |
| Admin coba akses customer route | 403 Forbidden |
| User tidak login coba akses protected route | redirect ke login |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| Role checking dilakukan SEBELUM controller dipanggil | selalu dicek |
| Role disimpan di database, bukan di session | selalu up-to-date |
| Jika role diubah oleh admin lain | user masih bisa akses sampai session expiry |
| Multiple middleware stack | semua dicek secara berurutan |
| Middleware registered sebagai alias | mudah digunakan di route |

---

# FORM REQUEST VALIDATION

## RegisterRequest

```php
// app/Http/Requests/RegisterRequest.php

class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi',
            'name.regex' => 'Nama hanya boleh mengandung huruf dan spasi',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
        ];
    }
}
```

## LoginRequest

```php
// app/Http/Requests/LoginRequest.php

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'password.required' => 'Password wajib diisi',
        ];
    }
}
```

## StoreOrderRequest

```php
// app/Http/Requests/StoreOrderRequest.php

class StoreOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:102400', // 100MB
                function ($attribute, $value, $fail) {
                    $allowedExtensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
                    $allowedMimes = [
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-powerpoint',
                        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'image/jpeg',
                        'image/png',
                    ];

                    $extension = strtolower($value->getClientOriginalExtension());
                    $mimeType = $value->getMimeType();

                    if (!in_array($extension, $allowedExtensions)) {
                        $fail('Ekstensi file tidak diizinkan');
                    }

                    if (!in_array($mimeType, $allowedMimes)) {
                        $fail('Tipe file tidak valid');
                    }

                    if ($value->getSize() === 0) {
                        $fail('File tidak boleh kosong');
                    }
                },
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'File wajib diupload',
            'file.max' => 'Ukuran file maksimal 100MB',
            'notes.max' => 'Catatan maksimal 1000 karakter',
        ];
    }
}
```

## AdminLoginRequest

```php
// app/Http/Requests/AdminLoginRequest.php

class AdminLoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }
}
```

## CancelOrderRequest

```php
// app/Http/Requests/CancelOrderRequest.php

class CancelOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'cancel_reason' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'cancel_reason.required' => 'Alasan pembatalan wajib diisi',
            'cancel_reason.min' => 'Alasan pembatalan minimal 10 karakter',
            'cancel_reason.max' => 'Alasan pembatalan maksimal 1000 karakter',
        ];
    }
}
```

## MarkNotificationReadRequest

```php
// app/Http/Requests/MarkNotificationReadRequest.php

class MarkNotificationReadRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'notification_id' => ['required', 'exists:notifications,id'],
        ];
    }
}
```

---

# CUSTOMER FLOW

## Dashboard Customer

### POSITIVE CASE

1. Customer login -> dashboard ditampilkan
2. Widget menampilkan:
   - Total Order (semua pesanan customer)
   - Pending (pesanan dengan status pending)
   - Processing (pesanan dengan status processing)
   - Completed (pesanan dengan status completed)
   - Notification badge (jumlah notifikasi belum dibaca)
3. Recent order ditampilkan (5 terakhir, diurutkan dari yang terbaru)
4. Notifikasi unread ditampilkan dengan badge
5. Quick action buttons: "Upload Pesanan", "Riwayat", "Notifikasi"

### NEGATIVE CASE

| Kondisi | Behavior |
|---------|----------|
| Customer belum punya order | tampilkan "Belum ada pesanan" dengan tombol "Upload Sekarang" |
| Customer belum ada notifikasi | badge tidak ditampilkan |
| Database error | tampilkan error page yang informatif |
| Session expired | redirect ke login |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| Customer akses dashboard tanpa login | redirect ke login |
| Customer login sebagai admin lalu akses dashboard customer | 403 Forbidden |
| Dashboard diakses dari mobile | responsive layout, widget stack vertikal |
| Dashboard diakses dari tablet | responsive layout, 2 kolom widget |
| Dashboard diakses dari desktop | responsive layout, full width, 4 kolom widget |
| Customer memiliki 1000+ pesanan | query dioptimasi dengan select count, tetap cepat |
| Customer akses dashboard dengan banyak notifikasi | badge menampilkan angka, > 99 ditampilkan "99+" |
| Dashboard diakses saat queue worker down | statistik tetap akurat dari database |
| Customer mengklik tombol upload | redirect ke form upload |
| Customer mengklik pesanan di recent | redirect ke detail pesanan |

## Upload Pesanan

### POSITIVE CASE

1. Customer klik "Upload Pesanan"
2. Form ditampilkan: field file (drag-and-drop + button), textarea catatan
3. Customer pilih file PDF 10MB
4. Customer isi catatan "Tolong cetak warna, 2 rangkap"
5. Klik "Kirim Pesanan"
6. Form Request validasi -> berhasil
7. `OrderService::createOrder()` dipanggil
8. `FileService::upload()` menyimpan file
9. Database record dibuat dengan status `pending`
10. Order number auto-generated (ORD-20260704-0001)
11. Success flash message: "Pesanan berhasil dikirim!"
12. Redirect ke detail pesanan

### NEGATIVE CASE

| Kondisi | Error Message | Behavior |
|---------|---------------|----------|
| Tidak ada file yang dipilih | "File wajib diupload" | form tetap ditampilkan |
| File extension .exe | "Ekstensi file tidak diizinkan" | form tetap ditampilkan |
| File extension .zip | "Ekstensi file tidak diizinkan" | form tetap ditampilkan |
| File size > 100MB | "Ukuran file maksimal 100MB" | form tetap ditampilkan |
| File kosong (0 bytes) | "File tidak boleh kosong" | form tetap ditampilkan |
| Catatan > 1000 karakter | "Catatan maksimal 1000 karakter" | form tetap ditampilkan |
| MIME type tidak sesuai | "Tipe file tidak valid" | form tetap ditampilkan |
| Upload gagal (network error) | "Gagal mengupload file" | tidak ada file/database record |
| Upload gagal (disk penuh) | "Gagal menyimpan file" | rollback, hapus file |
| Session expired | redirect ke login | tidak ada file/database record |
| Multiple validation errors | semua error ditampilkan | form tetap ditampilkan |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| Upload dengan koneksi lambat | tampilkan progress indicator, timeout setelah 5 menit |
| Upload file 99.9MB | diterima |
| Upload file 100.1MB | ditolak |
| Upload file tepat 100.00 MB | diterima |
| Customer upload sambil edit catatan di tab lain | catatan terakhir yang tersimpan |
| Double click tombol kirim | hanya 1 order yang dibuat (disable button + JS check) |
| Upload lalu refresh halaman | cek apakah order sudah dibuat sebelum menampilkan form lagi |
| Upload dari mobile dengan file sangat besar | timeout lebih lama, tampilkan loading |
| Upload file dengan nama file mengandung emoji | original filename disimpan, displayed dengan benar |
| Upload dari browser yang tidak support drag-and-drop | form input tetap berfungsi |
| Upload file dengan spasi di nama | diterima, original filename disimpan |
| Upload file dengan karakter non-ASCII | diterima, original filename disimpan |
| Upload file yang sama dengan customer berbeda | 2 order berbeda |
| Upload file sambil submit form | idempotency token cegah duplicate |
| Upload gagal di tengah | cleanup otomatis, tidak ada file/database record |
| Upload dengan notes kosong | diperbolehkan (notes nullable) |
| Upload dengan notes hanya spasi | disimpan sebagai spasi |

## Riwayat Pesanan

### POSITIVE CASE

1. Customer klik "Riwayat"
2. Daftar pesanan ditampilkan dengan kolom: Order Number, Tanggal, Status, Nama File, Catatan
3. Pagination aktif (10 per halaman)
4. Customer bisa klik detail untuk melihat detail pesanan
5. Sort by default: terbaru di atas
6. Status ditampilkan dengan badge warna

### NEGATIVE CASE

| Kondisi | Behavior |
|---------|----------|
| Customer belum punya pesanan | tampilkan "Belum ada pesanan" + tombol "Upload Sekarang" |
| Tidak ada hasil filter | tampilkan "Tidak ada pesanan ditemukan" |
| Database error | tampilkan error page |
| Customer mencoba akses halaman riwayat user lain | 403 Forbidden |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| Customer memiliki 100+ pesanan | pagination berfungsi dengan benar |
| Customer mencari dengan SQL injection | ditolak oleh parameterized query |
| Customer mencari dengan keyword kosong | tampilkan semua pesanan |
| Sort oleh tanggal terbaru | pesanan terbaru di atas |
| Sort oleh tanggal terlama | pesanan terlama di atas |
| Sort by status | grouping by status |
| Pagination halaman 1 | tombol "Sebelumnya" disabled |
| Pagination halaman terakhir | tombol "Berikutnya" disabled |
| Customer mengklik badge status | filter by status tersebut |
| Customer klik "Upload" dari riwayat | redirect ke form upload |
| Riwayat dengan banyak pesanan | query dioptimasi, loading cepat |

## Detail Pesanan

### POSITIVE CASE

1. Customer klik pesanan tertentu
2. Detail ditampilkan:
   - Nomor Order (bold)
   - Nama File (dengan icon tipe file)
   - Status (badge warna)
   - Tanggal Upload
   - Tanggal Selesai (jika sudah selesai)
   - Catatan
   - Timeline Status (cronologis)
3. Timeline menampilkan perubahan status secara kronologis:
   - Pending: "Pesanan diterima" (tanggal)
   - Processing: "Pesanan sedang diproses" (tanggal)
   - Completed: "Pesanan selesai" (tanggal)
   - Cancelled: "Pesanan dibatalkan - [alasan]" (tanggal)

### NEGATIVE CASE

| Kondisi | Behavior |
|---------|----------|
| Pesanan tidak ditemukan | 404 Not Found |
| Pesanan milik user lain | 403 Forbidden |
| File tidak ditemukan di storage | tampilkan warning "File tidak tersedia" |
| Database error | tampilkan error page |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| Detail pesanan dengan status Pending | timeline hanya menampilkan "Diterima" |
| Detail pesanan dengan status Processing | timeline: "Diterima" -> "Diproses" |
| Detail pesanan dengan status Completed | timeline: "Diterima" -> "Diproses" -> "Selesai" |
| Detail pesanan dengan status Cancelled | timeline: "Diterima" -> "Dibatalkan" dengan alasan |
| Customer akses detail pesanan yang sudah di-cancel | tetap bisa dilihat, status "Cancelled" |
| Customer akses detail pesanan yang sudah selesai | tetap bisa dilihat, ada tanggal selesai |
| Customer akses detail pesanan dari URL manual | authorization dicek |
| Detail pesanan dengan catatan kosong | tampilkan "-" atau "Tidak ada catatan" |
| Detail pesanan dengan file sudah dihapus | tampilkan warning, download disabled |
| Customer klik "Kembali" | redirect ke riwayat |

---

# ADMIN FLOW

## Admin Login

### POSITIVE CASE

1. Admin mengisi email dan password
2. Credentials benar
3. User adalah admin (role = admin)
4. Login berhasil
5. Session dibuat
6. Redirect ke admin dashboard

### NEGATIVE CASE

| Kondisi | Error Message |
|---------|---------------|
| Email tidak terdaftar | "Email atau password salah" |
| Password salah | "Email atau password salah" |
| User adalah customer, bukan admin | "Akun ini bukan admin. Silakan login di halaman customer" |
| Email kosong | "Email wajib diisi" |
| Password kosong | "Password wajib diisi" |
| Rate limit tercapai | "Terlalu banyak percobaan. Coba lagi dalam X menit" |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| Admin login dari device berbeda | session baru dibuat, semua session valid |
| Admin login berkali-kali | semua session valid |
| Admin akses halaman login saat sudah login | redirect ke admin dashboard |
| Admin mencoba akses halaman customer login | 403 atau redirect ke admin login |
| Admin login dengan "Remember Me" | session lifetime lebih lama |
| Admin login tanpa "Remember Me" | session lifetime 120 menit |

## Dashboard Admin

### POSITIVE CASE

1. Admin login -> dashboard ditampilkan
2. Widget menampilkan:
   - Hari Ini: jumlah order hari ini
   - Minggu Ini: jumlah order minggu ini
   - Bulan Ini: jumlah order bulan ini
   - Pending: jumlah order pending
   - Processing: jumlah order processing
   - Completed: jumlah order completed
   - Cancelled: jumlah order cancelled
   - Total Customer: jumlah customer terdaftar
   - Total File Upload: jumlah total file yang diupload
3. Data realtime (query dari database setiap load)
4. Quick links ke order list

### NEGATIVE CASE

| Kondisi | Behavior |
|---------|----------|
| Tidak ada pesanan | widget menampilkan angka 0 |
| Database error | tampilkan error page |
| Queue worker down | statistik tetap akurat dari database |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| Dashboard diakses dari mobile | responsive, widget stack vertikal |
| Dashboard diakses dari tablet | responsive, 2 kolom |
| Dashboard diakses dari desktop | responsive, full width |
| Dashboard dengan 10.000+ pesanan | query dioptimasi dengan count, tetap cepat |
| Dashboard diakses oleh customer | 403 Forbidden |
| Dashboard dengan banyak order hari ini | warning jika > threshold |
| Order Processing > 7 hari | warning "Pesanan lama diproses" |
| Admin klik widget "Pending" | redirect ke order list dengan filter pending |
| Admin klik widget "Total Customer" | redirect ke customer list (jika ada) |

## Daftar Pesanan

### POSITIVE CASE

1. Admin klik "Pesanan"
2. Daftar semua pesanan ditampilkan
3. Kolom: Order Number, Customer, Email, File, Status, Tanggal
4. Sorting tersedia: Terbaru, Terlama, Status, Nama Customer
5. Search tersedia: Order Number, Nama Customer, Email, Nama File
6. Pagination aktif (15 per halaman)
7. Filter by status tersedia (All, Pending, Processing, Completed, Cancelled)
8. Filter by tanggal tersedia
9. Setiap baris ada link ke detail

### NEGATIVE CASE

| Kondisi | Behavior |
|---------|----------|
| Tidak ada pesanan | tampilkan "Belum ada pesanan" |
| Search tidak ada hasil | tampilkan "Tidak ada pesanan ditemukan" |
| Filter menghasilkan 0 hasil | tampilkan "Tidak ada pesanan ditemukan" |
| Database error | tampilkan error page |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| Search dengan keyword sangat panjang | di-truncate ke max length |
| Search dengan SQL injection | ditolak oleh parameterized query |
| Search case insensitive | "ORD" dan "ord" hasilnya sama |
| Filter status "all" | tampilkan semua |
| Filter tanggal "all" | tampilkan semua |
| Sort by "Terbaru" | order by created_at DESC |
| Sort by "Terlama" | order by created_at ASC |
| Sort by "Status" | order by status ASC |
| Sort by "Nama Customer" | order by user.name ASC |
| Pagination halaman 1 | tombol "Sebelumnya" disabled |
| Pagination halaman terakhir | tombol "Berikutnya" disabled |
| Admin mencari dengan input kosong | tampilkan semua |
| Admin mencari dengan spasi saja | tampilkan semua |
| Admin mengklik filter berturut-turut | filter terakhir yang berlaku |
| Admin mengklik sort berturut-turut | toggle ASC/DESC |
| Order list dengan 1000+ data | pagination + indexed query, cepat |
| Admin filter + search bersamaan | keduanya diterapkan |
| Admin buka order list dari URL langsung | tetap berfungsi |
| Order baru diupload customer | muncul di list admin (realtime atau refresh) |

## Detail Pesanan Admin

### POSITIVE CASE

1. Admin klik pesanan tertentu
2. Detail ditampilkan:
   - Customer: nama customer
   - Email: email customer
   - Tanggal Upload: created_at
   - File: nama file + button download
   - Ukuran: file size (formatted)
   - Catatan: notes
   - Status: badge warna
3. Button tersedia sesuai status:
   - Pending: "Mulai Proses" (green), "Batalkan" (red)
   - Processing: "Selesaikan" (green)
   - Completed: tidak ada button aksi
   - Cancelled: tidak ada button aksi, tampilkan alasan cancel

### NEGATIVE CASE

| Kondisi | Behavior |
|---------|----------|
| Pesanan tidak ditemukan | 404 Not Found |
| File tidak ditemukan di storage | tampilkan warning, button download disabled |
| Database error | tampilkan error page |
| Admin tidak punya akses | 403 Forbidden |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| Detail pesanan status Pending | 2 button aktif (Proses, Batalkan) |
| Detail pesanan status Processing | 1 button aktif (Selesaikan) |
| Detail pesanan status Completed | tidak ada button, tampilkan tanggal selesai |
| Detail pesanan status Cancelled | tidak ada button, tampilkan alasan cancel |
| Admin membuka detail yang sedang diupdate admin lain | status terbaru ditampilkan |
| Admin download file yang sudah dihapus | error "File tidak tersedia" |
| Admin membuka detail dari URL manual tidak valid | 404 |
| Admin klik "Kembali" | redirect ke order list |
| Admin klik "Download" | file di-download via signed URL |
| Detail dengan catatan kosong | tampilkan "-" |
| Detail dengan ukuran file besar | tampilkan dalam format MB/GB |

## Admin: Start Processing

### POSITIVE CASE

1. Admin klik "Mulai Proses" pada pesanan dengan status Pending
2. Confirmation dialog muncul: "Mulai memproses pesanan ORD-XXXX?"
3. Admin klik "Ya"
4. Status berubah menjadi Processing
5. updated_at diupdate
6. Success message: "Pesanan sedang diproses"
7. Redirect ke detail pesanan

### NEGATIVE CASE

| Kondisi | Error Message |
|---------|---------------|
| Status bukan Pending | "Hanya pesanan Pending yang bisa diproses" |
| Pesanan tidak ditemukan | 404 |
| Database error | "Gagal mengubah status. Silakan coba lagi" |
| Admin tidak login | redirect ke login |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| Admin klik 2 kali dengan cepat | hanya 1x perubahan (disable button + server check) |
| Admin klik dari 2 tab | yang pertama berhasil, yang kedua error "Status sudah berubah" |
| Admin klik lalu refresh | status terbaru ditampilkan |
| Status diubah tepat saat customer melihat | customer melihat status terbaru setelah refresh |
| Admin ubah status via URL langsung | tetap melalui Service, authorization dicek |

## Admin: Complete Order

### POSITIVE CASE

1. Admin klik "Selesaikan" pada pesanan dengan status Processing
2. Confirmation dialog muncul: "Selesaikan pesanan ORD-XXXX?"
3. Admin klik "Ya"
4. Status berubah menjadi Completed
5. completed_at diisi dengan `now()`
6. Notifikasi dikirim ke customer (via queue)
7. Success message: "Pesanan selesai"
8. Redirect ke detail pesanan

### NEGATIVE CASE

| Kondisi | Error Message |
|---------|---------------|
| Status bukan Processing | "Hanya pesanan Processing yang bisa diselesaikan" |
| Pesanan tidak ditemukan | 404 |
| Database error | "Gagal menyelesaikan pesanan. Silakan coba lagi" |
| Notifikasi gagal dikirim | pesanan tetap selesai (notification via queue, retry) |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| Admin klik 2 kali | hanya 1x perubahan, 1x notifikasi |
| Admin klik lalu refresh | status terbaru ditampilkan |
| completed_at sesuai waktu server | bukan waktu client |
| Admin complete, customer langsung melihat | status terbaru di dashboard |
| Order selesai tepat tengah malam | completed_at sesuai timestamp server |
| Admin complete via URL langsung | tetap melalui Service, authorization dicek |
| Queue worker down | notifikasi tetap dibuat, dikirim saat worker up |

## Admin: Cancel Order

### POSITIVE CASE

1. Admin klik "Batalkan" pada pesanan dengan status Pending
2. Modal/form alasan cancel ditampilkan
3. Admin mengisi alasan: "File tidak sesuai format yang diminta"
4. Validation: alasan minimal 10 karakter
5. Admin klik "Batalkan Pesanan"
6. Status berubah menjadi Cancelled
7. cancel_reason disimpan
8. Notifikasi dikirim ke customer (via queue)
9. Success message: "Pesanan dibatalkan"
10. Redirect ke detail pesanan

### NEGATIVE CASE

| Kondisi | Error Message |
|---------|---------------|
| Status bukan Pending | "Hanya pesanan Pending yang bisa dibatalkan" |
| Alasan kosong | "Alasan pembatalan wajib diisi" |
| Alasan < 10 karakter | "Alasan pembatalan minimal 10 karakter" |
| Alasan > 1000 karakter | "Alasan pembatalan maksimal 1000 karakter" |
| Pesanan tidak ditemukan | 404 |
| Database error | "Gagal membatalkan pesanan. Silakan coba lagi" |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| Admin klik Cancel lalu cancel form | tidak ada perubahan |
| Admin isi alasan dengan karakter spesial | diterima |
| Admin isi alasan dengan HTML | disanitasi (XSS protection) |
| Admin cancel order yang sudah diproses sebagian | tetap bisa dibatalkan (dari Pending) |
| Admin cancel, customer melihat di dashboard | status "Cancelled" dengan alasan |
| Admin coba cancel dari status Processing | error |
| Admin coba cancel dari status Completed | error |
| Admin cancel via URL langsung | tetap melalui Service, authorization dicek |

## Admin: Download File

### POSITIVE CASE

1. Admin klik "Download" pada detail pesanan
2. `FileService::getSignedUrl()` dipanggil
3. Signed URL di-generate (berlaku 60 menit)
4. File di-download ke komputer admin
5. Nama file download: original_filename

### NEGATIVE CASE

| Kondisi | Behavior |
|---------|----------|
| File tidak ditemukan di storage | error "File tidak tersedia" |
| Order tidak ditemukan | 404 |
| Admin tidak login | redirect ke login |
| Admin bukan admin | 403 Forbidden |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| Signed URL expired | error "Link download sudah expired" |
| Admin download file yang sama berkali-kali | setiap kali generate URL baru |
| Admin download dari mobile | file berhasil di-download |
| File berukuran sangat besar (> 50MB) | download tetap berhasil, mungkin lambat |
| Admin download saat storage down | error "Gagal mengakses file" |
| File corrupt | admin mendapat warning |

---

# NOTIFICATION SYSTEM

Gunakan Database Notification Laravel (tabel `notifications`).

## Trigger Notifikasi

| Event | Recipient | Title | Message Template |
|-------|-----------|-------|------------------|
| Order diterima | Customer | "Pesanan Diterima" | "Pesanan {order_number} telah diterima dan akan segera kami proses." |
| Order diproses | Customer | "Pesanan Diproses" | "Pesanan {order_number} sedang kami proses. Mohon menunggu." |
| Order selesai | Customer | "Pesanan Selesai" | "Pesanan {order_number} telah selesai. Silakan cek detail pesanan." |
| Order dibatalkan | Customer | "Pesanan Dibatalkan" | "Pesanan {order_number} dibatalkan. Alasan: {reason}" |

## NotificationService::send()

```php
public function send(User $user, string $title, string $message, ?Order $order = null): void
{
    $notification = new DatabaseNotification();
    $notification->user_id = $user->id;
    $notification->order_id = $order?->id;
    $notification->type = 'order_update';
    $notification->title = $title;
    $notification->message = $message;
    $notification->is_read = false;
    $notification->save();
}
```

### POSITIVE CASE

1. Admin mengubah status -> notifikasi otomatis dikirim ke customer
2. Customer menerima notifikasi -> badge unread bertambah
3. Customer membuka halaman notifikasi -> daftar notifikasi ditampilkan
4. Customer menandai notifikasi sebagai dibaca -> is_read = true, badge berkurang
5. Customer menandai semua notifikasi sebagai dibaca -> semua is_read = true

### NEGATIVE CASE

| Kondisi | Behavior |
|---------|----------|
| Notifikasi gagal dikirim | pesanan tetap berhasil (notifikasi via queue) |
| Customer belum punya notifikasi | tampilkan "Belum ada notifikasi" |
| Notifikasi tidak ditemukan | 404 |
| Database error saat kirim notifikasi | log error, pesanan tetap berhasil |
| Customer coba mark notifikasi orang lain | 403 Forbidden |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| Customer memiliki 1000+ notifikasi | pagination aktif |
| Customer menandai notifikasi sebagai dibaca | update 1 record, badge berkurang |
| Customer menandai semua sebagai dibaca | bulk update, badge = 0 |
| Notifikasi dikirim via queue | delay beberapa detik, tetap akurat |
| Queue worker down | notifikasi tetap tersimpan di database, dikirim saat worker up |
| Customer login di 2 device | notifikasi sinkron (dari database) |
| Customer akses notifikasi saat ada notifikasi baru | notifikasi baru ditampilkan (realtime jika ada broadcast) |
| Notifikasi untuk order yang sudah dihapus | tetap ditampilkan (historical) |
| Notifikasi dengan judul yang sangat panjang | di-truncate di UI (50 karakter) |
| Notifikasi dengan pesan yang sangat panjang | di-truncate di UI (100 karakter) |
| Customer menandai notifikasi yang sudah dibaca | no-op, tidak error |
| Admin melihat notifikasi customer | tidak ditampilkan (admin tidak punya halaman notifikasi) |

---

# JOBS & QUEUE

## SendNotificationJob

```php
// app/Jobs/SendNotificationJob.php

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        private int $userId,
        private string $title,
        private string $message,
        private ?int $orderId = null,
    ) {}

    public function handle(NotificationService $notificationService): void
    {
        $user = User::find($this->userId);
        $order = $this->orderId ? Order::find($this->orderId) : null;

        $notificationService->send($user, $this->title, $this->message, $order);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Failed to send notification', [
            'user_id' => $this->userId,
            'title' => $this->title,
            'error' => $exception->getMessage(),
        ]);
    }
}
```

## Queue Configuration

```php
// config/queue.php
'default' => env('QUEUE_CONNECTION', 'database'),
```

### POSITIVE CASE

1. Notifikasi dikirim via queue -> tidak block request
2. Queue worker memproses job -> notifikasi sampai ke customer
3. Job berhasil -> notifikasi tersimpan di database

### NEGATIVE CASE

| Kondisi | Behavior |
|---------|----------|
| Queue worker down | job tetap tersimpan di database, diproses saat worker up |
| Job gagal | retry 3 kali dengan backoff 60 detik |
| Job gagal setelah 3 kali | ditambahkan ke failed_jobs table |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| Banyak notifikasi dikirim bersamaan | queue memproses secara FIFO |
| Queue penuh | job menunggu, tidak ditolak |
| Job timeout | retry dengan backoff |
| Database disconnect saat proses job | retry setelah reconnect |
| Job duplicate | SerializesModels prevent duplicate |

---

# SEARCH, FILTER & SORT

## Search (Admin)

### POSITIVE CASE

- Admin search "ORD-20260704" -> pesanan dengan nomor tersebut ditampilkan
- Admin search "Budi" -> pesanan customer bernama Budi ditampilkan
- Admin search "budi@example.com" -> pesanan customer dengan email tersebut ditampilkan
- Admin search "file.pdf" -> pesanan dengan file tersebut ditampilkan
- Search case insensitive -> "ORD" dan "ord" hasilnya sama
- Search menggunakan LIKE '%keyword%' dengan parameter binding

### NEGATIVE CASE

| Kondisi | Behavior |
|---------|----------|
| Tidak ada hasil | tampilkan "Tidak ada pesanan ditemukan" |
| Input kosong | tampilkan semua pesanan |
| Input hanya spasi | tampilkan semua pesanan |
| Database error | tampilkan error page |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| Search dengan karakter spesial (`@#$%`) | di-escape, treat sebagai string |
| Search dengan SQL injection (`' OR 1=1`) | ditolak oleh parameterized query |
| Search dengan XSS (`<script>alert(1)</script>`) | disanitasi oleh Blade |
| Search dengan keyword sangat panjang (> 255 karakter) | di-truncate |
| Search dengan wildcard (`*`) | tidak menggunakan wildcard, treat sebagai string biasa |
| Search real-time dengan debounce 300ms | tidak terlalu sering query |
| Search dari mobile | keyboard tidak menutupi hasil |
| Search dengan kombinasi angka dan huruf | berhasil |
| Search dengan unicode (`Budi 印刷`) | berhasil jika ada di database |

## Filter

### POSITIVE CASE

- Filter by status "Pending" -> hanya pesanan Pending
- Filter by status "Processing" -> hanya pesanan Processing
- Filter by status "Completed" -> hanya pesanan Completed
- Filter by status "Cancelled" -> hanya pesanan Cancelled
- Filter by status "All" -> semua pesanan
- Filter by tanggal -> pesanan pada rentang tanggal tertentu
- Kombinasi filter -> hasilnya intersection

### NEGATIVE CASE

| Kondisi | Behavior |
|---------|----------|
| Filter dengan status tidak valid | tampilkan semua |
| Filter dengan tanggal tidak valid | tampilkan semua |
| Database error | tampilkan error page |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| Filter status "Pending" + sort "Terbaru" | hasil terurut dari yang terbaru |
| Filter tanggal 1 Januari - 31 Desember 2026 | semua pesanan 2026 |
| Filter tanggal 1 Januari - 1 Januari 2026 | pesanan pada tanggal tersebut |
| Kombinasi search + filter + sort | ketiganya diterapkan |
| Filter di URL | bisa di-share/bookmark |
| Filter yang sama diklik lagi | toggle (on/off) |

## Sort

### POSITIVE CASE

- Sort by "Tanggal Terbaru" -> order by created_at DESC
- Sort by "Tanggal Terlama" -> order by created_at ASC
- Sort by "Status" -> order by status ASC
- Sort by "Nama Customer" -> order by users.name ASC

### NEGATIVE CASE

| Kondisi | Behavior |
|---------|----------|
| Sort dengan parameter tidak valid | gunakan default (Tanggal Terbaru) |
| Database error | tampilkan error page |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| Sort yang sama diklik 2 kali | toggle ASC/DESC |
| Sort dari URL | bisa di-share/bookmark |
| Sort + filter | hasilnya konsisten |
| Sort by status + banyak order | grouping by status, within group by date |

---

# SECURITY

## CSRF Protection

Semua form harus menggunakan `@csrf` directive.

### POSITIVE CASE

- Form submit dengan CSRF token valid -> berhasil
- CSRF token otomatis di-generate oleh Laravel

### NEGATIVE CASE

| Kondisi | Behavior |
|---------|----------|
| Form submit tanpa CSRF token | 419 Page Expired |
| Form submit dengan CSRF token expired | 419 Page Expired |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| Multiple tabs | setiap tab punya CSRF token sendiri |
| AJAX request | sertakan CSRF token di header `X-CSRF-TOKEN` |
| Token expired setelah 120 menit | redirect ke login |
| CSRF token di-regenerate saat login | mencegah session fixation |

## Rate Limiting

Rate limit login: 5 attempt per menit per IP.

### POSITIVE CASE

- Login berhasil dalam batas rate limit -> berhasil
- Login berhasil setelah rate limit reset -> berhasil

### NEGATIVE CASE

| Kondisi | Behavior |
|---------|----------|
| Login gagal 5 kali berturut-turut | 429 Too Many Requests |
| Rate limit masih aktif | error "Terlalu banyak percobaan" |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| Rate limit per IP | user dengan IP berbeda tidak terpengaruh |
| Rate limit reset otomatis setelah 1 menit | automatic |
| Rate limit untuk register juga aktif | 5 register per menit per IP |
| Rate limit untuk upload | tidak ada (hanya untuk auth) |

## Password Hash

Password selalu di-hash menggunakan bcrypt (`Hash::make()`).

### POSITIVE CASE

- Password di-hash sebelum disimpan
- Password diverifikasi menggunakan `Hash::check()`

### NEGATIVE CASE

| Kondisi | Behavior |
|---------|----------|
| Tidak boleh menyimpan password plain text | SELALU hash |
| Tidak boleh log password | JANGAN pernah log |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| Password sangat panjang (> 72 bytes) | bcrypt otomatis truncate ke 72 bytes |
| Password dengan karakter spesial | di-hash dengan benar |
| Password dengan unicode | di-hash dengan benar |

## Private File Storage

### POSITIVE CASE

- File disimpan di `storage/app/private/orders/`
- Tidak bisa diakses langsung via URL
- Download menggunakan signed route

### NEGATIVE CASE

| Kondisi | Behavior |
|---------|----------|
| User coba akses file langsung via URL | 403 Forbidden |
| Signed URL expired | error "Link download sudah expired" |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| Signed URL bisa di-generate ulang | admin bisa generate ulang |
| File dihapus dari storage tapi masih ada di database | admin mendapat warning |
| File corrupt | admin mendapat warning |

## Signed Download Route

### POSITIVE CASE

- Admin klik download -> signed URL di-generate, file didownload

### NEGATIVE CASE

| Kondisi | Behavior |
|---------|----------|
| Signed URL expired | error |
| File tidak ditemukan | error |
| User tidak punya akses | 403 |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| Signed URL berlaku 60 menit | configurable |
| Download dari mobile | file berhasil di-download |
| Download file besar | tetap berhasil, mungkin lambat |

---

# ERROR HANDLING

## HTTP Status Codes

| Kode | Keterangan | Kapan Digunakan |
|------|------------|-----------------|
| 200 | OK | Request berhasil |
| 201 | Created | Resource berhasil dibuat (register, upload) |
| 302 | Redirect | Setelah login, logout |
| 400 | Bad Request | Request tidak valid |
| 401 | Unauthorized | Belum login |
| 403 | Forbidden | Tidak punya akses |
| 404 | Not Found | Resource tidak ditemukan |
| 419 | Page Expired | CSRF token expired |
| 422 | Validation Error | Input tidak valid |
| 429 | Too Many Requests | Rate limit tercapai |
| 500 | Internal Server Error | Server error |

## Error Pages

Buat custom error page untuk: 403, 404, 419, 422, 500.

Setiap error page harus:

- Informatif (pesan yang jelas)
- Tidak mengekspos detail internal (no stack trace di production)
- Ada tombol "Kembali" atau link ke halaman sebelumnya
- Responsive
- Konsisten dengan design system

## Upload Gagal

### Transaction Pattern

```php
public function createOrder(User $user, UploadedFile $file, ?string $notes): Order
{
    return DB::transaction(function () use ($user, $file, $notes) {
        // 1. Upload file
        $storedFilename = $this->fileService->upload($file);

        try {
            // 2. Buat order
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => $this->generateOrderNumber(),
                'original_filename' => $file->getClientOriginalName(),
                'stored_filename' => $storedFilename,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'notes' => $notes,
                'status' => OrderStatus::PENDING,
            ]);

            return $order;
        } catch (\Exception $e) {
            // 3. Jika database gagal, hapus file
            $this->fileService->delete($storedFilename);
            throw $e;
        }
    });
}
```

### POSITIVE CASE

- Upload berhasil -> file tersimpan, database record dibuat
- Rollback jika database gagal -> file dihapus

### NEGATIVE CASE

| Kondisi | Behavior |
|---------|----------|
| Database insert gagal | rollback, hapus file, return error |
| File gagal disimpan | tidak ada database record, return error |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| Upload gagal di tengah | cleanup otomatis |
| Upload gagal karena disk penuh | rollback, error "Storage penuh" |
| Upload gagal karena izin folder | rollback, error "Gagal menyimpan file" |

## Database Gagal

### POSITIVE CASE

- Database operation berhasil -> data tersimpan
- Database disconnect -> reconnect otomatis

### NEGATIVE CASE

| Kondisi | Behavior |
|---------|----------|
| Database insert gagal | rollback transaction |
| Database update gagal | rollback transaction |
| Database disconnect | error "Gagal menghubungi database" |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| Database timeout | retry 1 kali, lalu error |
| Database deadlock | retry 1 kali, lalu error |
| Database corrupt | error "Database error" |

## File Hilang

### POSITIVE CASE

- File ada di storage -> download berhasil

### NEGATIVE CASE

| Kondisi | Behavior |
|---------|----------|
| File hilang dari storage tapi ada di database | admin mendapat warning |
| Customer coba download file yang hilang | error "File tidak tersedia" |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| File hilang saat admin download | error, log |
| File hilang saat customer melihat detail | tampilkan warning, download disabled |
| File hilang karena storage reset | semua order affected, admin notifikasi |

## Unauthorized

### POSITIVE CASE

- User login -> bisa akses protected page

### NEGATIVE CASE

| Kondisi | Behavior |
|---------|----------|
| User tidak login | 401 Unauthorized, redirect ke login |
| User login tapi role tidak sesuai | 403 Forbidden |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| Session expired | redirect ke login dengan message |
| Cookie dihapus | redirect ke login |
| Token invalid | redirect ke login |

## Validation Error

### POSITIVE CASE

- Semua input valid -> proses berhasil

### NEGATIVE CASE

| Kondisi | Behavior |
|---------|----------|
| Input tidak valid | 422 Unprocessable Entity |
| Error ditampilkan di form field yang sesuai | inline error messages |
| Old input dipertahankan (kecuali password) | form tetap terisi |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| AJAX request | return JSON error |
| Multiple validation errors | semua error ditampilkan |
| Validation error pada file | error spesifik (size, type, dll) |

## Server Error

### POSITIVE CASE

- Semua berjalan lancar -> 200 OK

### NEGATIVE CASE

| Kondisi | Behavior |
|---------|----------|
| Exception terjadi | 500 Internal Server Error |
| Log seluruh exception | ke `storage/logs/laravel.log` |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| Exception handler menampilkan error page | informatif tapi tidak detail |
| Development mode | tampilkan detail error |
| Production mode | tampilkan error page generik |
| Log error ke file | rotation harian |
| Error pada queue job | ditambahkan ke failed_jobs |

---

# EDGE CASES (COMPREHENSIVE)

## 1. Upload Edge Cases

| # | Kondisi | Behavior | Handler |
|---|---------|----------|---------|
| 1 | File kosong (0 bytes) | Reject | FileService::validate() |
| 2 | File > 100MB | Reject | FormRequest max:102400 |
| 3 | Extension palsu (rename .exe ke .pdf) | Reject berdasarkan MIME | FormRequest custom validation |
| 4 | Refresh saat upload berjalan | Idempotency token cegah duplicate | JavaScript + CSRF token |
| 5 | Double click tombol upload | Disable button setelah klik pertama | JavaScript |
| 6 | Upload dari mobile koneksi lambat | Timeout lebih lama, loading indicator | JavaScript + timeout config |
| 7 | Nama file sangat panjang (> 255) | Gunakan stored filename | FileService |
| 8 | Nama file karakter spesial | Simpan original filename | Eloquent |
| 9 | Upload file yang sama 2 kali | 2 order berbeda | OrderService |
| 10 | File tepat 100MB | Diterima | FormRequest |
| 11 | File 100.1MB | Ditolak | FormRequest |
| 12 | File 1 byte | Ditolak | FormRequest size check |
| 13 | Network putus saat upload | Tidak ada file/database record | Client-side abort |
| 14 | JavaScript disabled | Form tetap berfungsi | Server-side validation |
| 15 | File corrupt | Tetap disimpan | Admin cek manual |
| 16 | Upload dari mobile | Responsive form | Blade responsive |
| 17 | Upload dengan notes kosong | Diperbolehkan | Nullable column |
| 18 | Upload dengan notes spasi | Disimpan sebagai spasi | Eloquent |
| 19 | Upload file dengan emoji filename | Disimpan dengan benar | UTF-8 encoding |
| 20 | Upload dengan drag-and-drop | Form input tetap berfungsi | Fallback |

## 2. Authentication Edge Cases

| # | Kondisi | Behavior | Handler |
|---|---------|----------|---------|
| 21 | Email case insensitive | Treat sama | Email::lower() di FormRequest |
| 22 | Password hanya spasi | Ditolak | Custom validation rule |
| 23 | Concurrent registration email sama | Hanya 1 berhasil | Database unique constraint |
| 24 | User inject role admin | Diabaikan | $fillable hanya ['name', 'email', 'password'] |
| 25 | Session expired | Redirect ke login | Session middleware |
| 26 | Cookies dihapus | Harus login ulang | Session invalid |
| 27 | Login dari multiple device | Semua session valid | Session driver database |
| 28 | Brute force password | Rate limit aktif | RateLimiter facade |
| 29 | Password unicode/emoji | Diterima | Bcrypt handle |
| 30 | Login dengan Remember Me | Session lifetime lebih lama | RememberToken |
| 31 | Login tanpa Remember Me | Session lifetime 120 menit | Session config |
| 32 | Admin login di halaman customer | Error + redirect | EnsureIsCustomer middleware |

## 3. Order Status Edge Cases

| # | Kondisi | Behavior | Handler |
|---|---------|----------|---------|
| 33 | Update status 2 tab bersamaan | Optimistic locking | Cek updated_at sebelum update |
| 34 | Klik status berkali-kali cepat | Disable button | JavaScript |
| 35 | Status diubah saat customer melihat | Customer lihat status terbaru | Database query |
| 36 | Order Processing > 7 hari | Warning di admin dashboard | Query + conditional |
| 37 | Concurrent update 2 admin | Yang pertama berhasil | Optimistic locking |
| 38 | Cancel tanpa alasan | Error | FormRequest required |
| 39 | Alasan < 10 karakter | Error | FormRequest min:10 |
| 40 | Complete tanpa processing | Error | State validation |

## 4. Notification Edge Cases

| # | Kondisi | Behavior | Handler |
|---|---------|----------|---------|
| 41 | 1000+ notifikasi | Pagination | paginate() |
| 42 | Queue worker down | Tetap tersimpan | Job retry |
| 43 | Login 2 device | Notifikasi sinkron | Database source |
| 44 | Notifikasi order dihapus | Tetap ditampilkan | Foreign key nullable |
| 45 | Mark all sebagai read | Bulk update | DB::update() |
| 46 | Notifikasi via queue | Delay beberapa detik | ShouldQueue |
| 47 | Notifikasi sangat panjang | Di-truncate di UI | Blade @limit |

## 5. File Edge Cases

| # | Kondisi | Behavior | Handler |
|---|---------|----------|---------|
| 48 | File hilang dari storage | Admin warning | Cek exists() |
| 49 | File corrupt | Admin warning | Download error |
| 50 | Storage penuh | Upload gagal, rollback | DB::transaction |
| 51 | Extension uppercase | Diterima | strtolower() |
| 52 | File tanpa extension | Ditolak | Extension check |
| 53 | Double extension | Ditolak | Extension check |
| 54 | Signed URL expired | Error | URL::temporarySignedRoute() |
| 55 | File dihapus manual | Admin notifikasi | Health check |
| 56 | File dengan spasi nama | Diterima | Storage put() |
| 57 | File dengan unicode | Diterima | UTF-8 |

## 6. Search/Filter Edge Cases

| # | Kondisi | Behavior | Handler |
|---|---------|----------|---------|
| 58 | SQL injection | Ditolak | Parameterized query |
| 59 | XSS | Disanitasi | Blade escaping |
| 60 | Search kosong | Tampilkan semua | Empty string check |
| 61 | Filter invalid | Tampilkan semua | Validate filter value |
| 62 | Sort invalid | Default | Fallback to default |
| 63 | Kombinasi search+filter+sort | Ketiganya diterapkan | Query builder |
| 64 | URL bookmarkable | Filter tersimpan di URL | Query parameter |
| 65 | Search unicode | Berhasil | LIKE dengan binding |
| 66 | Search sangat panjang | Di-truncate | substr() |

## 7. Database Edge Cases

| # | Kondisi | Behavior | Handler |
|---|---------|----------|---------|
| 67 | Database disconnect | Reconnect | Laravel connection |
| 68 | Database timeout | Retry, error | DB::statement() |
| 69 | Database deadlock | Retry, error | DB::transaction() |
| 70 | Concurrent insert email sama | Unique constraint | Database level |

## 8. UI Edge Cases

| # | Kondisi | Behavior | Handler |
|---|---------|----------|---------|
| 71 | Browser back button | Halaman sebelumnya | Browser cache |
| 72 | Browser refresh | Data terbaru | Server-side render |
| 73 | Multiple tabs | Konsisten data | Database source |
| 74 | JavaScript disabled | Form tetap berfungsi | Server-side |
| 75 | Browser tidak support modern JS | Fallback | Progressive enhancement |
| 76 | Screen reader | Accessible | ARIA labels |
| 77 | Keyboard navigation | Navigasi dengan tab | tabindex + focus |
| 78 | Mobile landscape | Responsive | Media queries |
| 79 | Tablet portrait | Responsive | Media queries |
| 80 | Dark mode | Consistent | TailwindCSS dark: |

## 9. Concurrent Access Edge Cases

| # | Kondisi | Behavior | Handler |
|---|---------|----------|---------|
| 81 | 2 customer upload bersamaan | 2 order berbeda | Database transaction |
| 82 | 2 admin update status bersamaan | Optimistic locking | updated_at check |
| 83 | Admin update, customer view | Customer lihat data terbaru | Database query |
| 84 | Customer upload, admin view | Admin lihat order baru | Database query |
| 85 | 2 customer register email sama | Hanya 1 berhasil | Unique constraint |
| 86 | Admin complete, customer refresh | Status terbaru | Database query |

---

# BLADE VIEWS & UI

## Technology Stack

- TailwindCSS v4
- Laravel Blade
- Vite 7
- Axios (untuk AJAX)
- Alpine.js (untuk interaktivitas ringan)

## Layout Hierarchy

```
layouts/
├── app.blade.php          (customer layout)
│   ├── Navbar
│   │   ├── Logo
│   │   ├── Navigation Links (Dashboard, Riwayat, Notifikasi)
│   │   ├── Notification Badge
│   │   └── User Dropdown (profile, logout)
│   ├── Main Content (@yield('content'))
│   └── Footer
│
├── admin.blade.php        (admin layout)
│   ├── Sidebar
│   │   ├── Logo
│   │   ├── Navigation Links (Dashboard, Pesanan)
│   │   └── Collapse Button (mobile)
│   ├── Navbar
│   │   ├── Hamburger Menu (mobile)
│   │   ├── Search (optional)
│   │   └── Admin Dropdown (logout)
│   └── Main Content (@yield('content'))
│
└── guest.blade.php        (login/register layout)
    ├── Logo
    ├── Main Content (@yield('content'))
    └── Footer
```

## Component Specifications

### alert.blade.php

Props: `type` (success, error, warning, info), `message`, `dismissible`

### badge.blade.php

Props: `status` (pending, processing, completed, cancelled), `size` (sm, md)

### button.blade.php

Props: `variant` (primary, secondary, danger, success), `size` (sm, md, lg), `loading`, `disabled`

### card.blade.php

Props: `title`, `subtitle`

### loading.blade.php

Props: `type` (spinner, skeleton, progress), `size` (sm, md, lg)

### modal.blade.php

Props: `id`, `title`, `size` (sm, md, lg)

Content: `@slot('body')`, `@slot('footer')`

### pagination.blade.php

Props: `paginator` (LengthAwarePaginator)

### search-input.blade.php

Props: `value`, `placeholder`, `debounce` (ms)

### status-badge.blade.php

Props: `status` (OrderStatus enum)

Color mapping:
- pending: yellow
- processing: blue
- completed: green
- cancelled: red

### toast.blade.php

Props: `type` (success, error, warning, info), `message`, `duration` (ms)

### empty-state.blade.php

Props: `title`, `message`, `icon`, `actionLabel`, `actionUrl`

## Responsive Design

### Desktop (> 1024px)

- Full width layout
- Sidebar admin selalu terlihat (width: 256px)
- Dashboard widget 4 kolom
- Tabel dengan kolom lengkap
- Hover effects

### Tablet (768px - 1024px)

- Layout adaptif
- Sidebar admin bisa di-collapse (width: 64px -> icon only)
- Dashboard widget 2 kolom
- Tabel dengan kolom yang dikurangi
- Touch-friendly buttons

### Mobile (< 768px)

- Single column layout
- Sidebar admin hidden, gunakan hamburger menu
- Dashboard widget stack vertikal
- Tabel menjadi card layout
- Bottom navigation (optional)
- Swipe gestures (optional)

## Upload Component

```html
<!-- Drag and drop zone -->
<div class="upload-zone"
     x-data="uploadHandler()"
     @dragover.prevent="isDragging = true"
     @dragleave="isDragging = false"
     @drop.prevent="handleDrop($event)">

    <!-- Drop zone -->
    <div class="border-2 border-dashed rounded-lg p-8 text-center"
         :class="isDragging ? 'border-blue-500 bg-blue-50' : 'border-gray-300'">

        <input type="file"
               id="file"
               name="file"
               accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png"
               @change="handleFileSelect($event)"
               class="hidden">

        <label for="file" class="cursor-pointer">
            <icon-upload class="w-12 h-12 mx-auto text-gray-400" />
            <p class="mt-2 text-sm text-gray-600">
                Drag & drop file di sini, atau <span class="text-blue-500">klik untuk memilih</span>
            </p>
            <p class="mt-1 text-xs text-gray-500">
                PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, JPG, PNG (Maks. 100MB)
            </p>
        </label>
    </div>

    <!-- File preview -->
    <div x-show="selectedFile" class="mt-4">
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <div class="flex items-center space-x-3">
                <icon-file class="w-8 h-8 text-gray-500" />
                <div>
                    <p class="text-sm font-medium" x-text="selectedFile?.name"></p>
                    <p class="text-xs text-gray-500" x-text="formatSize(selectedFile?.size)"></p>
                </div>
            </div>
            <button @click="clearFile()" class="text-red-500 hover:text-red-700">
                <icon-x class="w-5 h-5" />
            </button>
        </div>
    </div>

    <!-- Progress bar -->
    <div x-show="isUploading" class="mt-4">
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="bg-blue-500 h-2 rounded-full transition-all duration-300"
                 :style="`width: ${progress}%`"></div>
        </div>
        <p class="text-sm text-gray-600 mt-1" x-text="`Mengupload... ${progress}%`"></p>
    </div>
</div>
```

## Confirmation Dialog

```html
<!-- Modal konfirmasi -->
<x-modal id="confirm-modal" title="Konfirmasi">
    <x-slot:body>
        <p id="confirm-message"></p>
    </x-slot:body>

    <x-slot:footer>
        <x-button variant="secondary" @click="closeModal()">Batal</x-button>
        <x-button variant="primary" @click="confirmAction()">Ya, Lanjutkan</x-button>
    </x-slot:footer>
</x-modal>
```

## Toast Notification

```html
<!-- Toast container -->
<div id="toast-container"
     class="fixed top-4 right-4 z-50 space-y-2">

    <!-- Toast template -->
    <div class="toast bg-white shadow-lg rounded-lg p-4 flex items-center space-x-3"
         x-data="{ show: false }"
         x-show="show"
         x-transition>

        <icon-check class="w-5 h-5 text-green-500" />
        <p class="text-sm" id="toast-message"></p>

        <button @click="show = false" class="text-gray-400 hover:text-gray-600">
            <icon-x class="w-4 h-4" />
        </button>
    </div>
</div>
```

---

# ROUTES

## Customer Routes

```php
// routes/web.php

// Guest routes (belum login)
Route::middleware('guest')->group(function () {
    Route::get('/', fn() => view('welcome'))->name('home');
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

// Customer routes (harus login sebagai customer)
Route::middleware(['auth', 'customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [CustomerOrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [CustomerOrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])->name('orders.show');
    Route::get('/notifications', [CustomerNotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [CustomerNotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/read-all', [CustomerNotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
```

## Admin Routes

```php
// routes/web.php

// Admin guest routes (belum login sebagai admin)
Route::middleware('guest:web')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'show'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'store']);
});

// Admin routes (harus login sebagai admin)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/processing', [AdminOrderController::class, 'processing'])->name('orders.processing');
    Route::post('/orders/{order}/complete', [AdminOrderController::class, 'complete'])->name('orders.complete');
    Route::post('/orders/{order}/cancel', [AdminOrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('/orders/{order}/download', [AdminOrderController::class, 'download'])->name('orders.download');
    Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('logout');
});
```

## Route Names

| Route | Method | Name | Middleware |
|-------|--------|------|------------|
| `/` | GET | `home` | guest |
| `/register` | GET | `register` | guest |
| `/register` | POST | - | guest |
| `/login` | GET | `login` | guest |
| `/login` | POST | - | guest |
| `/customer/dashboard` | GET | `customer.dashboard` | auth, customer |
| `/customer/orders` | GET | `customer.orders.index` | auth, customer |
| `/customer/orders/create` | GET | `customer.orders.create` | auth, customer |
| `/customer/orders` | POST | `customer.orders.store` | auth, customer |
| `/customer/orders/{order}` | GET | `customer.orders.show` | auth, customer |
| `/customer/notifications` | GET | `customer.notifications.index` | auth, customer |
| `/admin/login` | GET | `admin.login` | guest |
| `/admin/login` | POST | - | guest |
| `/admin/dashboard` | GET | `admin.dashboard` | auth, admin |
| `/admin/orders` | GET | `admin.orders.index` | auth, admin |
| `/admin/orders/{order}` | GET | `admin.orders.show` | auth, admin |
| `/admin/orders/{order}/processing` | POST | `admin.orders.processing` | auth, admin |
| `/admin/orders/{order}/complete` | POST | `admin.orders.complete` | auth, admin |
| `/admin/orders/{order}/cancel` | POST | `admin.orders.cancel` | auth, admin |
| `/admin/orders/{order}/download` | GET | `admin.orders.download` | auth, admin |

## Route Model Binding

Gunakan Route Model Binding untuk `{order}`.

### POSITIVE CASE

- Order ada -> controller menerima model instance
- Order tidak ada -> 404 otomatis

### NEGATIVE CASE

| Kondisi | Behavior |
|---------|----------|
| Order ID tidak valid | 404 |
| Order ID bukan angka | 404 |

### EDGE CASE

| Kondisi | Behavior |
|---------|----------|
| Order ID sangat besar | 404 |
| Order ID negatif | 404 |
| Order ID nol | 404 |
| Order dengan status tertentu | tetap bisa diakses |

---

# TESTING

## Test Structure

```
tests/
├── Feature/
│   ├── Auth/
│   │   ├── RegisterTest.php          (8 tests)
│   │   ├── LoginTest.php             (10 tests)
│   │   └── LogoutTest.php            (4 tests)
│   ├── Customer/
│   │   ├── DashboardTest.php         (6 tests)
│   │   ├── OrderUploadTest.php       (12 tests)
│   │   ├── OrderHistoryTest.php      (5 tests)
│   │   ├── OrderDetailTest.php       (5 tests)
│   │   └── NotificationTest.php      (7 tests)
│   ├── Admin/
│   │   ├── DashboardTest.php         (5 tests)
│   │   ├── OrderListTest.php         (8 tests)
│   │   ├── OrderDetailTest.php       (5 tests)
│   │   ├── OrderProcessingTest.php   (6 tests)
│   │   ├── OrderCompleteTest.php     (7 tests)
│   │   ├── OrderCancelTest.php       (8 tests)
│   │   └── DownloadTest.php          (4 tests)
│   ├── Authorization/
│   │   ├── CustomerAccessTest.php    (8 tests)
│   │   └── AdminAccessTest.php       (8 tests)
│   ├── EdgeCase/
│   │   ├── UploadEdgeCaseTest.php    (10 tests)
│   │   ├── ConcurrentAccessTest.php  (5 tests)
│   │   └── FileIntegrityTest.php     (5 tests)
│   └── Notification/
│       ├── NotificationCreatedTest.php (6 tests)
│       └── NotificationReadTest.php    (5 tests)
└── Unit/
    ├── Services/
    │   ├── OrderServiceTest.php      (15 tests)
    │   ├── NotificationServiceTest.php (8 tests)
    │   └── FileServiceTest.php       (10 tests)
    └── Enums/
        └── OrderStatusTest.php       (8 tests)
```

## Minimal Feature Tests

### Authentication

| # | Test | Input | Expected |
|---|------|-------|----------|
| 1 | Register dengan data valid | name, email, password, confirm | 302 redirect ke login |
| 2 | Register dengan email sudah ada | email existing | 422 error |
| 3 | Register dengan password kurang dari 8 | password < 8 | 422 error |
| 4 | Register dengan confirm tidak cocok | password != confirm | 422 error |
| 5 | Login dengan credentials valid | email, password benar | 302 redirect ke dashboard |
| 6 | Login dengan email salah | email tidak ada | 422 error |
| 7 | Login dengan password salah | password salah | 422 error |
| 8 | Logout | click logout | 302 redirect ke login, session hapus |
| 9 | Akses protected page tanpa login | - | 302 redirect ke login |
| 10 | Akses halaman login saat sudah login | - | 302 redirect ke dashboard |

### Customer

| # | Test | Input | Expected |
|---|------|-------|----------|
| 11 | Customer upload file valid | file valid | 302 redirect ke detail, order dibuat |
| 12 | Customer upload file invalid | file .exe | 422 error |
| 13 | Customer upload file > 100MB | file besar | 422 error |
| 14 | Customer melihat riwayat pesanan sendiri | - | 200, data sendiri saja |
| 15 | Customer mencoba melihat pesanan orang lain | order_id lain | 403 |
| 16 | Customer melihat detail pesanan sendiri | order sendiri | 200 |
| 17 | Customer melihat notifikasi | - | 200 |
| 18 | Customer menandai notifikasi sebagai dibaca | notification_id | 200, is_read = true |
| 19 | Customer menandai semua notifikasi sebagai dibaca | - | 200, semua is_read = true |
| 20 | Customer akses dashboard | - | 200, stats ditampilkan |

### Admin

| # | Test | Input | Expected |
|---|------|-------|----------|
| 21 | Admin login | credentials admin | 302 redirect ke admin dashboard |
| 22 | Admin melihat dashboard | - | 200, stats ditampilkan |
| 23 | Admin melihat daftar pesanan | - | 200, semua pesanan |
| 24 | Admin search pesanan | keyword | 200, hasil filter |
| 25 | Admin filter pesanan by status | status=completed | 200, hanya completed |
| 26 | Admin sort pesanan | sort=date | 200, terurut |
| 27 | Admin melihat detail pesanan | order_id | 200 |
| 28 | Admin mengubah status ke Processing | POST /processing | 302, status berubah |
| 29 | Admin mengubah status ke Completed | POST /complete | 302, status berubah, completed_at terisi |
| 30 | Admin cancel order | POST /cancel + reason | 302, status berubah |
| 31 | Admin download file | GET /download | 200, file downloaded |
| 32 | Admin mencoba akses customer dashboard | - | 403 |

### Authorization

| # | Test | Input | Expected |
|---|------|-------|----------|
| 33 | Customer mencoba akses admin routes | GET /admin/* | 403 |
| 34 | Admin mencoba akses customer routes | GET /customer/* | 403 |
| 35 | Guest mencoba akses customer routes | GET /customer/* | 302 redirect ke login |
| 36 | Guest mencoba akses admin routes | GET /admin/* | 302 redirect ke login |

### Order Status

| # | Test | Transisi | Expected |
|---|------|----------|----------|
| 37 | Pending -> Processing | POST /processing | 200, status = processing |
| 38 | Processing -> Completed | POST /complete | 200, status = completed |
| 39 | Pending -> Cancelled | POST /cancel | 200, status = cancelled |
| 40 | Completed -> Pending | POST /processing | 422 error |
| 41 | Cancelled -> Processing | POST /processing | 422 error |
| 42 | Processing -> Cancelled | POST /cancel | 422 error |
| 43 | Pending -> Completed (skip) | POST /complete | 422 error |
| 44 | Cancel tanpa alasan | POST /cancel tanpa reason | 422 error |

### Notification

| # | Test | Expected |
|---|------|----------|
| 45 | Notifikasi dibuat saat order diterima | notification exists |
| 46 | Notifikasi dibuat saat order diproses | notification exists |
| 47 | Notifikasi dibuat saat order selesai | notification exists |
| 48 | Notifikasi dibatalkan | notification exists |
| 49 | Notifikasi ditandai sebagai dibaca | is_read = true |
| 50 | Semua notifikasi ditandai sebagai dibaca | semua is_read = true |
| 51 | Unread count akurat | count == jumlah belum dibaca |

### Search & Filter

| # | Test | Input | Expected |
|---|------|-------|----------|
| 52 | Search by order number | keyword | 200, hasil filter |
| 53 | Search by customer name | keyword | 200, hasil filter |
| 54 | Search by email | keyword | 200, hasil filter |
| 55 | Search by filename | keyword | 200, hasil filter |
| 56 | Filter by status | status | 200, hasil filter |
| 57 | Sort by date | sort=date | 200, terurut |
| 58 | Sort by status | sort=status | 200, terurut |

### Pagination

| # | Test | Expected |
|---|------|----------|
| 59 | Pagination halaman 1 | 15 data |
| 60 | Pagination halaman 2 | 15 data berikutnya |
| 61 | Pagination halaman terakhir | sisa data |

### Edge Cases

| # | Test | Expected |
|---|------|----------|
| 62 | Upload file kosong | 422 error |
| 63 | Upload file > 100MB | 422 error |
| 64 | Upload extension palsu | 422 error |
| 65 | Double click upload | tidak duplicate |
| 66 | Admin complete 2 kali | tidak duplicate |
| 67 | Concurrent status update | optimistic locking |
| 68 | Session expired | redirect login |
| 69 | CSRF token invalid | 419 |
| 70 | Rate limit login | 429 |
| 71 | File tidak ditemukan | error handling |
| 72 | Customer akses order lain | 403 |
| 73 | Admin akses non-existent order | 404 |

---

# CODE QUALITY

## Standards

- PHPStan Ready (level 5 minimum)
- Laravel Pint untuk formatting (auto-format saat save)
- PSR-12 coding style
- Type Hint di semua parameter
- Return Type di semua method
- Strict Validation di semua input

## Rules

- Tidak ada duplicated code (DRY principle)
- Tidak ada hardcoded magic string (extract ke constants/enums)
- Semua business logic di Service class
- Semua validasi di Form Request
- Semua authorization di Policy
- Semua konfigurasi di config/ atau .env
- Semua error message dalam Bahasa Indonesia
- Semua date format konsisten (Y-m-d H:i:s)
- Semua response format konsisten

## Constants

```php
// app/Constants/OrderConstant.php
class OrderConstant
{
    public const MAX_FILE_SIZE = 102400; // 100MB in KB
    public const MAX_NOTES_LENGTH = 1000;
    public const MIN_CANCEL_REASON_LENGTH = 10;
    public const ORDER_PER_PAGE = 15;
    public const NOTIFICATION_PER_PAGE = 15;
    public const RECENT_ORDERS_LIMIT = 5;
    public const SIGNED_URL_EXPIRATION = 60; // minutes
    public const RATE_LIMIT_MAX_ATTEMPTS = 5;
    public const RATE_LIMIT_DECAY_MINUTES = 1;
}
```

## Enums

```php
// app/Enums/OrderStatus.php
enum OrderStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Diproses',
            self::COMPLETED => 'Selesai',
            self::CANCELLED => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'yellow',
            self::PROCESSING => 'blue',
            self::COMPLETED => 'green',
            self::CANCELLED => 'red',
        };
    }
}
```

---

# PERFORMANCE & OPTIMIZATION

## Database Optimization

1. **Indexes:** Tambah index pada kolom yang sering di-query:
   - `orders.user_id` (untuk customer orders)
   - `orders.status` (untuk filter)
   - `orders.created_at` (untuk sorting)
   - `orders.user_id, orders.status` (composite, untuk dashboard stats)
   - `notifications.user_id, notifications.is_read` (composite, untuk badge count)

2. **Eager Loading:** Gunakan `with()` untuk menghindari N+1 query:
   ```php
   Order::with('user')->paginate(15);
   ```

3. **Select Specific Columns:** Jangan gunakan `SELECT *`:
   ```php
   Order::select('id', 'order_number', 'status', 'created_at')->get();
   ```

4. **Chunking:** Untuk data besar:
   ```php
   Order::chunk(100, function ($orders) { ... });
   ```

5. **Count Queries:** Gunakan `count()` daripada `get()` + `count()`:
   ```php
   Order::where('status', 'pending')->count();
   ```

## Caching Strategy

1. **Dashboard Stats:** Cache selama 5 menit:
   ```php
   Cache::remember('admin_dashboard_stats', 300, function () {
       return $this->getDashboardStats();
   });
   ```

2. **Unread Notification Count:** Cache selama 1 menit:
   ```php
   Cache::remember("user_{$userId}_unread_count", 60, function () use ($userId) {
       return Notification::where('user_id', $userId)->where('is_read', false)->count();
   });
   ```

## Queue Optimization

1. **Queue Driver:** Gunakan `database` untuk production
2. **Retry:** 3 kali retry dengan backoff 60 detik
3. **Failed Jobs:** Simpan ke `failed_jobs` table
4. **Job Batching:** Untuk bulk operations

## Frontend Optimization

1. **Vite Build:** Optimized production build
2. **Image Optimization:** Gunakan format WebP jika memungkinkan
3. **Lazy Loading:** Gunakan lazy loading untuk gambar
4. **Debounce:** Search input dengan debounce 300ms
5. **Pagination:** Server-side pagination, bukan infinite scroll

---

# LOGGING & MONITORING

## Log Channels

```php
// config/logging.php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'daily'],
    ],
    'single' => [
        'driver' => 'single',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
    ],
    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
        'days' => 30,
    ],
],
```

## What to Log

1. **Application Logs:**
   - Order created
   - Order status changed
   - Notification sent
   - File uploaded
   - File downloaded

2. **Error Logs:**
   - All exceptions
   - Failed jobs
   - Database errors
   - File system errors

3. **Security Logs:**
   - Failed login attempts
   - Unauthorized access attempts
   - Rate limit exceeded

## Log Format

```
[2026-07-04 10:30:00] production.INFO: Order created {"order_id":1,"user_id":5,"order_number":"ORD-20260704-0001"}
```

## Monitoring

1. **Health Check:** Endpoint `/up` untuk monitoring
2. **Queue Monitoring:** Monitor queue size dan failed jobs
3. **Database Monitoring:** Monitor query performance
4. **Error Tracking:** Sentry atau Bugsnag (optional)

---

# DEPLOYMENT CHECKLIST

## Pre-Deployment

- [ ] Environment variables configured (.env)
- [ ] Database migrated
- [ ] Storage linked (`php artisan storage:link`)
- [ ] Admin seeder run
- [ ] Queue worker running
- [ ] Cache cleared (`php artisan cache:clear`)
- [ ] Config cached (`php artisan config:cache`)
- [ ] Route cached (`php artisan route:cache`)
- [ ] View cached (`php artisan view:cache`)
- [ ] Assets built (`npm run build`)
- [ ] Tests passing

## Post-Deployment

- [ ] Verify all routes accessible
- [ ] Test login (customer + admin)
- [ ] Test file upload
- [ ] Test file download
- [ ] Test order status flow
- [ ] Test notifications
- [ ] Test search/filter/sort
- [ ] Test responsive design
- [ ] Monitor error logs
- [ ] Monitor queue worker

## Security Checklist

- [ ] APP_DEBUG=false in production
- [ ] APP_KEY set and unique
- [ ] HTTPS enabled
- [ ] CSRF protection enabled
- [ ] Rate limiting configured
- [ ] File storage private
- [ ] Password hashing (bcrypt)
- [ ] SQL injection prevention (parameterized queries)
- [ ] XSS prevention (Blade escaping)
- [ ] Authorization policies active

---

# FUTURE READY

Struktur project harus mudah ditambahkan:

| Feature | Implementation Notes |
|---------|---------------------|
| WhatsApp Notification | Tambah service WhatsAppService, queue job |
| Email Notification | Tambah Mail class, queue job |
| Payment Gateway | Tambah Payment model, service, midtrans/xendit integration |
| Multiple Branch | Tambah branches table, branch_id di orders |
| Multiple Admin | Role sudah support, tinggal tambah admin |
| Print Pricing | Tambah pricing table, hitung total di order |
| Invoice | Tambah Invoice model, PDF generation |
| Online Payment | Integrasi payment gateway |
| Order Tracking | Real-time tracking via WebSocket/broadcast |
| Cloud Storage | Ganti disk ke S3/GCS |
| Queue Worker | Supervisor untuk queue worker |
| API | Tambah API routes untuk mobile app |
| Multi-language | Gunakan Laravel localization |
| Dark Mode | TailwindCSS dark mode toggle |
| Export | Excel/PDF export untuk order list |
| Dashboard Charts | Chart.js atau ApexCharts |

---

# FINAL REQUIREMENT

Project harus:

- [ ] Production Ready
- [ ] Clean Code
- [ ] Responsive (Desktop, Tablet, Mobile)
- [ ] Aman (Security best practices)
- [ ] Mudah dikembangkan (Clean Architecture)
- [ ] Mengikuti Laravel Best Practice
- [ ] Tidak mengandung hardcoded logic
- [ ] Tidak mengandung business logic pada Controller
- [ ] Semua validasi harus eksplisit (Form Request)
- [ ] Seluruh positive case, negative case, dan edge case harus ditangani secara jelas
- [ ] Semua fitur wajib memiliki authorization, validation, dan error handling yang konsisten
- [ ] Semua tests passing
- [ ] Tidak ada warning/error di PHPStan level 5
- [ ] Code style mengikuti PSR-12
- [ ] Database migration bisa di-rollback
- [ ] Semua file upload tersimpan di private storage
- [ ] Semua download menggunakan signed URL
- [ ] Semua notifikasi via queue
- [ ] Semua error page custom dan informatif
- [ ] Documentation lengkap dan terupdate
