<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\User;
use App\Models\PengaturanSistem;
use Carbon\Carbon;
use Illuminate\Http\Request;

class QrAbsensiController extends Controller
{
    public function showScanForm($token)
    {
        $pegawai = User::where('qr_token', $token)->where('role', 'pegawai')->where('is_active', true)->first();

        if (!$pegawai) {
            abort(404, 'QR Code tidak valid atau Pegawai tidak aktif.');
        }

        $absensiHariIni = Absensi::getAbsensiHariIni($pegawai->id);

        return view('absensi.scan', compact('pegawai', 'absensiHariIni'));
    }

    public function processScan(Request $request, $token)
    {
        $pegawai = User::where('qr_token', $token)->where('role', 'pegawai')->where('is_active', true)->first();

        if (!$pegawai) {
            return back()->with('error', 'QR Code tidak valid.');
        }

        $now = Carbon::now();
        $absensiHariIni = Absensi::getAbsensiHariIni($pegawai->id);

        // Jika tombol Absen Masuk ditekan
        if ($request->has('absen_masuk')) {
            if ($absensiHariIni && $absensiHariIni->jam_masuk) {
                return back()->with('error', 'Pegawai sudah melakukan absen masuk hari ini.');
            }

            $jamMasukStandar = Carbon::createFromTimeString(PengaturanSistem::get('jam_masuk', '08:00'));
            $toleransi = (int) PengaturanSistem::get('toleransi_menit', 15);
            $batasTerlambat = $jamMasukStandar->copy()->addMinutes($toleransi);
            
            $status = $now->greaterThan($batasTerlambat) ? 'terlambat' : 'hadir';

            Absensi::updateOrCreate(
                [
                    'user_id' => $pegawai->id,
                    'tanggal' => $now->toDateString(),
                ],
                [
                    'jam_masuk'     => $now->toTimeString(),
                    'status'        => $status,
                    'qr_scan_masuk' => true,
                ]
            );

            return back()->with('success', 'Absen masuk berhasil direkam.');
        }

        // Jika tombol Absen Pulang ditekan
        if ($request->has('absen_pulang')) {
            if (!$absensiHariIni || !$absensiHariIni->jam_masuk) {
                return back()->with('error', 'Pegawai belum melakukan absen masuk.');
            }

            if ($absensiHariIni->jam_pulang) {
                return back()->with('error', 'Pegawai sudah melakukan absen pulang.');
            }

            $absensiHariIni->update([
                'jam_pulang' => $now->toTimeString(),
                'qr_scan_pulang' => true,
            ]);

            return back()->with('success', 'Absen pulang berhasil direkam.');
        }

        return back()->with('error', 'Aksi tidak valid.');
    }
}
