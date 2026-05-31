# 🕒 Absensi KNMP - Sistem Presensi Karyawan Modern

Sistem Presensi Karyawan (Absensi) berbasis web dan QR Code yang dirancang secara modern dan responsif menggunakan **Laravel 12** dan **Filament PHP v5**. Sistem ini dilengkapi fitur keamanan ganda berupa validasi lokasi berbasis **GPS Geofencing (Radius Kantor)** dan pencatatan kehadiran menggunakan **QR Code scanner**.

---

## Table of Contents

- [Project Description](#project-description)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Database Schema](#database-schema)
- [Contributing](#contributing)
- [License](#license)

---

## Project Description

Absensi KNMP adalah sistem manajemen kehadiran karyawan yang dikembangkan khusus untuk Kampung Nelayan Merah Putih (KNMP) di bawah Kementerian Kelautan dan Perikanan. Sistem ini menyediakan dua panel utama yang terpisah:

- **Admin Panel**: Untuk HRD/Manajemen dalam mengelola pegawai, memantau kehadiran, dan mengatur sistem
- **Pegawai Panel**: Untuk karyawan dalam melakukan presensi, mengajukan izin, dan melihat riwayat kehadiran

---

---

## Features

### User Roles & Panels

| Role | Panel | Description |
| :--- | :--- | :--- |
| **Admin / HRD** | Admin Panel (`/admin`) | Full system access including employee management, attendance monitoring, and system configuration |
| **Pegawai** | Pegawai Panel (`/pegawai`) | Self-service attendance, leave requests, and personal attendance history |

#### Admin Panel Features

- **Dashboard Attendance**: Statistics visualization, monthly attendance trends, and recent attendance records
- **Employee Management**: Create, edit, deactivate accounts; automatic QR Token generation
- **Attendance Recap**: Monitor, correct, and export all employee attendance history
- **Leave Management**: Approval/rejection workflow for sick leave, vacation, and business trips
- **Announcement Board**: Publish announcements to all employee dashboards
- **System Settings**: Dynamic configuration of working hours, lateness tolerance, office coordinates, and GPS validation

#### Employee Panel Features

- **Quick Attendance**: Fast check-in/check-out buttons with device location detection
- **Leave Requests**: Submit leave/sick requests with supporting documentation
- **Attendance History**: Personal attendance records with status indicators
- **Latest Announcements**: Real-time announcement widget on dashboard

### GPS Geofencing (Location Validation)

- Uses Haversine formula for precise distance calculation between employee location and office
- Attendance restricted to within configured office radius (e.g., 500 meters)
- GPS validation can be disabled flexibly via admin settings

### QR Code Attendance

- Each employee receives a unique encrypted QR Code token
- Attendance can be recorded by scanning employee QR Code at designated scanners without individual login

---

## Technology Stack

| Technology | Version | Purpose |
| :--- | :--- | :--- |
| [Laravel](https://laravel.com) | 12.x | Backend framework |
| [Filament](https://filamentphp.com) | 5.x | Admin panel & UI engine (TALL Stack) |
| [PHP](https://www.php.net) | >= 8.2 | Server-side language |
| [MySQL](https://www.mysql.com) / [MariaDB](https://mariadb.org) | - | Database |
| [SQLite](https://www.sqlite.org) | - | Local development database |
| [Vite](https://vitejs.dev) | - | Frontend build tool |
| [Tailwind CSS](https://tailwindcss.com) | 3.x | CSS framework |
| [Alpine.js](https://alpinejs.dev) | 3.x | JavaScript interactivity |
| [Livewire](https://livewire.laravel.com) | - | Dynamic UI components |

---

## Requirements

| Requirement | Minimum Version |
| :--- | :--- |
| PHP | 8.2 |
| Composer | Latest |
| Node.js | 18.x |
| NPM | 9.x |
| Database Server | MySQL 5.7+, MariaDB 10.3+, or SQLite 3 |

---

## Installation

### 1. Clone Repository

```bash
git clone https://github.com/Teguhkr/absensi-knmp.git
cd absensi-knmp
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install JavaScript Dependencies

```bash
npm install
```

### 4. Environment Configuration

Copy the example environment file:

```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

### 5. Configure Database

Edit `.env` file with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absensi_knmp
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 6. Run Migrations & Seeders

```bash
php artisan migrate --seed
```

### 7. Link Storage

```bash
php artisan storage:link
```

### 8. Start Development Server

```bash
php artisan serve
```

The application will be available at [http://127.0.0.1:8000](http://127.0.0.1:8000).

---

## Configuration

### Environment Variables

| Variable | Description | Default |
| :--- | :--- | :--- |
| `APP_NAME` | Application name | `Absensi KNMP` |
| `APP_ENV` | Environment (local, production) | `local` |
| `APP_DEBUG` | Debug mode | `true` |
| `APP_URL` | Application URL | `http://localhost` |
| `DB_CONNECTION` | Database driver | `mysql` |

### System Settings (Admin Panel)

Access `/admin/pengaturan-sistem` to configure:

- **Work Hours**: Jam Masuk (default: 08:00), Jam Pulang (default: 16:00)
- **Lateness Tolerance**: Toleransi Keterlambatan in minutes (default: 15)
- **GPS Settings**: Office coordinates (latitude/longitude) and radius in meters (default: 500)
- **GPS Validation**: Enable/disable GPS checking during attendance

### File Storage

Employee photos and attendance images are stored in `storage/app/public/pegawai/`. Access via `/storage` symbolic link.

---

## Usage

### Development Commands

```bash
# Start development server with hot reload
npm run dev

# Build production assets
npm run build

# Run PHP tests
php artisan test

# Run linter
vendor/bin/pint

# Clear application cache
php artisan optimize:clear
```

### Production Deployment

```bash
# Build optimized assets
npm run build

# Cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
php artisan migrate --force
```

### Demo Accounts

#### Administrator

- **URL**: `http://127.0.0.1:8000/admin`
- **Email**: `teguhk356@gmail.com`
- **Password**: `password`

#### Employees

- **URL**: `http://127.0.0.1:8000/pegawai`
- **Default Password**: `password`

| Name | Email | NIP | Department |
| :--- | :--- | :--- | :--- |
| Budi Santoso | `budi@knmp.go.id` | 198501012010011001 | Bidang Perikanan |
| Siti Rahayu | `siti@knmp.go.id` | 199003152012012002 | Bidang Umum |
| Agus Hermawan | `agus@knmp.go.id` | 198712052011011003 | Bidang Perikanan |

---

## Database Schema

### Core Tables

| Table | Description | Key Fields |
| :--- | :--- | :--- |
| `users` | Employee accounts | id, name, email, nik, jabatan, departemen, role, qr_token |
| `absensi` | Attendance records | id, user_id, tanggal, jam_masuk, jam_pulang, status, latitude/longitude |
| `izins` | Leave requests | id, user_id, jenis, tanggal_mulai, tanggal_selesai, status, keterangan |
| `pengumumen` | Announcements | id, judul, isi, tgl_posting |
| `pengaturan_sistems` | System configuration | key, value pairs |

---

## Contribution Guidelines

### Development Setup

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/your-feature-name`
3. Install dependencies: `composer install && npm install`
4. Create your feature branch from `main`
5. Follow PSR-12 coding standards
6. Run tests: `php artisan test`
7. Submit a pull request

### Code Standards

- Follow PSR-12 PHP coding standards
- Use Laravel Pint for code formatting: `vendor/bin/pint`
- Write meaningful commit messages
- Include tests for new features

### Project Structure

```
app/
├── Filament/
│   ├── Pages/           # Admin pages
│   ├── Resources/       # CRUD resources (Pegawai, Absensi, Izin, Pengumuman)
│   └── Widgets/         # Dashboard widgets
├── Http/
│   └── Controllers/     # HTTP controllers
└── Models/              # Eloquent models
```

---

## License

This project is open-sourced software licensed under the [MIT License](LICENSE).
