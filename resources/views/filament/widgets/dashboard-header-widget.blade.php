<x-filament-widgets::widget>
    <div class="dashboard-banner">
        <div class="banner-body">
            <div class="banner-greeting">
                <h1>Selamat Datang Kembali, {{ auth()->user()->name }}!</h1>
                <p>Kelola data kehadiran, perizinan, dan konfigurasi sistem KNMP secara real-time.</p>
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
