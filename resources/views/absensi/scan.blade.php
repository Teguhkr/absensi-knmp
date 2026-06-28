<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Presensi - {{ \App\Models\PengaturanSistem::get('nama_instansi', 'KNMP') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-tr from-slate-50 via-blue-50/20 to-slate-100 min-h-screen flex flex-col">

    <!-- Header / Navigasi (Sama dengan Welcome Page) -->
    <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-slate-100 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <!-- Kiri: Logo KNMP -->
            <a href="{{ url('/') }}" class="flex items-center space-x-3 hover:opacity-90 transition duration-200">
                <img src="{{ asset('logo-knmp.png') }}" alt="Logo KNMP" class="h-12 w-auto object-contain">
                <div class="hidden md:block">
                    <span class="text-sm font-bold text-slate-800 tracking-wide uppercase block">Presensi KNMP</span>
                    <span class="text-[10px] text-slate-500 font-semibold tracking-wider uppercase block">Kampung Nelayan Merah Putih</span>
                </div>
            </a>
            
            <!-- Kanan: Logo KKP & Ekonomi Biru -->
            <div class="flex items-center space-x-3 sm:space-x-4">
                <img src="{{ asset('Logo KKP Pangan Biru Putih.png') }}" alt="Logo KKP" class="h-10 sm:h-12 w-auto object-contain">
                <div class="h-8 w-px bg-slate-200 hidden sm:block"></div>
                <div class="bg-[#0B3B60] p-1 rounded-lg flex items-center justify-center">
                    <img src="{{ asset('Ekonomi Biru untuk Indonesia Emas (Putih).png') }}" alt="Logo Ekonomi Biru" class="h-8 sm:h-10 w-auto object-contain">
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center py-12 px-4 relative overflow-hidden">
        <!-- Background Decorative Elements -->
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-blue-100/40 rounded-full blur-3xl -z-10"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-sky-100/40 rounded-full blur-3xl -z-10"></div>

        <div class="max-w-md w-full bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden backdrop-blur-sm">
            <!-- Header Card KKP Theme -->
            <div class="bg-[#0B3B60] p-6 text-center relative">
                <div class="absolute inset-0 bg-gradient-to-b from-white/10 to-transparent pointer-events-none"></div>
                <h1 class="text-xl font-extrabold text-white tracking-wide uppercase">Scan Presensi Pegawai</h1>
                <p class="text-blue-100/90 text-xs font-semibold mt-1 tracking-wider uppercase">{{ \App\Models\PengaturanSistem::get('nama_instansi', 'KNMP') }}</p>
            </div>

            <!-- Card Body -->
            <div class="p-8">
                <!-- Status Notifications -->
                @if(session('success'))
                    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl relative mb-6 text-sm flex items-start" role="alert">
                        <svg class="w-5 h-5 text-emerald-600 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl relative mb-6 text-sm flex items-start" role="alert">
                        <svg class="w-5 h-5 text-rose-600 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="font-medium">{{ session('error') }}</span>
                    </div>
                @endif

                <!-- Pegawai Profile Info -->
                <div class="flex items-center space-x-4 mb-8 p-4 bg-slate-50/70 border border-slate-100 rounded-2xl">
                    <img class="w-16 h-16 rounded-full border-2 border-[#0B3B60]/20 object-cover shadow-sm" 
                         src="{{ $pegawai->foto ? asset('storage/' . $pegawai->foto) : 'https://ui-avatars.com/api/?name=' . urlencode($pegawai->name) . '&background=0B3B60&color=fff' }}" 
                         alt="Foto Profil">
                    <div class="overflow-hidden">
                        <h2 class="text-lg font-bold text-slate-800 truncate">{{ $pegawai->name }}</h2>
                        <p class="text-slate-500 text-xs font-mono tracking-wider mt-0.5">{{ $pegawai->nik }}</p>
                        <span class="inline-flex px-2.5 py-0.5 bg-blue-50 text-[#0B3B60] border border-blue-100 text-[10px] font-bold rounded-full mt-1.5 uppercase tracking-wide">
                            {{ $pegawai->departemen ?? 'Pegawai' }}
                        </span>
                    </div>
                </div>

                <!-- Clock / Time Info Grid -->
                <div class="grid grid-cols-2 gap-4 mb-8">
                    <!-- Jam Masuk -->
                    <div class="p-4 border rounded-2xl flex flex-col items-center justify-center text-center {{ $absensiHariIni && $absensiHariIni->jam_masuk ? 'border-emerald-200 bg-emerald-50/30' : 'border-slate-100 bg-slate-50/30' }}">
                        <span class="text-xs text-slate-500 font-medium uppercase tracking-wider mb-1">Jam Masuk</span>
                        <span class="text-xl font-extrabold {{ $absensiHariIni && $absensiHariIni->jam_masuk ? 'text-emerald-700' : 'text-slate-400' }}">
                            {{ $absensiHariIni && $absensiHariIni->jam_masuk ? \Carbon\Carbon::parse($absensiHariIni->jam_masuk)->format('H:i') : '--:--' }}
                        </span>
                    </div>
                    <!-- Jam Pulang -->
                    <div class="p-4 border rounded-2xl flex flex-col items-center justify-center text-center {{ $absensiHariIni && $absensiHariIni->jam_pulang ? 'border-amber-200 bg-amber-50/30' : 'border-slate-100 bg-slate-50/30' }}">
                        <span class="text-xs text-slate-500 font-medium uppercase tracking-wider mb-1">Jam Pulang</span>
                        <span class="text-xl font-extrabold {{ $absensiHariIni && $absensiHariIni->jam_pulang ? 'text-amber-700' : 'text-slate-400' }}">
                            {{ $absensiHariIni && $absensiHariIni->jam_pulang ? \Carbon\Carbon::parse($absensiHariIni->jam_pulang)->format('H:i') : '--:--' }}
                        </span>
                    </div>
                </div>

                <!-- Process Form -->
                <form action="{{ route('absensi.scan.process', $pegawai->qr_token) }}" method="POST" class="space-y-4">
                    @csrf
                    @if(!$absensiHariIni || !$absensiHariIni->jam_masuk)
                        <button type="submit" name="absen_masuk" class="w-full bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-teal-600 hover:to-emerald-600 text-white font-bold py-3.5 px-4 rounded-xl transition duration-300 shadow-md shadow-emerald-900/10 hover:shadow-emerald-900/20 transform hover:-translate-y-0.5 flex items-center justify-center text-sm tracking-wide">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                            PROSES PRESENSI MASUK
                        </button>
                    @elseif($absensiHariIni && $absensiHariIni->jam_masuk && !$absensiHariIni->jam_pulang)
                        <button type="submit" name="absen_pulang" 
                                @if($warningPulangCepat) onclick="return confirm('{{ $warningPulangCepat }}')" @endif
                                class="w-full bg-gradient-to-r from-rose-600 to-red-600 hover:from-red-600 hover:to-rose-600 text-white font-bold py-3.5 px-4 rounded-xl transition duration-300 shadow-md shadow-rose-900/10 hover:shadow-rose-900/20 transform hover:-translate-y-0.5 flex items-center justify-center text-sm tracking-wide">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            PROSES PRESENSI PULANG
                        </button>
                    @else
                        <div class="text-center p-4 bg-slate-50 text-slate-500 rounded-xl border border-slate-100 text-sm font-semibold tracking-wide flex items-center justify-center">
                            <svg class="w-5 h-5 text-slate-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Presensi hari ini sudah selesai.
                        </div>
                    @endif
                </form>

                <!-- Back button -->
                <div class="mt-6 text-center">
                    <a href="{{ url('/') }}" class="text-xs font-semibold text-[#0B3B60] hover:text-blue-700 transition duration-200 inline-flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Kembali ke Halaman Utama
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-100 py-6 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="mb-1">&copy; {{ date('Y') }} Kampung Nelayan Merah Putih (KNMP) - Kementerian Kelautan dan Perikanan.</p>
            <p>Hak Cipta Dilindungi.</p>
        </div>
    </footer>

</body>
</html>
