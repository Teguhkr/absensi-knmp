# 🕒 Absensi KNMP - Sistem Presensi Karyawan Modern

Sistem Presensi Karyawan (Absensi) berbasis web dan QR Code yang dirancang secara modern dan responsif menggunakan **Laravel 11** dan **Filament PHP v3**. Sistem ini dilengkapi fitur keamanan ganda berupa validasi lokasi berbasis **GPS Geofencing (Radius Kantor)** dan pencatatan kehadiran menggunakan **QR Code scanner**.

---

## ✨ Fitur Utama

### 👥 Peran & Panel Pengguna
*   **Admin Panel (HRD / Manajemen)**:
    *   **Dashboard Kehadiran**: Visualisasi statistik kehadiran hari ini, grafik tren kehadiran bulanan, serta daftar presensi terbaru.
    *   **Manajemen Pegawai**: Mengelola data akun pegawai (tambah, edit, nonaktifkan, generate QR Token otomatis).
    *   **Rekapitulasi Absensi**: Memantau, mengoreksi, dan mengekspor seluruh riwayat kehadiran pegawai.
    *   **Manajemen Izin & Cuti**: Sistem verifikasi persetujuan (Approve/Reject) pengajuan izin sakit, cuti, maupun dinas luar.
    *   **Papan Pengumuman**: Menyebarluaskan pengumuman atau informasi instansi kepada seluruh dashboard pegawai.
    *   **Pengaturan Sistem**: Konfigurasi instansi secara dinamis (jam kerja, toleransi terlambat, koordinat kantor, radius presensi, dan status validasi GPS).
*   **Pegawai Panel (Karyawan)**:
    *   **Presensi Mandiri**: Tombol cepat untuk *Absen Masuk* dan *Absen Pulang* yang terintegrasi dengan deteksi lokasi perangkat.
    *   **Pengajuan Izin**: Mengajukan surat izin atau cuti secara mandiri dengan lampiran alasan/dokumen pendukung.
    *   **Riwayat Presensi**: Melihat rekapan riwayat kehadiran pribadi dari hari ke hari lengkap dengan statusnya.
    *   **Widget Pengumuman**: Widget info terbaru langsung di dashboard pegawai.

### 📍 GPS Geofencing (Validasi Radius)
*   Menghitung jarak koordinat pegawai dengan koordinat kantor secara presisi menggunakan **Formula Haversine**.
*   Pegawai hanya dapat melakukan absensi jika berada di dalam radius kantor yang telah ditentukan oleh Admin (misal: 500 meter).
*   Fitur validasi GPS dapat dinonaktifkan secara fleksibel melalui panel pengaturan admin jika diperlukan.

### 🔍 Presensi Berbasis QR Code
*   Setiap pegawai mendapatkan QR Code unik dengan token terenkripsi.
*   Presensi dapat dilakukan dengan memindai (scan) QR Code pegawai pada pos atau perangkat pemindai yang ditunjuk instansi tanpa harus login ke akun masing-masing.

---

## 🛠️ Spesifikasi Teknologi (Tech Stack)

*   **Backend Framework**: [Laravel 11.x](https://laravel.com)
*   **Admin Panel / UI Engine**: [Filament v3](https://filamentphp.com) (TALL Stack: Tailwind CSS, Alpine.js, Laravel, Livewire)
*   **Database**: MySQL / MariaDB (Dukungan SQLite untuk lokal)
*   **Frontend Tooling**: Vite & Tailwind CSS
*   **Lokasi & Geolocation**: HTML5 Geolocation API

---

## ⚙️ Panduan Instalasi Lokal

Ikuti langkah-langkah di bawah ini untuk menjalankan project ini di komputer lokal Anda:

### 1. Prasyarat Sistem
Pastikan perangkat Anda sudah terinstall:
*   PHP >= 8.2
*   Composer (Dependency Manager)
*   Node.js & NPM
*   Database Server (MySQL / MariaDB)

### 2. Kloning Repositori
```bash
git clone https://github.com/Teguhkr/absensi-knmp.git
cd absensi-knmp
```

### 3. Install Dependensi PHP
```bash
composer install
```

### 4. Install Dependensi Javascript & Aset Frontend
```bash
npm install
npm run dev
```

### 5. Salin dan Sesuaikan Konfigurasi `.env`
Salin file `.env.example` ke `.env`:
```bash
cp .env.example .env
```
Buka file `.env` yang baru dibuat dan sesuaikan konfigurasi database Anda:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absensi_knmp
DB_USERNAME=username_database_anda
DB_PASSWORD=password_database_anda
```

### 6. Buat Kunci Aplikasi & Link Storage
```bash
php artisan key:generate
php artisan storage:link
```

### 7. Jalankan Migrasi & Database Seeder
Jalankan perintah berikut untuk membuat tabel dan mengisi data awal (default akun demo & pengaturan sistem):
```bash
php artisan migrate --seed
```

### 8. Jalankan Server Lokal
```bash
php artisan serve
```
Aplikasi sekarang dapat diakses melalui browser di alamat [http://127.0.0.1:8000](http://127.0.0.1:8000).

---

## 🔑 Akun Uji Coba (Demo Accounts)

Setelah menjalankan perintah `db:seed`, Anda dapat masuk dengan akun-akun berikut:

### Akun Administrator (Akses Admin Panel)
*   **URL**: `http://127.0.0.1:8000/admin`
*   **Email**: `teguhk356@gmail.com`
*   **Password**: `password`

### Akun Karyawan / Pegawai (Akses Pegawai Panel)
*   **URL**: `http://127.0.0.1:8000/pegawai`
*   **Password Default**: `password`
*   **Daftar Akun Pegawai**:
    | Nama Pegawai | Email | NIP | Departemen |
    | :--- | :--- | :--- | :--- |
    | Budi Santoso | `budi@knmp.go.id` | 198501012010011001 | Bidang Perikanan |
    | Siti Rahayu | `siti@knmp.go.id` | 199003152012012002 | Bidang Umum |
    | Agus Hermawan | `agus@knmp.go.id` | 198712052011011003 | Bidang Perikanan |

---

## 🗺️ Skema Aturan Jam Kerja & Presensi

Secara default (bisa diubah dari halaman **Pengaturan Sistem** di Admin Panel):
1.  **Jam Masuk**: `08:00`
2.  **Jam Pulang**: `16:00`
3.  **Toleransi Keterlambatan**: `15 Menit` (Karyawan melakukan absen setelah pukul `08:15` akan otomatis ditandai sebagai **Terlambat**).
4.  **Radius Lokasi**: `500 Meter` dari koordinat latitude & longitude kantor.

---

## 🔒 Lisensi

Project ini dilisensikan di bawah lisensi MIT. Silakan gunakan dan sesuaikan untuk kebutuhan Anda.
