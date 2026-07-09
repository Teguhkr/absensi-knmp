<x-filament-panels::page>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<div class="absensi-grid">

    {{-- ============= CARD KIRI: STATUS PRESENSI ============= --}}
    <div class="absensi-card">

        <div class="absensi-card-header">
            <svg style="width:17px;height:17px;min-width:17px;color:#0ea5e9;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span style="font-size:0.8rem;color:#64748b;font-weight:500;">Status Hari Ini:</span>
            <span style="font-size:0.8rem;font-weight:700;color:#0284c7;">
                {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
            </span>
        </div>

        <div class="absensi-card-body">

            {{-- ===== BANNER PENUGASAN AKTIF ===== --}}
            @if($penugasanAktif)
                <div style="
                    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
                    border: 1.5px solid #fcd34d;
                    border-radius: 12px;
                    padding: 12px 14px;
                    margin-bottom: 12px;
                ">
                    <div style="display:flex;align-items:flex-start;gap:10px;">
                        <svg style="width:20px;height:20px;min-width:20px;color:#d97706;margin-top:1px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <div style="flex:1;">
                            <p style="font-weight:700;font-size:0.82rem;color:#92400e;margin-bottom:3px;">
                                🚀 Sedang dalam Penugasan
                            </p>
                            <p style="font-size:0.72rem;color:#b45309;line-height:1.6;margin-bottom:4px;">
                                @if($penugasanAktif->nomor_spt)
                                    <strong>No. SPT: {{ $penugasanAktif->nomor_spt }}</strong> ·
                                @endif
                                {{ \Carbon\Carbon::parse($penugasanAktif->tanggal_mulai)->format('d/m/Y') }} –
                                {{ \Carbon\Carbon::parse($penugasanAktif->tanggal_selesai)->format('d/m/Y') }}
                            </p>

                            {{-- Lokasi Penugasan Aktif --}}
                            @if($penugasanAktif->lokasi_aktif_lat && $penugasanAktif->lokasi_aktif_lng)
                                <div style="
                                    background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;
                                    padding:8px 10px;margin-top:6px;font-size:0.7rem;
                                ">
                                    <p style="font-weight:600;color:#9a3412;margin-bottom:3px;">
                                        📍 Lokasi Penugasan Aktif:
                                        @if($penugasanAktif->req_lokasi_status === 'approved')
                                            <span style="font-size:0.65rem;background:#dcfce7;color:#166534;padding:1px 6px;border-radius:99px;margin-left:4px;">Lokasi Diperbarui</span>
                                        @endif
                                    </p>
                                    <p style="font-family:monospace;color:#7c3aed;font-size:0.72rem;">
                                        {{ number_format($penugasanAktif->lokasi_aktif_lat, 6) }},
                                        {{ number_format($penugasanAktif->lokasi_aktif_lng, 6) }}
                                    </p>
                                    <p style="color:#6b7280;font-size:0.65rem;margin-top:2px;">
                                        Presensi divalidasi dalam radius kantor dari lokasi ini.
                                    </p>
                                </div>
                            @else
                                <p style="font-size:0.68rem;color:#78350f;margin-top:4px;font-style:italic;">
                                    ⚠️ Lokasi penugasan belum diset. Hubungi admin.
                                </p>
                            @endif

                            {{-- Status Request Perubahan Lokasi --}}
                            @if($penugasanAktif->req_lokasi_status === 'pending')
                                <div style="
                                    margin-top:8px;background:#eff6ff;border:1px solid #bfdbfe;
                                    border-radius:8px;padding:7px 10px;font-size:0.7rem;
                                ">
                                    <p style="font-weight:600;color:#1d4ed8;">⏳ Permohonan Ubah Lokasi Sedang Ditinjau Admin</p>
                                    <p style="color:#3b82f6;font-family:monospace;">
                                        → {{ number_format($penugasanAktif->req_latitude, 6) }},
                                          {{ number_format($penugasanAktif->req_longitude, 6) }}
                                    </p>
                                </div>
                            @elseif($penugasanAktif->req_lokasi_status === 'rejected')
                                <div style="
                                    margin-top:8px;background:#fef2f2;border:1px solid #fecaca;
                                    border-radius:8px;padding:7px 10px;font-size:0.7rem;
                                ">
                                    <p style="font-weight:600;color:#b91c1c;">❌ Permohonan Ubah Lokasi Ditolak</p>
                                    @if($penugasanAktif->req_lokasi_catatan)
                                        <p style="color:#ef4444;">Catatan: {{ $penugasanAktif->req_lokasi_catatan }}</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ===== TOMBOL & FORM UBAH LOKASI (kapan saja selama penugasan aktif) ===== --}}
                @if($penugasanAktif->req_lokasi_status !== 'pending')
                    <div style="margin-bottom:12px;">
                        <button
                            type="button"
                            wire:click="toggleFormUbahLokasi"
                            style="
                                width:100%;display:flex;align-items:center;justify-content:center;gap:8px;
                                padding:9px 14px;font-size:0.78rem;font-weight:600;
                                color:#7c3aed;background:#f5f3ff;border:1.5px solid #ddd6fe;
                                border-radius:10px;cursor:pointer;transition:all 0.2s;
                            "
                        >
                            <svg style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{ $showFormUbahLokasi ? '✕ Tutup Form' : '📍 Ajukan Perubahan Lokasi Penugasan' }}
                        </button>

                        @if($showFormUbahLokasi)
                            <div style="
                                margin-top:10px;background:#faf5ff;border:1.5px solid #e9d5ff;
                                border-radius:12px;padding:14px;
                            ">
                                <p style="font-size:0.75rem;font-weight:700;color:#6d28d9;margin-bottom:10px;">
                                    📝 Ajukan Perubahan Lokasi Penugasan
                                </p>
                                <p style="font-size:0.68rem;color:#7c3aed;margin-bottom:10px;line-height:1.5;">
                                    Perubahan lokasi memerlukan persetujuan admin. Presensi tetap menggunakan lokasi saat ini hingga disetujui.
                                </p>

                                {{-- Input Latitude --}}
                                <div style="margin-bottom:8px;">
                                    <label style="font-size:0.72rem;font-weight:600;color:#4c1d95;display:block;margin-bottom:4px;">
                                        Latitude (Lokasi Baru)
                                    </label>
                                    <div style="display:flex;gap:6px;">
                                        <input
                                            type="number"
                                            step="any"
                                            wire:model="reqLatitude"
                                            placeholder="Contoh: -6.200000"
                                            style="
                                                flex:1;padding:8px 10px;font-size:0.75rem;
                                                border:1.5px solid #c4b5fd;border-radius:8px;
                                                background:#fff;color:#334155;
                                            "
                                        >
                                    </div>
                                </div>

                                {{-- Input Longitude --}}
                                <div style="margin-bottom:8px;">
                                    <label style="font-size:0.72rem;font-weight:600;color:#4c1d95;display:block;margin-bottom:4px;">
                                        Longitude (Lokasi Baru)
                                    </label>
                                    <input
                                        type="number"
                                        step="any"
                                        wire:model="reqLongitude"
                                        placeholder="Contoh: 106.816666"
                                        style="
                                            width:100%;padding:8px 10px;font-size:0.75rem;
                                            border:1.5px solid #c4b5fd;border-radius:8px;
                                            background:#fff;color:#334155;box-sizing:border-box;
                                        "
                                    >
                                </div>

                                {{-- Tombol gunakan GPS --}}
                                <button
                                    type="button"
                                    wire:click="gunakanGpsSaatIni"
                                    style="
                                        font-size:0.7rem;font-weight:600;color:#7c3aed;
                                        background:none;border:none;cursor:pointer;
                                        padding:0;margin-bottom:10px;text-decoration:underline;
                                    "
                                >
                                    🎯 Gunakan koordinat GPS saat ini
                                </button>

                                {{-- Input Alasan --}}
                                <div style="margin-bottom:10px;">
                                    <label style="font-size:0.72rem;font-weight:600;color:#4c1d95;display:block;margin-bottom:4px;">
                                        Alasan Perubahan Lokasi <span style="color:#ef4444;">*</span>
                                    </label>
                                    <textarea
                                        wire:model="reqAlasan"
                                        rows="2"
                                        placeholder="Jelaskan alasan perubahan lokasi penugasan..."
                                        style="
                                            width:100%;padding:8px 10px;font-size:0.75rem;
                                            border:1.5px solid #c4b5fd;border-radius:8px;
                                            background:#fff;color:#334155;box-sizing:border-box;
                                            resize:vertical;font-family:inherit;
                                        "
                                    ></textarea>
                                </div>

                                <button
                                    type="button"
                                    wire:click="ajukanPerubahanLokasi"
                                    style="
                                        width:100%;padding:10px;font-size:0.78rem;font-weight:700;
                                        color:#fff;background:linear-gradient(135deg,#7c3aed,#6d28d9);
                                        border:none;border-radius:10px;cursor:pointer;
                                    "
                                >
                                    Kirim Permohonan ke Admin
                                </button>
                            </div>
                        @endif
                    </div>
                @endif
            @endif

            {{-- Jam Digital --}}
            <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                <span style="font-size:0.6rem;font-weight:700;text-transform:uppercase;letter-spacing:0.12em;color:#94a3b8;">
                    Waktu Saat Ini
                </span>
                <div class="clock-box-futuristic" wire:ignore>
                    <div class="clock-icon-pulse">
                        <span class="pulse-dot-cyan"></span>
                    </div>
                    <div class="clock-time">
                        <span id="clock-hours">00</span>
                        <span class="clock-colon">:</span>
                        <span id="clock-minutes">00</span>
                        <span class="clock-colon-sec">:</span>
                        <span id="clock-seconds" class="seconds-neon">00</span>
                    </div>
                </div>
            </div>

            {{-- Peta Interaktif --}}
            <div id="map-container" style="margin-top:10px;">
                <div id="map" wire:ignore style="height:260px;border-radius:12px;z-index:5;"></div>
            </div>

            {{-- Panel Jam --}}
            <div class="jam-grid">
                <div class="jam-cell">
                    <p class="jam-label">Jam Masuk</p>
                    <p class="jam-value" style="color:#10b981;">{{ $absensiHariIni?->jam_masuk ?? '--:--' }}</p>
                </div>
                <div class="jam-cell">
                    <p class="jam-label">Jam Pulang</p>
                    <p class="jam-value" style="color:#f43f5e;">{{ $absensiHariIni?->jam_pulang ?? '--:--' }}</p>
                </div>
            </div>

            {{-- ===== TOMBOL AKSI ===== --}}
            @if($statusAbsenSekarang === 'Belum Presensi')
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
                        PRESENSI MASUK SEKARANG
                    </button>
                </div>

            @elseif($statusAbsenSekarang === 'Sudah Masuk')
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
                        PRESENSI PULANG SEKARANG
                    </button>
                @endif

            @else
                <div class="selesai-badge">
                    <svg style="width:18px;height:18px;min-width:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Presensi hari ini telah selesai. Terima kasih!
                </div>
            @endif

            {{-- GPS Bar --}}
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
    <div class="knmp-card-holographic">
        <div class="knmp-header">
            <div class="knmp-brand">
                <div class="knmp-logo-circle">K</div>
                <span class="knmp-brand-name">KNMP DIGITAL ID</span>
            </div>
            <span class="knmp-badge-aktif">AKTIF</span>
        </div>
        <div class="knmp-body">
            <div class="knmp-qr-wrapper">
                <img
                    src="{{ (new \chillerlan\QRCode\QRCode)->render(auth()->user()->qrUrl) }}"
                    alt="QR Code Presensi {{ auth()->user()->name }}"
                    style="width:140px;height:140px;"
                >
            </div>
            <div>
                <p class="knmp-nip-label">Nomor Induk Kependudukan (NIK)</p>
                <p class="knmp-nip-value">{{ auth()->user()->nik }}</p>
            </div>
        </div>
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
        const elH = document.getElementById('clock-hours');
        const elM = document.getElementById('clock-minutes');
        const elS = document.getElementById('clock-seconds');
        if (elH) elH.innerText = h;
        if (elM) elM.innerText = m;
        if (elS) elS.innerText = s;
    }
    updateClock();
    setInterval(updateClock, 1000);

    // ===== Map Setup =====
    const kantorLat   = {{ $kantorLatitude ?? 0 }};
    const kantorLng   = {{ $kantorLongitude ?? 0 }};
    const radiusMeter = {{ $radiusAbsensi ?? 500 }};

    // Lokasi penugasan aktif (jika ada)
    const penugasanLat = {{ $penugasanAktif?->lokasi_aktif_lat ?? 'null' }};
    const penugasanLng = {{ $penugasanAktif?->lokasi_aktif_lng ?? 'null' }};
    const adaPenugasan = {{ $penugasanAktif ? 'true' : 'false' }};

    let map, pegawaiMarker, kantorMarker, penugasanMarker, radiusCircle, penugasanCircle;

    function haversine(lat1, lon1, lat2, lon2) {
        const R = 6371e3;
        const φ1 = lat1 * Math.PI/180, φ2 = lat2 * Math.PI/180;
        const Δφ = (lat2 - lat1) * Math.PI/180;
        const Δλ = (lon2 - lon1) * Math.PI/180;
        const a = Math.sin(Δφ/2)**2 + Math.cos(φ1)*Math.cos(φ2)*Math.sin(Δλ/2)**2;
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    }

    function initMap(pegawaiLat, pegawaiLng) {
        const mapEl = document.getElementById('map');
        if (!mapEl) return;

        // Tentukan center: prioritas lokasi penugasan > pegawai > kantor
        const refLat = adaPenugasan && penugasanLat ? penugasanLat : (pegawaiLat || kantorLat || -6.2);
        const refLng = adaPenugasan && penugasanLng ? penugasanLng : (pegawaiLng || kantorLng || 106.8);

        if (!map) {
            map = L.map('map', { zoomControl: false }).setView([refLat, refLng], 15);
            L.control.zoom({ position: 'bottomright' }).addTo(map);
            const isDark = document.documentElement.classList.contains('dark');
            L.tileLayer(isDark
                ? 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png'
                : 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',
                { maxZoom: 19, attribution: '&copy; CARTO' }
            ).addTo(map);
        }

        const kantorIcon = L.divIcon({
            className: 'leaflet-kantor-marker-container',
            html: `<div class="kantor-modern-marker">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:15px;height:15px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12v18H3V3z"/>
                </svg></div>`,
            iconSize: [32, 32], iconAnchor: [16, 16], popupAnchor: [0, -16]
        });

        const pegawaiIcon = L.divIcon({
            className: 'leaflet-pegawai-marker-container',
            html: '<div class="pegawai-pulse-marker"></div>',
            iconSize: [20, 20], iconAnchor: [10, 10], popupAnchor: [0, -10]
        });

        const penugasanIcon = L.divIcon({
            className: '',
            html: `<div style="
                background:#7c3aed;color:#fff;border-radius:50%;width:32px;height:32px;
                display:flex;align-items:center;justify-content:center;font-size:14px;
                border:3px solid #fff;box-shadow:0 2px 8px rgba(124,58,237,0.5);
            ">📍</div>`,
            iconSize: [32, 32], iconAnchor: [16, 16], popupAnchor: [0, -16]
        });

        // Kantor marker (hanya jika tidak dalam penugasan atau koordinat kantor ada)
        if (kantorLat && kantorLng && !adaPenugasan) {
            if (!kantorMarker) {
                kantorMarker = L.marker([kantorLat, kantorLng], { icon: kantorIcon }).addTo(map)
                    .bindPopup('<b>Lokasi Kantor</b><br><span style="font-size:0.75rem;">Kampung Nelayan Merah Putih</span>');
                radiusCircle = L.circle([kantorLat, kantorLng], {
                    color: '#10b981', fillColor: '#10b981', fillOpacity: 0.08,
                    weight: 2, dashArray: '6 6', radius: radiusMeter
                }).addTo(map);
            }
        }

        // Lokasi penugasan marker
        if (adaPenugasan && penugasanLat && penugasanLng) {
            if (!penugasanMarker) {
                penugasanMarker = L.marker([penugasanLat, penugasanLng], { icon: penugasanIcon }).addTo(map)
                    .bindPopup('<b>Lokasi Penugasan</b><br><span style="font-size:0.75rem;color:#7c3aed;">Radius presensi aktif</span>');
            }
            if (!penugasanCircle) {
                penugasanCircle = L.circle([penugasanLat, penugasanLng], {
                    color: '#7c3aed', fillColor: '#7c3aed', fillOpacity: 0.1,
                    weight: 2, dashArray: '6 6', radius: radiusMeter
                }).addTo(map);
            }
        }

        // Pegawai marker
        if (pegawaiLat && pegawaiLng) {
            if (!pegawaiMarker) {
                pegawaiMarker = L.marker([pegawaiLat, pegawaiLng], { icon: pegawaiIcon }).addTo(map)
                    .bindPopup('<b>Lokasi Anda</b>');
            } else {
                pegawaiMarker.setLatLng([pegawaiLat, pegawaiLng]);
            }

            // Fit bounds
            const refMarker = penugasanMarker || kantorMarker;
            const refCircle = penugasanCircle || radiusCircle;
            if (refMarker && refCircle) {
                const group = new L.featureGroup([pegawaiMarker, refCircle]);
                map.fitBounds(group.getBounds().pad(0.15));
            } else {
                map.setView([pegawaiLat, pegawaiLng], 16);
            }
        } else if (adaPenugasan && penugasanLat) {
            map.setView([penugasanLat, penugasanLng], 15);
        }
    }

    // ===== GPS =====
    const locationEl = document.getElementById('location-status');

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                $wire.set('latitude', lat);
                $wire.set('longitude', lng);
                if (locationEl) {
                    locationEl.innerText = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                    locationEl.style.color = '#34d399';
                }
                initMap(lat, lng);
            },
            () => {
                if (locationEl) {
                    locationEl.innerText = 'Gagal mendapatkan lokasi';
                    locationEl.style.color = '#f43f5e';
                }
                initMap(null, null);
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    } else {
        if (locationEl) locationEl.innerText = 'Browser tidak mendukung GPS.';
        initMap(null, null);
    }
</script>
@endscript

</x-filament-panels::page>