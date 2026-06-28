<?php

namespace App\Filament\Pegawai\Pages;

use App\Models\Absensi;
use App\Models\Izin;
use App\Models\PengaturanSistem;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class AbsensiSaya extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-finger-print';
    protected static ?string $navigationLabel = 'Presensi Mandiri';
    protected static ?string $title = 'Presensi Mandiri';
    protected string $view = 'filament.pegawai.pages.absensi-saya';
    protected static ?int $navigationSort = 1;

    public $latitude;
    public $longitude;
    public $keterangan;
    public $statusAbsenSekarang = 'Belum Presensi';
    public $absensiHariIni;
    public $confirmPulangCepat = false;
    public $pesanPeringatanPulang = '';
    public $kantorLatitude;
    public $kantorLongitude;
    public $radiusAbsensi;

    // Penugasan state
    public $penugasanAktif = null;

    // Form perubahan lokasi
    public $showFormUbahLokasi = false;
    public $reqLatitude;
    public $reqLongitude;
    public $reqAlasan = '';

    public function mount()
    {
        $this->loadAbsensiHariIni();
        $this->kantorLatitude  = (float) PengaturanSistem::get('kantor_latitude', 0);
        $this->kantorLongitude = (float) PengaturanSistem::get('kantor_longitude', 0);
        $this->radiusAbsensi   = (float) PengaturanSistem::get('radius_absensi', 500);
        $this->checkPenugasanAktif();
    }

    /**
     * Cek apakah pegawai sedang dalam penugasan (Izin Dinas) yang aktif hari ini
     */
    public function checkPenugasanAktif(): void
    {
        $today = Carbon::today()->toDateString();
        $this->penugasanAktif = Izin::where('user_id', Auth::id())
            ->where('jenis', 'dinas')
            ->where('status', 'approved')
            ->where('tanggal_mulai', '<=', $today)
            ->where('tanggal_selesai', '>=', $today)
            ->first();
    }

    public function loadAbsensiHariIni(): void
    {
        $this->absensiHariIni = Absensi::getAbsensiHariIni(Auth::id());

        if ($this->absensiHariIni) {
            if ($this->absensiHariIni->jam_pulang) {
                $this->statusAbsenSekarang = 'Sudah Pulang';
            } elseif ($this->absensiHariIni->jam_masuk) {
                $this->statusAbsenSekarang = 'Sudah Masuk';
            } else {
                // Record ada (pre-created untuk penugasan/izin) tapi belum jam_masuk
                $this->statusAbsenSekarang = 'Belum Presensi';
            }
        } else {
            $this->statusAbsenSekarang = 'Belum Presensi';
        }
    }

    public function absenMasuk(): void
    {
        if ($this->penugasanAktif) {
            // Blokir presensi jika ada request perubahan lokasi yang masih pending
            if ($this->penugasanAktif->req_lokasi_status === 'pending') {
                Notification::make()->warning()
                    ->title('Permohonan Perubahan Lokasi Sedang Ditinjau')
                    ->body('Anda tidak dapat melakukan presensi sementara permohonan perubahan lokasi menunggu persetujuan admin.')
                    ->send();
                return;
            }

            // GPS wajib ada
            if (!$this->latitude || !$this->longitude) {
                Notification::make()->danger()->title('Lokasi GPS tidak ditemukan! Pastikan Anda mengizinkan akses lokasi.')->send();
                return;
            }

            // Jika lokasi penugasan sudah diset, validasi jarak ke lokasi tersebut
            $lokasiLat = $this->penugasanAktif->lokasi_aktif_lat;
            $lokasiLng = $this->penugasanAktif->lokasi_aktif_lng;
            if ($lokasiLat && $lokasiLng) {
                $jarak           = PengaturanSistem::hitungJarak(
                    (float) $this->latitude, (float) $this->longitude,
                    $lokasiLat, $lokasiLng
                );
                $radiusPenugasan = (float) PengaturanSistem::get('radius_absensi', 500);
                if ($jarak > $radiusPenugasan) {
                    Notification::make()->danger()
                        ->title('Anda berada di luar radius lokasi penugasan! (' . round($jarak) . 'm dari lokasi penugasan)')
                        ->send();
                    return;
                }
            } else {
                // Lokasi penugasan belum diset oleh admin → fallback ke validasi kantor
                if (!$this->validateGps()) {
                    return;
                }
            }
        } else {
            if (!$this->validateGps()) {
                return;
            }
        }

        if ($this->absensiHariIni && $this->absensiHariIni->jam_masuk) {
            Notification::make()->warning()->title('Anda sudah melakukan presensi masuk hari ini.')->send();
            return;
        }

        $now             = Carbon::now();
        $jamMasukStandar = Carbon::createFromTimeString(PengaturanSistem::get('jam_masuk', '08:00'));
        $toleransi       = (int) PengaturanSistem::get('toleransi_menit', 15);
        $batasTerlambat  = $jamMasukStandar->copy()->addMinutes($toleransi);

        // Jika penugasan, status selalu 'dinas'
        $status = $this->penugasanAktif
            ? 'dinas'
            : ($now->greaterThan($batasTerlambat) ? 'terlambat' : 'hadir');

        $keteranganFinal = $this->keterangan;
        if ($this->penugasanAktif) {
            $infoSpt         = $this->penugasanAktif->nomor_spt ? ' (No. SPT: ' . $this->penugasanAktif->nomor_spt . ')' : '';
            $catatanPenugasan = 'Penugasan' . $infoSpt;
            $keteranganFinal  = $keteranganFinal ? $catatanPenugasan . '; ' . $keteranganFinal : $catatanPenugasan;
        }

        Absensi::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'tanggal' => $now->toDateString(),
            ],
            [
                'jam_masuk'       => $now->toTimeString(),
                'latitude_masuk'  => $this->latitude,
                'longitude_masuk' => $this->longitude,
                'status'          => $status,
                'keterangan'      => $keteranganFinal,
            ]
        );

        Notification::make()->success()
            ->title('Presensi Masuk Berhasil!' . ($this->penugasanAktif ? ' (Status: Penugasan)' : ''))
            ->send();
        $this->loadAbsensiHariIni();
        $this->keterangan = '';
    }

    public function absenPulang($force = false): void
    {
        // Untuk penugasan: gunakan GPS saat ini, validasi ke lokasi penugasan aktif
        if ($this->penugasanAktif) {
            // Blokir presensi jika ada request perubahan lokasi yang masih pending
            if ($this->penugasanAktif->req_lokasi_status === 'pending') {
                Notification::make()->warning()
                    ->title('Permohonan Perubahan Lokasi Sedang Ditinjau')
                    ->body('Anda tidak dapat melakukan presensi pulang sementara permohonan perubahan lokasi menunggu persetujuan admin.')
                    ->send();
                return;
            }

            if (!$this->latitude || !$this->longitude) {
                Notification::make()->danger()->title('Lokasi GPS tidak ditemukan. Harap aktifkan GPS.')->send();
                return;
            }

            // Validasi jarak ke lokasi penugasan aktif
            $lokasiLat = $this->penugasanAktif->lokasi_aktif_lat;
            $lokasiLng = $this->penugasanAktif->lokasi_aktif_lng;
            if ($lokasiLat && $lokasiLng) {
                $jarak           = PengaturanSistem::hitungJarak(
                    (float) $this->latitude, (float) $this->longitude,
                    $lokasiLat, $lokasiLng
                );
                $radiusPenugasan = (float) PengaturanSistem::get('radius_absensi', 500);
                if ($jarak > $radiusPenugasan) {
                    Notification::make()->danger()
                        ->title('Di luar radius lokasi penugasan! (' . round($jarak) . 'm). Jika lokasi berubah, ajukan perubahan lokasi dan tunggu persetujuan admin.')
                        ->send();
                    return;
                }
            } else {
                // Lokasi penugasan belum diset → fallback ke validasi kantor
                if (!$this->validateGps()) {
                    return;
                }
            }

            $latPulang = $this->latitude;
            $lngPulang = $this->longitude;
        } else {
            if (!$this->validateGps()) {
                return;
            }
            $latPulang = $this->latitude;
            $lngPulang = $this->longitude;
        }

        if (!$this->absensiHariIni || !$this->absensiHariIni->jam_masuk) {
            Notification::make()->warning()->title('Anda belum melakukan presensi masuk.')->send();
            return;
        }

        if ($this->absensiHariIni->jam_pulang) {
            Notification::make()->warning()->title('Anda sudah melakukan presensi pulang.')->send();
            return;
        }

        $now              = Carbon::now();
        $jamMasuk         = Carbon::createFromTimeString($this->absensiHariIni->jam_masuk);
        $jamMasukStandar  = Carbon::createFromTimeString(PengaturanSistem::get('jam_masuk', '08:00'));
        $jamPulangStandar = Carbon::createFromTimeString(PengaturanSistem::get('jam_pulang', '16:00'));

        $durasiStandarMenit = $jamMasukStandar->diffInMinutes($jamPulangStandar);
        $durasiAktualMenit  = $jamMasuk->diffInMinutes($now);
        $isPulangCepat      = $now->lessThan($jamPulangStandar) || ($durasiAktualMenit < $durasiStandarMenit);

        if ($isPulangCepat && !$force) {
            $kurangMenit          = $durasiStandarMenit - $durasiAktualMenit;
            $jamKurang            = floor($kurangMenit / 60);
            $menitKurang          = $kurangMenit % 60;
            $durasiAktualJam      = floor($durasiAktualMenit / 60);
            $durasiAktualSisaMenit = $durasiAktualMenit % 60;

            $this->pesanPeringatanPulang = "Jam pulang standar adalah pukul " . $jamPulangStandar->format('H:i') .
                " (durasi minimal " . ($durasiStandarMenit / 60) . " jam). Durasi kerja Anda baru {$durasiAktualJam} jam {$durasiAktualSisaMenit} menit " .
                "(kurang {$jamKurang} jam {$menitKurang} menit). Apakah Anda yakin ingin pulang cepat?";
            $this->confirmPulangCepat = true;
            return;
        }

        $keteranganUpdate = $this->absensiHariIni->keterangan;
        if ($isPulangCepat) {
            $durasiAktualJam       = floor($durasiAktualMenit / 60);
            $durasiAktualSisaMenit = $durasiAktualMenit % 60;
            $catatanPulangCepat    = "Pulang Cepat (Durasi Kerja: {$durasiAktualJam} jam {$durasiAktualSisaMenit} menit)";
            $keteranganUpdate      = $keteranganUpdate ? $keteranganUpdate . '; ' . $catatanPulangCepat : $catatanPulangCepat;
        }

        $this->absensiHariIni->update([
            'jam_pulang'       => $now->toTimeString(),
            'latitude_pulang'  => $latPulang,
            'longitude_pulang' => $lngPulang,
            'keterangan'       => $keteranganUpdate,
        ]);

        Notification::make()->success()
            ->title('Presensi Pulang Berhasil!' . ($isPulangCepat ? ' (Dicatat Pulang Cepat)' : ''))
            ->send();
        $this->confirmPulangCepat = false;
        $this->loadAbsensiHariIni();
    }

    /**
     * Tampilkan/sembunyikan form perubahan lokasi penugasan
     */
    public function toggleFormUbahLokasi(): void
    {
        $this->showFormUbahLokasi = !$this->showFormUbahLokasi;
        // Pre-fill dengan GPS saat ini
        if ($this->showFormUbahLokasi && $this->latitude && !$this->reqLatitude) {
            $this->reqLatitude  = $this->latitude;
            $this->reqLongitude = $this->longitude;
        }
    }

    /**
     * Isi form perubahan lokasi dari GPS saat ini
     */
    public function gunakanGpsSaatIni(): void
    {
        $this->reqLatitude  = $this->latitude;
        $this->reqLongitude = $this->longitude;
        Notification::make()->info()->title('Koordinat GPS saat ini telah diisi.')->send();
    }

    /**
     * Submit request perubahan lokasi penugasan
     */
    public function ajukanPerubahanLokasi(): void
    {
        if (!$this->penugasanAktif) {
            Notification::make()->warning()->title('Tidak ada penugasan aktif.')->send();
            return;
        }

        if ($this->penugasanAktif->req_lokasi_status === 'pending') {
            Notification::make()->warning()->title('Anda sudah memiliki request perubahan lokasi yang sedang menunggu persetujuan.')->send();
            return;
        }

        if (!$this->reqLatitude || !$this->reqLongitude) {
            Notification::make()->danger()->title('Latitude dan Longitude harus diisi.')->send();
            return;
        }

        if (!$this->reqAlasan) {
            Notification::make()->danger()->title('Alasan perubahan lokasi harus diisi.')->send();
            return;
        }

        // Validasi range koordinat
        if (abs((float)$this->reqLatitude) > 90 || abs((float)$this->reqLongitude) > 180) {
            Notification::make()->danger()->title('Koordinat tidak valid. Latitude: -90 s/d 90, Longitude: -180 s/d 180.')->send();
            return;
        }

        $this->penugasanAktif->update([
            'req_latitude'      => $this->reqLatitude,
            'req_longitude'     => $this->reqLongitude,
            'req_lokasi_status' => 'pending',
            'req_lokasi_alasan' => $this->reqAlasan,
            'req_lokasi_catatan' => null, // reset catatan admin sebelumnya
        ]);

        // Reload agar data ter-refresh
        $this->checkPenugasanAktif();

        $this->showFormUbahLokasi = false;
        $this->reqAlasan          = '';

        Notification::make()->success()
            ->title('Permohonan perubahan lokasi telah dikirim!')
            ->body('Menunggu persetujuan admin. Anda akan mendapat notifikasi setelah diproses.')
            ->send();
    }

    private function validateGps(): bool
    {
        $validasiGps = PengaturanSistem::get('validasi_gps', 1);

        if ($validasiGps && (!$this->latitude || !$this->longitude)) {
            Notification::make()->danger()->title('Lokasi GPS tidak ditemukan! Pastikan Anda mengizinkan akses lokasi.')->send();
            return false;
        }

        if ($validasiGps && !PengaturanSistem::dalamRadius((float) $this->latitude, (float) $this->longitude)) {
            Notification::make()->danger()->title('Anda berada di luar radius kantor yang diizinkan!')->send();
            return false;
        }

        return true;
    }
}
