<?php

namespace App\Filament\Pegawai\Pages;

use App\Models\Absensi;
use App\Models\PengaturanSistem;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class AbsensiSaya extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-finger-print';
    protected static ?string $navigationLabel = 'Absensi Mandiri';
    protected static ?string $title = 'Absensi Mandiri';
    protected string $view = 'filament.pegawai.pages.absensi-saya';
    protected static ?int $navigationSort = 1;

    public $latitude;
    public $longitude;
    public $keterangan;
    public $statusAbsenSekarang = 'Belum Absen';
    public $absensiHariIni;
    public $confirmPulangCepat = false;
    public $pesanPeringatanPulang = '';
    public $kantorLatitude;
    public $kantorLongitude;
    public $radiusAbsensi;

    public function mount()
    {
        $this->loadAbsensiHariIni();
        $this->kantorLatitude = (float) PengaturanSistem::get('kantor_latitude', 0);
        $this->kantorLongitude = (float) PengaturanSistem::get('kantor_longitude', 0);
        $this->radiusAbsensi = (float) PengaturanSistem::get('radius_absensi', 500);
    }

    public function loadAbsensiHariIni()
    {
        $this->absensiHariIni = Absensi::getAbsensiHariIni(Auth::id());

        if ($this->absensiHariIni) {
            if ($this->absensiHariIni->jam_pulang) {
                $this->statusAbsenSekarang = 'Sudah Pulang';
            } elseif ($this->absensiHariIni->jam_masuk) {
                $this->statusAbsenSekarang = 'Sudah Masuk';
            }
        }
    }

    public function absenMasuk()
    {
        if (!$this->validateGps()) {
            return;
        }

        if ($this->absensiHariIni && $this->absensiHariIni->jam_masuk) {
            Notification::make()->warning()->title('Anda sudah melakukan absen masuk hari ini.')->send();
            return;
        }

        $now = Carbon::now();
        $jamMasukStandar = Carbon::createFromTimeString(PengaturanSistem::get('jam_masuk', '08:00'));
        $toleransi = (int) PengaturanSistem::get('toleransi_menit', 15);

        $batasTerlambat = $jamMasukStandar->copy()->addMinutes($toleransi);

        $status = $now->greaterThan($batasTerlambat) ? 'terlambat' : 'hadir';

        Absensi::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'tanggal' => $now->toDateString(),
            ],
            [
                'jam_masuk' => $now->toTimeString(),
                'latitude_masuk' => $this->latitude,
                'longitude_masuk' => $this->longitude,
                'status' => $status,
                'keterangan' => $this->keterangan,
            ]
        );

        Notification::make()->success()->title('Absen Masuk Berhasil!')->send();
        $this->loadAbsensiHariIni();
        $this->keterangan = '';
    }

    public function absenPulang($force = false)
    {
        if (!$this->validateGps()) {
            return;
        }

        if (!$this->absensiHariIni || !$this->absensiHariIni->jam_masuk) {
            Notification::make()->warning()->title('Anda belum melakukan absen masuk.')->send();
            return;
        }

        if ($this->absensiHariIni->jam_pulang) {
            Notification::make()->warning()->title('Anda sudah melakukan absen pulang.')->send();
            return;
        }

        $now = Carbon::now();
        $jamMasuk = Carbon::createFromTimeString($this->absensiHariIni->jam_masuk);
        $jamMasukStandar = Carbon::createFromTimeString(PengaturanSistem::get('jam_masuk', '08:00'));
        $jamPulangStandar = Carbon::createFromTimeString(PengaturanSistem::get('jam_pulang', '16:00'));
        
        $durasiStandarMenit = $jamMasukStandar->diffInMinutes($jamPulangStandar);
        $durasiAktualMenit = $jamMasuk->diffInMinutes($now);

        $isPulangCepat = $now->lessThan($jamPulangStandar) || ($durasiAktualMenit < $durasiStandarMenit);

        if ($isPulangCepat && !$force) {
            $kurangMenit = $durasiStandarMenit - $durasiAktualMenit;
            $jamKurang = floor($kurangMenit / 60);
            $menitKurang = $kurangMenit % 60;
            
            $durasiAktualJam = floor($durasiAktualMenit / 60);
            $durasiAktualSisaMenit = $durasiAktualMenit % 60;

            $this->pesanPeringatanPulang = "Jam pulang standar adalah pukul " . $jamPulangStandar->format('H:i') . " (durasi minimal " . ($durasiStandarMenit/60) . " jam). Durasi kerja Anda baru {$durasiAktualJam} jam {$durasiAktualSisaMenit} menit (kurang {$jamKurang} jam {$menitKurang} menit). Apakah Anda yakin ingin pulang cepat?";
            $this->confirmPulangCepat = true;
            return;
        }

        $keteranganUpdate = $this->absensiHariIni->keterangan;
        if ($isPulangCepat) {
            $durasiAktualJam = floor($durasiAktualMenit / 60);
            $durasiAktualSisaMenit = $durasiAktualMenit % 60;
            $catatanPulangCepat = "Pulang Cepat (Durasi Kerja: {$durasiAktualJam} jam {$durasiAktualSisaMenit} menit)";
            $keteranganUpdate = $keteranganUpdate ? $keteranganUpdate . '; ' . $catatanPulangCepat : $catatanPulangCepat;
        }

        $this->absensiHariIni->update([
            'jam_pulang' => $now->toTimeString(),
            'latitude_pulang' => $this->latitude,
            'longitude_pulang' => $this->longitude,
            'keterangan' => $keteranganUpdate,
        ]);

        Notification::make()->success()->title('Absen Pulang Berhasil!' . ($isPulangCepat ? ' (Dicatat Pulang Cepat)' : ''))->send();
        $this->confirmPulangCepat = false;
        $this->loadAbsensiHariIni();
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
