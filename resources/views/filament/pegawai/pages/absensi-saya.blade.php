<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Card Status Absensi --}}
        <x-filament::section>
            <x-slot name="heading">
                Status Hari Ini: <span class="text-primary-600">{{ \Carbon\Carbon::now()->format('l, d F Y') }}</span>
            </x-slot>

            <div class="flex flex-col items-center justify-center py-4 space-y-4">
                <div class="text-center">
                    <p class="text-gray-500 text-sm">Jam Sekarang</p>
                    <p class="text-4xl font-bold font-mono" id="clock" wire:ignore>{{ \Carbon\Carbon::now()->format('H:i:s') }}</p>
                </div>
                
                <div class="w-full flex justify-between px-8 py-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-center">
                        <p class="text-sm text-gray-500">Masuk</p>
                        <p class="text-xl font-bold text-success-600">{{ $absensiHariIni?->jam_masuk ?? '--:--' }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-500">Pulang</p>
                        <p class="text-xl font-bold text-danger-600">{{ $absensiHariIni?->jam_pulang ?? '--:--' }}</p>
                    </div>
                </div>

                @if($statusAbsenSekarang == 'Belum Absen')
                    <x-filament::button size="lg" color="success" class="w-full" wire:click="absenMasuk" id="btn-masuk">
                        Absen Masuk Sekarang
                    </x-filament::button>
                @elseif($statusAbsenSekarang == 'Sudah Masuk')
                    @if($confirmPulangCepat)
                        <div class="w-full p-4 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 rounded-lg text-amber-800 dark:text-amber-300 text-sm space-y-3">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 mr-2 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                <div>
                                    <p class="font-semibold text-left">Peringatan Pulang Cepat</p>
                                    <p class="mt-1 text-left">{{ $pesanPeringatanPulang }}</p>
                                </div>
                            </div>
                            <div class="flex gap-2 justify-end">
                                <x-filament::button size="xs" color="gray" wire:click="$set('confirmPulangCepat', false)">
                                    Batal
                                </x-filament::button>
                                <x-filament::button size="xs" color="warning" wire:click="absenPulang(true)">
                                    Ya, Tetap Pulang
                                </x-filament::button>
                            </div>
                        </div>
                    @else
                        <x-filament::button size="lg" color="danger" class="w-full" wire:click="absenPulang" id="btn-pulang">
                            Absen Pulang Sekarang
                        </x-filament::button>
                    @endif
                @else
                    <div class="w-full text-center p-3 bg-success-100 text-success-700 rounded-lg font-medium">
                        Anda sudah menyelesaikan absensi hari ini. Terima kasih!
                    </div>
                @endif
                
                @if($statusAbsenSekarang == 'Belum Absen')
                    <textarea wire:model="keterangan" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm" placeholder="Keterangan (Opsional)..." rows="2"></textarea>
                @endif
            </div>
            
            <div class="mt-4 text-xs text-gray-500 text-center" wire:ignore>
                Lokasi: <span id="location-status">Mencari lokasi GPS...</span>
            </div>
        </x-filament::section>

        {{-- Card QR Code --}}
        <x-filament::section>
            <x-slot name="heading">
                Identitas QR Code
            </x-slot>

            <div class="flex flex-col items-center justify-center py-4 space-y-4">
                <p class="text-sm text-gray-500 text-center">Tunjukkan QR Code ini pada scanner untuk absen via perangkat lain.</p>
                <div class="p-2 bg-white rounded-xl shadow-sm">
                    <img src="{{ (new \chillerlan\QRCode\QRCode)->render(auth()->user()->qrUrl) }}" alt="QR Code Absensi" class="w-48 h-48">
                </div>
                <p class="font-mono text-sm text-gray-600">{{ auth()->user()->nip }}</p>
                <p class="font-bold">{{ auth()->user()->name }}</p>
            </div>
        </x-filament::section>
    </div>

    @script
    <script>
        // Clock
        setInterval(function() {
            const now = new Date();
            const timeString = now.getHours().toString().padStart(2, '0') + ':' + 
                             now.getMinutes().toString().padStart(2, '0') + ':' + 
                             now.getSeconds().toString().padStart(2, '0');
            document.getElementById('clock').innerText = timeString;
        }, 1000);

        // GPS Geolocation
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    @this.set('latitude', position.coords.latitude);
                    @this.set('longitude', position.coords.longitude);
                    document.getElementById('location-status').innerText = position.coords.latitude + ', ' + position.coords.longitude;
                    document.getElementById('location-status').classList.add('text-success-600');
                },
                (error) => {
                    document.getElementById('location-status').innerText = 'Gagal mendapatkan lokasi: ' + error.message;
                    document.getElementById('location-status').classList.add('text-danger-600');
                },
                { enableHighAccuracy: true }
            );
        } else {
            document.getElementById('location-status').innerText = 'Browser tidak mendukung GPS.';
        }
    </script>
    @endscript
</x-filament-panels::page>
