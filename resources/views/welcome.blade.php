<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Absensi - {{ \App\Models\PengaturanSistem::get('nama_instansi', 'KNMP') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-tr from-slate-50 via-blue-50/20 to-slate-100 min-h-screen flex flex-col">

    <!-- Header / Navigasi -->
    <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-slate-100 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <!-- Kiri: Logo KNMP -->
            <div class="flex items-center space-x-3">
                <img src="{{ asset('logo-knmp.png') }}" alt="Logo KNMP" class="h-12 w-auto object-contain">
                <div class="hidden md:block">
                </div>
            </div>

            <!-- Kanan: Logo KKP & Ekonomi Biru -->
            <div class="flex items-center space-x-3 sm:space-x-4">
                <div class="h-8 w-px bg-slate-200 hidden sm:block"></div>
                <div class="bg-[#0B3B60] p-1 rounded-lg flex items-center justify-center">
                    <img src="{{ asset('Ekonomi Biru untuk Indonesia Emas (Putih).png') }}" alt="Logo Ekonomi Biru" class="h-8 sm:h-10 w-auto object-contain">
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
        <!-- Background Decorative Elements -->
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-blue-100/40 rounded-full blur-3xl -z-10"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-sky-100/40 rounded-full blur-3xl -z-10"></div>

        <div class="max-w-5xl w-full">
            <!-- Hero Title -->
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-800 border border-blue-100 mb-6">
                    <span class="w-2 h-2 rounded-full bg-[#0B3B60] mr-2 animate-pulse"></span>
                    Kementerian Kelautan dan Perikanan
                </span>
                <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
                    Sistem Informasi <span class="bg-clip-text text-transparent bg-gradient-to-r from-[#0B3B60] to-blue-500">Absensi Pegawai</span>
                </h1>
                <p class="mt-4 text-base md:text-lg text-slate-600 font-normal leading-relaxed">
                    Portal Layanan Presensi Kampus Nelayan Merah Putih (KNMP) untuk Pegawai dan Administrator.
                </p>
            </div>

            <!-- Panel Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                <!-- Card Pegawai -->
                <div class="group bg-white/70 backdrop-blur-sm rounded-2xl shadow-sm border border-slate-100 hover:border-blue-200/50 hover:shadow-xl hover:-translate-y-1 transition duration-300 flex flex-col h-full overflow-hidden">
                    <div class="p-8 flex-grow">
                        <div class="w-14 h-14 bg-gradient-to-br from-blue-50 to-blue-100 text-[#0B3B60] rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition duration-300">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <h2 class="text-2xl font-bold text-slate-800 mb-3">Panel Pegawai</h2>
                        <p class="text-slate-500 leading-relaxed mb-6 text-sm">Masuk untuk melakukan absensi harian (GPS & QR), memantau riwayat presensi, serta mengajukan izin atau sakit secara praktis.</p>
                        
                        <!-- List Fitur -->
                        <ul class="space-y-2 text-sm text-slate-600">
                            <li class="flex items-center">
                                <svg class="w-4 h-4 text-emerald-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Presensi via Geolocation & QR Code
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 text-emerald-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Riwayat Presensi & Profil Pegawai
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 text-emerald-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Form Pengajuan Izin / Sakit
                            </li>
                        </ul>
                    </div>
                    <div class="px-8 pb-8">
                        <a href="{{ url('/pegawai') }}" class="block w-full text-center bg-gradient-to-r from-[#0B3B60] to-blue-700 hover:from-blue-700 hover:to-[#0B3B60] text-white font-semibold py-3.5 px-4 rounded-xl transition duration-300 shadow-md shadow-blue-900/10 hover:shadow-blue-900/20 transform hover:-translate-y-0.5">
                            Login sebagai Pegawai
                        </a>
                    </div>
                </div>

                <!-- Card Admin -->
                <div class="group bg-white/70 backdrop-blur-sm rounded-2xl shadow-sm border border-slate-100 hover:border-blue-200/50 hover:shadow-xl hover:-translate-y-1 transition duration-300 flex flex-col h-full overflow-hidden">
                    <div class="p-8 flex-grow">
                        <div class="w-14 h-14 bg-gradient-to-br from-slate-100 to-slate-200 text-slate-800 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition duration-300">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                        <h2 class="text-2xl font-bold text-slate-800 mb-3">Panel Admin</h2>
                        <p class="text-slate-500 leading-relaxed mb-6 text-sm">Masuk untuk mengelola data master pegawai, memantau rekap absensi harian, memvalidasi perizinan, dan mengatur sistem.</p>
                        
                        <!-- List Fitur -->
                        <ul class="space-y-2 text-sm text-slate-600">
                            <li class="flex items-center">
                                <svg class="w-4 h-4 text-emerald-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Dashboard & Statistik Kehadiran Realtime
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 text-emerald-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Manajemen Pengguna & Perizinan Pegawai
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 text-emerald-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Export Laporan Excel & Konfigurasi Jam Kerja
                            </li>
                        </ul>
                    </div>
                    <div class="px-8 pb-8">
                        <a href="{{ url('/admin') }}" class="block w-full text-center bg-slate-900 hover:bg-slate-800 text-white font-semibold py-3.5 px-4 rounded-xl transition duration-300 shadow-md transform hover:-translate-y-0.5">
                            Login sebagai Administrator
                        </a>
                    </div>
                </div>
            </div>

            <!-- Scanner Akses Cepat -->
            <div class="mt-16 bg-white/40 border border-slate-100 rounded-2xl p-6 md:p-8 max-w-2xl mx-auto text-center backdrop-blur-sm shadow-sm hover:shadow-md transition duration-300">
                <div class="inline-flex items-center justify-center p-3 bg-white rounded-2xl shadow-sm border border-slate-100 mb-4">
                    <svg class="w-8 h-8 text-[#0B3B60]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-2">Akses Cepat Scanner QR Code</h3>
                <p class="text-sm text-slate-500 max-w-md mx-auto mb-6">Digunakan khusus oleh Admin / Petugas Keamanan untuk memindai QR Code pegawai di lokasi pintu masuk/keluar.</p>
                <a href="{{ url('/admin') }}" class="inline-flex items-center justify-center px-6 py-3 border border-slate-200 shadow-sm text-sm font-semibold rounded-xl text-slate-700 bg-white hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0B3B60] transition duration-200">
                    Buka Perangkat Scanner QR
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-100 py-6 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="mb-1">&copy; {{ date('Y') }} Kampus Nusantara Maritim Patimban (KNMP) - Kementerian Kelautan dan Perikanan.</p>
            <p>Hak Cipta Dilindungi.</p>
        </div>
    </footer>

</body>
</html>
