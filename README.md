<h1 align="center">
  ☕ Apipi Coffee — Sistem Kasir (POS)
</h1>

<p align="center">
  Aplikasi Point of Sale berbasis web untuk manajemen penjualan kafe, dibangun dengan Laravel 10 + Filament Admin.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-10.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 10" />
  <img src="https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.1+" />
  <img src="https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL" />
  <img src="https://img.shields.io/badge/Filament-3.0-FDAE4B?style=for-the-badge" alt="Filament 3" />
  <img src="https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker" />
  <img src="https://img.shields.io/badge/Railway-Deploy-0B0D0E?style=for-the-badge&logo=railway&logoColor=white" alt="Railway" />
</p>

---

## 📖 Tentang Proyek

**Apipi Coffee POS** adalah sistem kasir (Point of Sale) berbasis web yang dirancang khusus untuk kebutuhan operasional kafe. Aplikasi ini memungkinkan staf kasir memproses transaksi dengan mudah, sementara manajer  dapat memantau laporan penjualan, mengelola menu, dan mengatur tim karyawan semua dalam satu platform.

Proyek ini dikembangkan sebagai **Projek RPL (Rekayasa Perangkat Lunak)** menggunakan framework Laravel 10, dengan deployment ke cloud via Railway dan Google Cloud Run.

---

## ✨ Fitur Utama

### 🛒 Point of Sale (Kasir)

- Tampilan menu produk dengan kategori & filter real-time
- Keranjang belanja interaktif (tambah, kurangi, hapus item)
- Pilihan metode pembayaran (tunai, QRIS, dll.)
- Cetak struk / receipt otomatis setelah transaksi
- Nomor order otomatis dengan format `APC-XXXXX`

### 📦 Manajemen Menu (Produk)

- Tambah, edit, dan hapus produk (soft delete)
- Upload gambar produk ke **Cloudflare R2** (object storage)
- Atur harga, stok, kategori, dan status ketersediaan
- SKU otomatis untuk setiap produk

### 📊 Laporan Penjualan

- Laporan harian, mingguan, bulanan, dan tahunan
- Grafik interaktif (chart) pendapatan & jumlah transaksi
- Filter laporan berdasarkan rentang tanggal kustom
- Export laporan ke format **Excel (.xlsx)** dan **PDF**

### 👥 Manajemen Karyawan

- CRUD data karyawan (tambah, edit, hapus)
- Penetapan role per karyawan
- ID karyawan otomatis dengan format `EMP-XXX`
- Status karyawan: `active`, `inactive`, `leave`

### ⚙️ Pengaturan Sistem

- Identitas bisnis (nama kafe, logo) — khusus Manager
- Ganti password & update profil (semua role)
- Pilihan tema warna & dark mode per pengguna
- Forgot password dengan reset langsung

---

## 🔐 Role & Hak Akses

| Role           | POS | Menu | Laporan | Karyawan | Identitas Bisnis |
|----------------|:---:|:----:|:-------:|:--------:|:----------------:|
| **Cashier**    | ✅  | ❌   | ❌      | ❌       | ❌               |
| **Admin**      | ❌  | ✅   | ✅      | ❌       | ❌               |
| **Manager**    | ✅  | ✅   | ✅      | ✅       | ✅               |

---

## 🗄️ Struktur Database

| Tabel          | Keterangan                                      |
|----------------|-------------------------------------------------|
| `users`        | Data karyawan (role, status, avatar, shift)     |
| `products`     | Data menu/produk (SKU, harga, stok, gambar)     |
| `categories`   | Kategori produk (nama, slug)                    |
| `orders`       | Data transaksi (nomor order, total, kasir)      |
| `order_items`  | Detail item per transaksi                       |

---

## 🏗️ Arsitektur Proyek

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/            # Endpoint data terpadu (UnifiedApiController)
│   │   ├── Auth/           # Login & autentikasi
│   │   ├── OrderController.php
│   │   ├── ProductController.php
│   │   ├── ReportController.php
│   │   ├── SettingsController.php
│   │   └── PosController.php
│   └── Middleware/
├── Models/                 # Eloquent models (User, Product, Order, dll.)
├── Services/               # Business logic (ReportService, OrderService)
├── Repositories/           # Data access layer
├── DTO/                    # Data Transfer Objects
├── Exports/                # Excel export (Maatwebsite)
├── Filament/               # Admin panel (Filament 3)
└── Support/                # Helper (BusinessSettings, StorageUrl)
```

---

## 🛠️ Tech Stack

| Teknologi                   | Keterangan                              |
|-----------------------------|-----------------------------------------|
| **Laravel 10**              | Framework PHP utama                     |
| **PHP 8.1+**                | Bahasa pemrograman                      |
| **MySQL**                   | Database relasional                     |
| **Filament 3**              | Admin panel UI                          |
| **Laravel Sanctum**         | Autentikasi API                         |
| **Maatwebsite Excel**       | Export laporan ke Excel                 |
| **barryvdh/laravel-dompdf** | Export laporan ke PDF                   |
| **Cloudflare R2**           | Object storage untuk gambar             |
| **Vite + TailwindCSS**      | Build tool & CSS framework              |
| **Docker**                  | Containerisasi aplikasi                 |
| **Railway**                 | Platform deployment cloud               |
| **Google Cloud Run**        | Alternatif deployment (via cloudbuild)  |

---

## 🚀 Instalasi Lokal (XAMPP)

### Prasyarat

- PHP 8.1+
- Composer
- MySQL (XAMPP)
- Node.js & npm

### Langkah Instalasi

1. **Clone repository**

   ```bash
   git clone <url-repository> APIPICOFFE-main
   cd APIPICOFFE-main
   ```

2. **Install dependensi PHP**

   ```bash
   composer install
   ```

3. **Install dependensi Node.js**

   ```bash
   npm install
   ```

4. **Salin file environment**

   ```bash
   cp .env.example .env
   ```

5. **Generate application key**

   ```bash
   php artisan key:generate
   ```

6. **Konfigurasi `.env`** — sesuaikan variabel berikut:

   ```env
   APP_NAME="Apipi Coffee"
   APP_URL=http://localhost

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=apipicoffe
   DB_USERNAME=root
   DB_PASSWORD=

   FILESYSTEM_DISK=public   # Gunakan 'public' untuk lokal, 's3' untuk produksi
   ```

7. **Jalankan migrasi & seeder**

   ```bash
   php artisan migrate --seed
   ```

8. **Buat symlink storage**

   ```bash
   php artisan storage:link
   ```

9. **Build assets frontend**

   ```bash
   npm run dev
   ```

10. **Akses aplikasi** di `http://localhost/APIPICOFFE-main/public`

---

## ☁️ Deployment

### Railway (Rekomendasi)

Proyek ini sudah siap deploy ke **Railway** menggunakan Docker:

1. Push kode ke GitHub
2. Hubungkan repository ke Railway
3. Tambahkan environment variables berikut di Railway:

   ```
   APP_KEY=<hasil php artisan key:generate --show>
   APP_ENV=production
   DB_HOST=<Railway MySQL host>
   DB_DATABASE=<nama database>
   DB_USERNAME=<username>
   DB_PASSWORD=<password>
   AWS_ACCESS_KEY_ID=<Cloudflare R2 key>
   AWS_SECRET_ACCESS_KEY=<Cloudflare R2 secret>
   AWS_BUCKET=<nama bucket>
   AWS_ENDPOINT=https://<account_id>.r2.cloudflarestorage.com
   AWS_URL=https://pub-<id>.r2.dev
   AWS_USE_PATH_STYLE_ENDPOINT=true
   ```

4. Railway akan otomatis build & deploy via Dockerfile

### Google Cloud Run

Gunakan `cloudbuild.yaml` untuk deployment via Google Cloud Build:

```bash
gcloud builds submit --config cloudbuild.yaml
```

---

## 🗂️ Environment Variables Penting

| Variable                       | Keterangan                                  |
|-------------------------------|----------------------------------------------|
| `APP_KEY`                     | Application encryption key (wajib diisi)     |
| `APP_URL`                     | URL publik aplikasi                          |
| `DB_*`                        | Konfigurasi koneksi MySQL                    |
| `FILESYSTEM_DISK`             | `public` (lokal) atau `s3` (produksi/R2)     |
| `AWS_ACCESS_KEY_ID`           | Cloudflare R2 access key                     |
| `AWS_SECRET_ACCESS_KEY`       | Cloudflare R2 secret key                     |
| `AWS_BUCKET`                  | Nama bucket Cloudflare R2                    |
| `AWS_ENDPOINT`                | Endpoint API R2 (upload dari server)         |
| `AWS_URL`                     | URL publik R2 (untuk akses gambar di browser)|
| `AWS_DEFAULT_REGION`          | Selalu `auto` untuk Cloudflare R2            |
| `AWS_USE_PATH_STYLE_ENDPOINT` | Selalu `true` untuk Cloudflare R2            |
| `STORAGE_PROXY_MEDIA`         | `true` = gambar diproxy via `/media/...`     |

---

## 📁 Struktur Routes

| Method | URL                         | Akses             | Fungsi                         |
|--------|-----------------------------|-------------------|--------------------------------|
| GET    | `/login`                    | Guest             | Halaman login                  |
| GET    | `/`                         | Cashier+          | Dashboard POS                  |
| POST   | `/orders`                   | Auth              | Buat order baru                |
| GET    | `/receipt/{orderNumber}`    | Auth              | Tampilkan struk                |
| GET    | `/menu`                     | Admin+            | Manajemen menu                 |
| GET    | `/reports`                  | Admin+            | Laporan penjualan              |
| GET    | `/reports/export`           | Admin+            | Export laporan                 |
| GET    | `/settings`                 | Auth              | Halaman pengaturan             |
| POST   | `/settings/identity`        | Manager           | Update identitas bisnis        |
| POST   | `/settings/employees`       | Manager           | Tambah karyawan                |

---

## 📄 Lisensi

Proyek ini dibuat untuk keperluan **tugas akademik (RPL)** dan tidak untuk distribusi komersial.

---

<p align="center">
  Dibuat dengan ❤️ menggunakan <a href="https://laravel.com">Laravel</a> oleh KELOMPOK 6_RPL_2025A
</p>JJ
