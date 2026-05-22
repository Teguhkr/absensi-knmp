<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak | 403 Forbidden</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        body {
            background: radial-gradient(circle at 50% 50%, #0f172a 0%, #020617 100%);
        }
    </style>
</head>
<body class="text-slate-200 min-h-screen flex items-center justify-center p-4">
    <div class="relative max-w-lg w-full bg-slate-900/60 backdrop-blur-xl border border-slate-800 p-8 rounded-3xl shadow-2xl text-center overflow-hidden">
        <!-- Accent Glow -->
        <div class="absolute -top-24 -left-24 w-48 h-48 bg-rose-500/20 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -right-24 w-48 h-48 bg-teal-500/20 rounded-full blur-3xl"></div>

        <div class="relative">
            <!-- Icon -->
            <div class="inline-flex p-4 bg-rose-500/10 rounded-full text-rose-500 mb-6 animate-pulse">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.249-8.25-3.286zm0 13.036h.008v.008H12v-.008z"/>
                </svg>
            </div>

            <!-- Error Title -->
            <h1 class="text-4xl font-extrabold tracking-tight text-white mb-2">Akses Ditolak!</h1>
            <p class="text-rose-400 font-semibold text-sm tracking-wider uppercase mb-6">403 - Forbidden Area</p>

            <!-- Message Description -->
            <div class="text-slate-300 space-y-4 mb-8 text-sm md:text-base">
                <p>
                    Maaf, Anda tidak memiliki izin untuk mengakses halaman ini. Halaman ini khusus diperuntukkan untuk <strong>Administrator</strong>.
                </p>
                <p class="text-slate-400 text-xs md:text-sm bg-slate-950/40 p-4 rounded-2xl border border-slate-800/80">
                    Anda saat ini masuk sebagai <strong>{{ auth()->user()->name ?? 'Pengguna' }}</strong> ({{ ucfirst(auth()->user()->role ?? 'Pegawai') }}).
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
                @if(auth()->check() && auth()->user()->role === 'pegawai')
                    <a href="/pegawai" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 text-sm font-semibold rounded-2xl text-white bg-gradient-to-r from-teal-500 to-emerald-600 hover:from-teal-400 hover:to-emerald-500 shadow-lg shadow-teal-500/20 hover:shadow-teal-500/30 hover:scale-[1.02] transition-all duration-200">
                        Dashboard Pegawai
                    </a>
                @elseif(auth()->check() && auth()->user()->role === 'admin')
                    <a href="/admin" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 text-sm font-semibold rounded-2xl text-white bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-400 hover:to-indigo-500 shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30 hover:scale-[1.02] transition-all duration-200">
                        Dashboard Admin
                    </a>
                @endif

                @if(auth()->check())
                    <form id="logout-form" action="{{ auth()->user()->role === 'admin' ? route('filament.admin.auth.logout') : route('filament.pegawai.auth.logout') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                    <button onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 border border-slate-800 text-sm font-semibold rounded-2xl text-slate-300 hover:bg-slate-800 hover:text-white hover:scale-[1.02] transition-all duration-200">
                        Logout / Keluar
                    </button>
                @else
                    <a href="/" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 border border-slate-800 text-sm font-semibold rounded-2xl text-slate-300 hover:bg-slate-800 hover:text-white transition-all duration-200">
                        Kembali Ke Beranda
                    </a>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
