<x-filament-panels::page>

{{-- ===== GRID UTAMA — menggunakan CSS class murni, bukan Tailwind, agar tidak konflik Filament v5 ===== --}}
<div class="absensi-grid">

    {{-- ============= CARD KIRI: STATUS ABSENSI ============= --}}
    <div class="absensi-card">

        {{-- Header --}}
        <div class="absensi-card-header">
            <svg style="width:17px;height:17px;min-width:17px;color:#0ea5e9;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span style="font-size:0.8rem;color:#64748b;font-weight:500;">Status Hari Ini:</span>
            <span style="font-size:0.8rem;font-weight:700;color:#0284c7;">
                {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
            </span>
        </div>

        {{-- Body --}}
        <div class="absensi-card-body">

            {{-- Jam Digital --}}
            <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                <span style="font-size:0.6rem;font-weight:700;text-transform:uppercase;letter-spacing:0.12em;color:#94a3b8;">
                    Waktu Saat Ini
                </span>
                <div class="clock-box">
                    <span id="clock" wire:ignore>{{ \Carbon\Carbon::now()->format('H:i:s') }}</span>
                </div>
            </div>

            {{-- Panel Jam Masuk & Pulang --}}
            <div class="jam-grid">
                <div class="jam-cell">
                    <p class="jam-label">Jam Masuk</p>
                    <p class="jam-value" style="color:#10b981;">
                        {{ $absensiHariIni?->jam_masuk ?? '--:--' }}
                    </p>
                </div>
                <div class="jam-cell">
                    <p class="jam-label">Jam Pulang</p>
                    <p class="jam-value" style="color:#f43f5e;">
                        {{ $absensiHariIni?->jam_pulang ?? '--:--' }}
                    </p>
                </div>
            </div>

            {{-- Tombol Aksi --}}
            @if($statusAbsenSekarang == 'Belum Absen')
                <div style="display:flex;flex-direction:column;gap:10px;">
                    <textarea
                        wire:model="keterangan"
                        rows="2"
                        placeholder="Tambahkan catatan keterangan (opsional)..."
                        class="absensi-textarea"
                    ></textarea>
                    <button wire:click="absenMasuk" id="btn-masuk" class="btn-absen btn-masuk">
                        <svg style="width:17px;height:17px;min-width:17px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        ABSEN MASUK SEKARANG
                    </button>
                </div>

            @elseif($statusAbsenSekarang == 'Sudah Masuk')
                @if($confirmPulangCepat)
                    <div class="warn-box">
                        <div style="display:flex;align-items:flex-start;gap:10px;">
                            <svg style="width:18px;height:18px;min-width:18px;color:#f59e0b;margin-top:1px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div>
                                <p style="font-weight:700;font-size:0.875rem;color:#92400e;margin-bottom:4px;">Peringatan Pulang Cepat</p>
                                <p style="font-size:0.75rem;color:#b45309;line-height:1.5;">{{ $pesanPeringatanPulang }}</p>
                            </div>
                        </div>
                        <div class="warn-btn-row">
                            <x-filament::button size="sm" color="gray" wire:click="$set('confirmPulangCepat', false)">
                                Batal
                            </x-filament::button>
                            <x-filament::button size="sm" color="warning" wire:click="absenPulang(true)">
                                Ya, Tetap Pulang
                            </x-filament::button>
                        </div>
                    </div>
                @else
                    <button wire:click="absenPulang" id="btn-pulang" class="btn-absen btn-pulang">
                        <svg style="width:17px;height:17px;min-width:17px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        ABSEN PULANG SEKARANG
                    </button>
                @endif

            @else
                <div class="selesai-badge">
                    <svg style="width:18px;height:18px;min-width:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Absensi hari ini telah selesai. Terima kasih!
                </div>
            @endif

            {{-- GPS --}}
            <div class="gps-bar" wire:ignore>
                <span class="gps-dot"></span>
                <span>Lokasi GPS:</span>
                <span id="location-status" style="font-family:ui-monospace,monospace;font-size:0.68rem;font-weight:500;color:#64748b;">
                    Mencari lokasi GPS...
                </span>
            </div>

        </div>
    </div>

    {{-- ============= CARD KANAN: KNMP DIGITAL ID ============= --}}
    <div class="knmp-card">

        {{-- Header --}}
        <div class="knmp-header">
            <div class="knmp-brand">
                <div class="knmp-logo-circle">K</div>
                <span class="knmp-brand-name">KNMP DIGITAL ID</span>
            </div>
            <span class="knmp-badge-aktif">AKTIF</span>
        </div>

        {{-- QR + NIP --}}
        <div class="knmp-body">
            <div class="knmp-qr-wrapper">
                <img
                    src="{{ (new \chillerlan\QRCode\QRCode)->render(auth()->user()->qrUrl) }}"
                    alt="QR Code Absensi {{ auth()->user()->name }}"
                    style="width:140px;height:140px;"
                >
            </div>
            <div>
                <p class="knmp-nip-label">Nomor Induk Pegawai</p>
                <p class="knmp-nip-value">{{ auth()->user()->nip }}</p>
            </div>
        </div>

        {{-- Footer --}}
        <div class="knmp-footer">
            <div>
                <p class="knmp-field-label">Nama Pegawai</p>
                <p class="knmp-field-name">{{ auth()->user()->name }}</p>
            </div>
            <div>
                <p class="knmp-field-label" style="text-align:right;">Jabatan / Akses</p>
                <p class="knmp-field-role">{{ auth()->user()->role ?? 'Pegawai' }}</p>
            </div>
        </div>

    </div>

</div>

@script
<script>
    // ===== Live Clock =====
    function updateClock() {
        const now = new Date();
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        const s = String(now.getSeconds()).padStart(2, '0');
        const el = document.getElementById('clock');
        if (el) el.innerText = `${h}:${m}:${s}`;
    }
    updateClock();
    setInterval(updateClock, 1000);

    // ===== GPS Geolocation =====
    const locationEl = document.getElementById('location-status');

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const lat = pos.coords.latitude.toFixed(6);
                const lng = pos.coords.longitude.toFixed(6);
                @this.set('latitude', pos.coords.latitude);
                @this.set('longitude', pos.coords.longitude);
                if (locationEl) {
                    locationEl.innerText = `${lat}, ${lng}`;
                    locationEl.style.color = '#34d399';
                }
            },
            (err) => {
                if (locationEl) {
                    locationEl.innerText = 'Gagal mendapatkan lokasi';
                    locationEl.style.color = '#f43f5e';
                }
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    } else {
        if (locationEl) locationEl.innerText = 'Browser tidak mendukung GPS.';
    }
</script>
@endscript

</x-filament-panels::page>