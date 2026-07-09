<x-filament-widgets::widget>
    <div class="dashboard-banner">
        <div class="banner-body">
            <div class="banner-greeting">
                <h1>Selamat Datang Kembali, {{ auth()->user()->name }}!</h1>
                <p>Kelola data kehadiran, perizinan, dan konfigurasi sistem KNMP secara real-time.</p>

                {{-- Quote Lucu untuk Admin --}}
                @php
                    $adminQuotes = [
                        "Menjadi admin itu berat, harus pura-pura sabar lihat pegawai yang izinnya mepet-mepet. 😌",
                        "Mengawasi koordinat presensi: memastikan pegawai benar-benar bekerja, bukan malah melipir ke warung kopi. ☕",
                        "Admin KNMP: penjaga gerbang kedisiplinan dan pengatur ritme sistem. Semangat mengelola data hari ini! 👮‍♂️",
                        "Peta kehadiran terpantau aman. Jika ada titik koordinat yang melayang di laut, itu mungkin pegawai yang rajin sedang snorkeling. 🐠",
                        "Data kehadiran hari ini: ada yang rajin, ada yang mepet batas toleransi, ada juga yang... ah sudahlah. 🤫",
                        "Tugas admin hari ini: setujui cuti pegawai dengan senyuman, tolak dengan catatan yang bijaksana. 💼",
                        "Jangan lupa bahagia hari ini, meskipun tugas Anda adalah meninjau laporan harian yang kadang keterangannya cuma satu kalimat. 📝",
                        "Pikiran pegawai saat jam 16:59: 'Tinggal 1 menit untuk absen pulang.' Pikiran admin: 'Tinggal beberapa menit untuk memantau siapa yang pulang cepat.' 👀"
                    ];
                    $randomAdminQuote = $adminQuotes[array_rand($adminQuotes)];
                @endphp
                <div class="admin-quote-box" style="
                    margin-top: 12px;
                    padding: 8px 14px;
                    background: rgba(255, 255, 255, 0.12);
                    border-left: 3px solid #ffba08;
                    border-radius: 4px 8px 8px 4px;
                    font-size: 0.85rem;
                    color: rgba(255, 255, 255, 0.95);
                    max-width: 90%;
                    font-style: italic;
                    backdrop-filter: blur(4px);
                    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
                ">
                    "{{ $randomAdminQuote }}"
                </div>
            </div>
            <div class="banner-clock-section">
                <div class="banner-date">
                    <svg class="banner-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
                </div>
                <div class="banner-clock-box" wire:ignore>
                    <span id="dashboard-clock">{{ \Carbon\Carbon::now()->format('H:i:s') }}</span>
                </div>
            </div>
        </div>
    </div>

    @script
    <script>
        function updateDashboardClock() {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            const el = document.getElementById('dashboard-clock');
            if (el) el.innerText = `${h}:${m}:${s}`;
        }
        updateDashboardClock();
        setInterval(updateDashboardClock, 1000);
    </script>
    @endscript
</x-filament-widgets::widget>
