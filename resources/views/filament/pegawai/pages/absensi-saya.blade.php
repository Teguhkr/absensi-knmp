<x-filament-panels::page>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

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

            {{-- Peta Interaktif Geolocation --}}
            <div id="map-container" style="margin-top: 10px;">
                <div id="map" wire:ignore style="height: 260px; border-radius: 12px; z-index: 5;"></div>
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
    <div class="knmp-card-holographic">

        {{-- Header --}}
        <div class="knmp-header">
            <div class="knmp-brand">
                <div class="knmp-logo-circle">K</div>
                <span class="knmp-brand-name">KNMP DIGITAL ID</span>
            </div>
            <span class="knmp-badge-aktif">AKTIF</span>
        </div>

        {{-- QR + NIK --}}
        <div class="knmp-body">
            <div class="knmp-qr-wrapper">
                <img
                    src="{{ (new \chillerlan\QRCode\QRCode)->render(auth()->user()->qrUrl) }}"
                    alt="QR Code Absensi {{ auth()->user()->name }}"
                    style="width:140px;height:140px;"
                >
            </div>
            <div>
                <p class="knmp-nip-label">Nomor Induk Kependudukan (NIK)</p>
                <p class="knmp-nip-value">{{ auth()->user()->nik }}</p>
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
        
        const elHours = document.getElementById('clock-hours');
        const elMinutes = document.getElementById('clock-minutes');
        const elSeconds = document.getElementById('clock-seconds');
        
        if (elHours) elHours.innerText = h;
        if (elMinutes) elMinutes.innerText = m;
        if (elSeconds) elSeconds.innerText = s;
    }
    updateClock();
    setInterval(updateClock, 1000);    // ===== Leaflet Map =====
    const kantorLat = {{ $kantorLatitude ?? 0 }};
    const kantorLng = {{ $kantorLongitude ?? 0 }};
    const radiusMeter = {{ $radiusAbsensi ?? 500 }};
    
    let map;
    let pegawaiMarker;
    let kantorMarker;
    let radiusCircle;

    function isInsideRadius(lat1, lon1, lat2, lon2, radius) {
        if (!lat1 || !lon1 || !lat2 || !lon2) return false;
        const R = 6371e3; // metres
        const φ1 = lat1 * Math.PI/180;
        const φ2 = lat2 * Math.PI/180;
        const Δφ = (lat2-lat1) * Math.PI/180;
        const Δλ = (lon2-lon1) * Math.PI/180;

        const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                  Math.cos(φ1) * Math.cos(φ2) *
                  Math.sin(Δλ/2) * Math.sin(Δλ/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

        const d = R * c; // in metres
        return d <= radius;
    }

    function initMap(pegawaiLat, pegawaiLng) {
        const mapEl = document.getElementById('map');
        if (!mapEl) return;

        if (!map) {
            const centerLat = pegawaiLat || kantorLat || -6.200000;
            const centerLng = pegawaiLng || kantorLng || 106.816666;
            
            map = L.map('map', {
                zoomControl: false
            }).setView([centerLat, centerLng], 15);

            L.control.zoom({
                position: 'bottomright'
            }).addTo(map);
            
            // Dynamic Tile Layer for Light/Dark Mode
            const isDark = document.documentElement.classList.contains('dark');
            const tileUrl = isDark 
                ? 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png' 
                : 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';
            
            L.tileLayer(tileUrl, {
                maxZoom: 19,
                attribution: '&copy; <a href="https://carto.com/attributions">CARTO</a>'
            }).addTo(map);
        }
        
        // Custom Modern Icons
        const kantorIcon = L.divIcon({
            className: 'leaflet-kantor-marker-container',
            html: `<div class="kantor-modern-marker">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 15px; height: 15px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12v18H3V3z" />
                </svg>
            </div>`,
            iconSize: [32, 32],
            iconAnchor: [16, 16],
            popupAnchor: [0, -16]
        });

        const pegawaiIcon = L.divIcon({
            className: 'leaflet-pegawai-marker-container',
            html: '<div class="pegawai-pulse-marker"></div>',
            iconSize: [20, 20],
            iconAnchor: [10, 10],
            popupAnchor: [0, -10]
        });
        
        // Setup Kantor Marker & Radius Circle
        if (kantorLat && kantorLng) {
            if (!kantorMarker) {
                kantorMarker = L.marker([kantorLat, kantorLng], { icon: kantorIcon }).addTo(map)
                    .bindPopup('<b>Lokasi Kantor</b><br><span style="color:#64748b;font-size:0.75rem;">Kampung Nelayan Merah Putih</span>');
            }
            
            const inside = isInsideRadius(pegawaiLat, pegawaiLng, kantorLat, kantorLng, radiusMeter);
            const circleColor = inside ? '#10b981' : '#ef4444';
            
            if (!radiusCircle) {
                radiusCircle = L.circle([kantorLat, kantorLng], {
                    color: circleColor,
                    fillColor: circleColor,
                    fillOpacity: 0.08,
                    weight: 2,
                    dashArray: '6, 6',
                    radius: radiusMeter
                }).addTo(map);
            } else {
                radiusCircle.setStyle({
                    color: circleColor,
                    fillColor: circleColor
                });
            }
        }
        
        // Setup Pegawai Marker
        if (pegawaiLat && pegawaiLng) {
            if (!pegawaiMarker) {
                pegawaiMarker = L.marker([pegawaiLat, pegawaiLng], { icon: pegawaiIcon }).addTo(map)
                    .bindPopup('<b>Lokasi Anda</b>');
            } else {
                pegawaiMarker.setLatLng([pegawaiLat, pegawaiLng]);
            }
            
            // Adjust bounds to show both if office coordinates are valid
            if (kantorLat && kantorLng) {
                const group = new L.featureGroup([pegawaiMarker, radiusCircle]);
                map.fitBounds(group.getBounds().pad(0.15));
            } else {
                map.setView([pegawaiLat, pegawaiLng], 16);
            }
        }
    }

    // ===== GPS Geolocation =====
    const locationEl = document.getElementById('location-status');

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                const latFixed = lat.toFixed(6);
                const lngFixed = lng.toFixed(6);
                $wire.set('latitude', lat);
                $wire.set('longitude', lng);
                if (locationEl) {
                    locationEl.innerText = `${latFixed}, ${lngFixed}`;
                    locationEl.style.color = '#34d399';
                }
                
                // Initialize map with current user coordinates
                initMap(lat, lng);
            },
            (err) => {
                if (locationEl) {
                    locationEl.innerText = 'Gagal mendapatkan lokasi';
                    locationEl.style.color = '#f43f5e';
                }
                // Initialize map centered on office if GPS fails
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