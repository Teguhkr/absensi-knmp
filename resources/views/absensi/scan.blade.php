<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Absensi - {{ \App\Models\PengaturanSistem::get('nama_instansi', 'KNMP') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-blue-600 p-6 text-center">
            <h1 class="text-2xl font-bold text-white">Scan Absensi</h1>
            <p class="text-blue-100 mt-1">{{ \App\Models\PengaturanSistem::get('nama_instansi', 'KNMP') }}</p>
        </div>

        <div class="p-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="flex items-center space-x-4 mb-6 p-4 bg-gray-50 rounded-xl">
                <img class="w-16 h-16 rounded-full border-2 border-blue-500 object-cover" 
                     src="{{ $pegawai->foto ? asset('storage/' . $pegawai->foto) : 'https://ui-avatars.com/api/?name=' . urlencode($pegawai->name) . '&background=2563eb&color=fff' }}" 
                     alt="Foto Profil">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">{{ $pegawai->name }}</h2>
                    <p class="text-gray-500 text-sm font-mono">{{ $pegawai->nip }}</p>
                    <span class="inline-block px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full mt-1">{{ $pegawai->departemen ?? 'Pegawai' }}</span>
                </div>
            </div>

            <div class="space-y-4 mb-6">
                <div class="flex justify-between items-center p-3 border rounded-lg {{ $absensiHariIni && $absensiHariIni->jam_masuk ? 'border-green-300 bg-green-50' : 'border-gray-200' }}">
                    <span class="text-gray-600">Jam Masuk</span>
                    <span class="font-bold {{ $absensiHariIni && $absensiHariIni->jam_masuk ? 'text-green-600' : 'text-gray-400' }}">
                        {{ $absensiHariIni->jam_masuk ?? '--:--' }}
                    </span>
                </div>
                <div class="flex justify-between items-center p-3 border rounded-lg {{ $absensiHariIni && $absensiHariIni->jam_pulang ? 'border-red-300 bg-red-50' : 'border-gray-200' }}">
                    <span class="text-gray-600">Jam Pulang</span>
                    <span class="font-bold {{ $absensiHariIni && $absensiHariIni->jam_pulang ? 'text-red-600' : 'text-gray-400' }}">
                        {{ $absensiHariIni->jam_pulang ?? '--:--' }}
                    </span>
                </div>
            </div>

            <form action="{{ route('absensi.scan.process', $pegawai->qr_token) }}" method="POST" class="space-y-3">
                @csrf
                @if(!$absensiHariIni || !$absensiHariIni->jam_masuk)
                    <button type="submit" name="absen_masuk" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-xl transition duration-200 shadow-md flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                        Proses Absen Masuk
                    </button>
                @elseif($absensiHariIni && $absensiHariIni->jam_masuk && !$absensiHariIni->jam_pulang)
                    <button type="submit" name="absen_pulang" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-xl transition duration-200 shadow-md flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Proses Absen Pulang
                    </button>
                @else
                    <div class="text-center p-4 bg-gray-100 text-gray-500 rounded-xl border border-gray-200">
                        Absensi hari ini sudah selesai.
                    </div>
                @endif
            </form>
        </div>
    </div>

</body>
</html>
