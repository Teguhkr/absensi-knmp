<x-filament-panels::page>

<style>
    /* ===== Layout Utama ===== */
    .absensi-grid {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        width: 100%;
    }

    /* ===== Card Wrapper ===== */
    .absensi-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .dark .absensi-card {
        background: #1e293b;
        border-color: #334155;
    }
    .absensi-card-header {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 14px 20px;
        border-bottom: 1px solid #f1f5f9;
        background: #fafafa;
    }
    .dark .absensi-card-header {
        border-bottom-color: #334155;
        background: #1a2535;
    }
    .absensi-card-body {
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 16px;
        flex: 1;
    }

    /* ===== Digital Clock ===== */
    .clock-box {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #0f172a;
        border-radius: 12px;
        padding: 10px 28px;
        border: 1px solid rgba(52, 211, 153, 0.25);
    }
    #clock {
        font-family: ui-monospace, 'Cascadia Code', 'Fira Code', monospace;
        font-size: 2.25rem;
        font-weight: 700;
        color: #34d399;
        letter-spacing: 0.08em;
        line-height: 1;
    }

    /* ===== Jam Masuk / Pulang ===== */
    .jam-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
    }
    .dark .jam-grid {
        background: rgba(30, 41, 59, 0.6);
        border-color: rgba(71, 85, 105, 0.4);
    }
    .jam-cell { padding: 14px 8px; text-align: center; }
    .jam-cell:first-child { border-right: 1px solid #e2e8f0; }
    .dark .jam-cell:first-child { border-right-color: rgba(71, 85, 105, 0.4); }
    .jam-label {
        font-size: 0.62rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #94a3b8;
        font-weight: 600;
        margin-bottom: 4px;
    }
    .jam-value {
        font-family: ui-monospace, monospace;
        font-size: 1.5rem;
        font-weight: 700;
    }

    /* ===== Textarea ===== */
    .absensi-textarea {
        width: 100%;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 0.875rem;
        color: #374151;
        background: #ffffff;
        resize: none;
        font-family: inherit;
        transition: border-color 0.15s, box-shadow 0.15s;
        box-sizing: border-box;
    }
    .dark .absensi-textarea { background: #0f172a; border-color: #475569; color: #e2e8f0; }
    .absensi-textarea::placeholder { color: #9ca3af; }
    .absensi-textarea:focus { outline: none; border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,0.12); }

    /* ===== Tombol Absen ===== */
    .btn-absen {
        display: flex; align-items: center; justify-content: center; gap: 10px;
        width: 100%; padding: 13px 20px; border-radius: 12px;
        font-weight: 700; font-size: 0.85rem; letter-spacing: 0.06em;
        border: none; cursor: pointer; transition: all 0.15s ease; color: white; box-sizing: border-box;
    }
    .btn-absen:active { transform: scale(0.98); }
    .btn-masuk { background: linear-gradient(135deg,#10b981,#0d9488); box-shadow: 0 4px 14px rgba(16,185,129,0.3); }
    .btn-masuk:hover { background: linear-gradient(135deg,#059669,#0f766e); box-shadow: 0 6px 18px rgba(16,185,129,0.4); }
    .btn-pulang { background: linear-gradient(135deg,#f43f5e,#e11d48); box-shadow: 0 4px 14px rgba(244,63,94,0.3); }
    .btn-pulang:hover { background: linear-gradient(135deg,#e11d48,#be123c); box-shadow: 0 6px 18px rgba(244,63,94,0.4); }

    /* ===== Warn Box ===== */
    .warn-box { background: rgba(251,191,36,0.08); border: 1px solid rgba(251,191,36,0.35); border-radius: 12px; padding: 16px; }
    .dark .warn-box { background: rgba(120,53,15,0.15); border-color: rgba(180,83,9,0.3); }
    .warn-btn-row { display: flex; gap: 8px; justify-content: flex-end; margin-top: 12px; }

    /* ===== Selesai ===== */
    .selesai-badge {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        width: 100%; padding: 13px 16px; border-radius: 12px;
        background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.25);
        color: #059669; font-weight: 600; font-size: 0.875rem; box-sizing: border-box;
    }
    .dark .selesai-badge { background: rgba(16,185,129,0.1); border-color: rgba(16,185,129,0.2); color: #34d399; }

    /* ===== GPS Bar ===== */
    .gps-bar { display: flex; align-items: center; justify-content: center; gap: 5px; font-size: 0.7rem; color: #94a3b8; padding-top: 4px; }
    .gps-dot { width: 6px; height: 6px; border-radius: 50%; background: #0ea5e9; animation: gps-blink 2s infinite; flex-shrink: 0; }
    @keyframes gps-blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.3; } }

    /* ===== KNMP ID Card ===== */
    .knmp-card {
        background: linear-gradient(145deg,#0f2744 0%,#0c1e3a 55%,#091629 100%);
        border: 1px solid rgba(148,163,184,0.12);
        border-radius: 16px; color: white;
        display: flex; flex-direction: column; justify-content: space-between;
        padding: 22px; min-height: 370px;
    }
    .knmp-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 14px; margin-bottom: 4px; }
    .knmp-brand { display: flex; align-items: center; gap: 10px; }
    .knmp-logo-circle { width: 28px; height: 28px; border-radius: 50%; background: #0ea5e9; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; color: white; flex-shrink: 0; }
    .knmp-brand-name { font-size: 11px; font-weight: 700; letter-spacing: 0.12em; color: rgba(255,255,255,0.85); }
    .knmp-badge-aktif { padding: 3px 10px; background: rgba(16,185,129,0.18); color: #34d399; font-size: 10px; font-weight: 700; border-radius: 99px; border: 1px solid rgba(16,185,129,0.35); letter-spacing: 0.08em; }
    .knmp-body { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 14px; padding: 16px 0; }
    .knmp-qr-wrapper { background: white; border-radius: 14px; padding: 12px; width: 164px; height: 164px; display: flex; align-items: center; justify-content: center; transition: transform 0.3s ease; box-shadow: 0 8px 24px rgba(0,0,0,0.3); }
    .knmp-qr-wrapper:hover { transform: scale(1.04); }
    .knmp-nip-label { font-size: 9px; color: rgba(255,255,255,0.45); text-transform: uppercase; letter-spacing: 0.13em; font-weight: 600; margin-bottom: 3px; text-align: center; }
    .knmp-nip-value { font-family: ui-monospace, monospace; font-size: 13px; font-weight: 700; color: #7dd3fc; letter-spacing: 0.05em; text-align: center; }
    .knmp-footer { border-top: 1px solid rgba(255,255,255,0.1); padding-top: 14px; display: flex; justify-content: space-between; align-items: flex-end; }
    .knmp-field-label { font-size: 9px; color: rgba(255,255,255,0.4); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 3px; }
    .knmp-field-name { font-size: 13px; font-weight: 700; color: rgba(255,255,255,0.92); }
    .knmp-field-role { font-size: 12px; font-weight: 500; color: #7dd3fc; text-align: right; }
</style>

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