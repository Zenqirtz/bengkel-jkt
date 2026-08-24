# 🚗 Sistem Informasi Manajemen Bengkel (Body Repair & Cat)

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![Vite](https://img.shields.io/badge/Vite-6.x-646CFF?style=for-the-badge&logo=vite&logoColor=white)](https://vitejs.dev)
[![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com)

Aplikasi web terintegrasi berbasis **Laravel 12** yang dirancang untuk mengelola seluruh alur operasional bengkel perbaikan bodi dan pengecatan mobil (Body Repair & Paint Workshop). Sistem ini mempermudah proses mulai dari penerimaan kendaraan, estimasi biaya, monitoring pengerjaan, manajemen stok gudang, pencatatan keuangan/kasir, hingga pelaporan berkala.

---

## 📌 Fitur Utama & Modul

### 1. 👥 Customer Service (Front Office)
* **Manajemen SPK (Surat Perintah Kerja)**: Input SPK masuk, SPK keluar, SPK batal, dan penutupan SPK.
* **Manajemen Pelanggan, Pemilik, & Perantara**: Database terstruktur untuk data pemilik unit dan pihak perantara.
* **Integrasi Asuransi**: Manajemen rekanan asuransi dan penanganan berkas klaim.
* **Invoice & Kwitansi OR (Own Risk)**: Penerbitan dan pencatatan tanda terima invoice klaim asuransi.
* **Laporan CS**: Laporan kendaraan masuk/keluar, outstanding OR, dan posisi perbaikan unit.

### 2. 📋 Administrasi & Estimasi
* **Konsep & Rincian Estimasi**: Pembuatan estimasi biaya perbaikan dan komponen penggantian suku cadang.
* **Progress Kerja & Unit Rawat Jalan**: Monitoring status pengerjaan fisik kendaraan secara real-time.
* **Klaim Dokumen & Salvage**: Pengelolaan dokumen klaim dan pengiriman/penerimaan suku cadang bekas (salvage).
* **Laporan Analitik**: Laporan aging penagihan, aging penawaran, rekap point panel pengerjaan, dan insentif surveyor/marketing.

### 3. 📦 Gudang & Logistik (Inventory Management)
* **Master Barang**: Pengelolaan master data sparepart, bahan baku, dan material cat.
* **Order & Penerimaan Barang**: Pembuatan Order Pembelian (PO), input pembelian, dan retur pembelian.
* **Pengeluaran & Permintaan Barang**: Alokasi pemakaian bahan/sparepart per unit SPK.
* **Stock Opname & Tutup Buku**: Penyesuaian stok fisik periodik dan kontrol pemakaian material.

### 4. 💳 Keuangan & Akuntansi (Finance & Cashier)
* **Manajemen Kas & Bank**: Input transaksi kas masuk/keluar, bank masuk/keluar, serta monitoring mutasi rekening.
* **Pembayaran & Penerimaan Gabungan**: Transaksi pelunasan multi-invoice atau multi-SPK.
* **Uang Muka (Down Payment)**: Pencatatan uang muka pembelian dan uang muka penjualan.
* **Input Memorial & Jurnal**: Pencatatan jurnal memorial untuk penyesuaian akuntansi.
* **Pelaporan Keuangan**: Laporan kwitansi lunas, laporan kas, buku besar, dan rekap voucher bank.

### 5. ⚙️ Pengaturan & Keamanan (System Settings)
* **Role & Privilege Management**: Pengaturan hak akses bertingkat per grup, per user, dan per cabang bengkel.
* **Master Data Otomotif**: Konfigurasi jenis/merk/tipe kendaraan, panel pekerjaan, dan posisi pekerjaan.
* **Audit & Keamanan**: Pencatatan log aktivitas user (*Audit Trail*) dan riwayat login.
* **Backup & Profil**: Backup database berkala dan profil informasi perusahaan.

---

## 🛠️ Tools & Tech Stack

### Backend & Core
* **Language**: PHP >= 8.2
* **Framework**: [Laravel 12](https://laravel.com)
* **Authentication**: Laravel Jetstream
* **Export & Report Engine**: [Laravel Excel (Maatwebsite)](https://laravel-excel.com/)
* **Image Processing**: [Intervention Image](https://image.intervention.io/)
* **Database**: MySQL / MariaDB

### Frontend & UI/UX
* **Template**: Materialize Admin Template
* **CSS Framework**: [Bootstrap 5.3](https://getbootstrap.com/)
* **Build Tool & Bundler**: [Vite 6](https://vitejs.dev/) & Laravel Vite Plugin
* **Preprocessor**: SASS / SCSS
* **Icons**: Iconify & Tabler / Boxicons

### JavaScript Plugins & Components
* **DataTables Suite**: Server-side & client-side datatables dengan export buttons, fixed columns, dan responsive view.
* **Form & Input**: Select2, Flatpickr (Datepicker), Cleave.js, Tagify, Bootstrap-Select.
* **Visualisasi & Chart**: ApexCharts & Chart.js.
* **Komponen Interaktif**: SweetAlert2 (Pop-up/Dialog), Dropzone (File upload), FullCalendar, Notiflix / Notyf.

### Environment & Development Tools
* **Local Server**: [Laragon](https://laragon.org/) / XAMPP
* **Package Manager**: [Composer](https://getcomposer.org/) (PHP) & [NPM](https://nodejs.org/) (JavaScript)
* **Version Control**: Git & GitHub

---

## 🚀 Panduan Instalasi (Local Development)

Ikuti langkah-langkah berikut untuk menjalankan proyek ini di lingkungan lokal (Localhost):

### 1. Clone Repositori
```bash
git clone https://github.com/Zenqirtz/bengkel-jkt.git
cd bengkel-jkt
```

### 2. Konfigurasi Environment (`.env`)
Salin file konfigurasi `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```
Sesuaikan kredensial database pada file `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database_anda
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Install Dependensi PHP & JavaScript
```bash
# Install PHP Dependencies
composer install

# Install NPM Packages
npm install
```

### 4. Generate Application Key & Migrasi Database
```bash
# Generate Key
php artisan key:generate

# Migrasi Database
php artisan migrate
```

### 5. Jalankan Server Pengembangan
Buka terminal dan jalankan dev server:
```bash
# Jalankan Vite untuk asset frontend
npm run dev

# Jalankan Local Server Laravel (di terminal terpisah)
php artisan serve
```
Akses aplikasi melalui browser di: `http://127.0.0.1:8000`

---

## 📄 Lisensi
Proyek ini dikembangkan untuk kebutuhan operasional bengkel perbaikan bodi mobil. Hak cipta dilindungi undang-undang.
