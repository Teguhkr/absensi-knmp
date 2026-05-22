<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Absensi - {{ \App\Models\PengaturanSistem::get('nama_instansi', 'KNMP') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">

    <div class="flex-grow flex items-center justify-center p-6">
        <div class="max-w-4xl w-full">
            <div class="text-center mb-12">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-blue-100 text-blue-600 mb-4">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 tracking-tight">Sistem Absensi Pegawai</h1>
                <p class="mt-4 text-xl text-gray-600 font-medium">{{ \App\Models\PengaturanSistem::get('nama_instansi', 'KNMP') }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-3xl mx-auto">
                <!-- Card Pegawai -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden hover:shadow-2xl transition duration-300 border border-gray-100 flex flex-col h-full">
                    <div class="p-8 flex-grow">
                        <div class="w-14 h-14 bg-teal-100 text-teal-600 rounded-xl flex items-center justify-center mb-6">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-3">Panel Pegawai</h2>
                        <p class="text-gray-500 leading-relaxed mb-6">Masuk untuk melakukan absensi harian (GPS & QR), melihat riwayat absensi, dan mengajukan izin/sakit.</p>
                    </div>
                    <div class="px-8 pb-8">
                        <a href="{{ url('/pegawai') }}" class="block w-full text-center bg-teal-600 hover:bg-teal-700 text-white font-semibold py-3 px-4 rounded-xl transition duration-200 shadow-md">
                            Login sebagai Pegawai
                        </a>
                    </div>
                </div>

                <!-- Card Admin -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden hover:shadow-2xl transition duration-300 border border-gray-100 flex flex-col h-full">
                    <div class="p-8 flex-grow">
                        <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center mb-6">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-3">Panel Admin</h2>
                        <p class="text-gray-500 leading-relaxed mb-6">Masuk untuk mengelola data pegawai, laporan absensi, menyetujui izin, dan mengatur konfigurasi sistem.</p>
                    </div>
                    <div class="px-8 pb-8">
                        <a href="{{ url('/admin') }}" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-xl transition duration-200 shadow-md">
                            Login sebagai Administrator
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Scanner Akses Cepat -->
            <div class="mt-12 text-center">
                <a href="{{ url('/admin') }}" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                    <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                    Buka Perangkat Scanner QR
                </a>
                <p class="mt-3 text-sm text-gray-500">Scan QR Code dilakukan dari perangkat yang terhubung ke sistem oleh Admin/Satpam.</p>
            </div>
        </div>
    </div>

</body>
</html>
