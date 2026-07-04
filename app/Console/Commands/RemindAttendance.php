<?php

namespace App\Console\Commands;

use App\Models\Absensi;
use App\Models\Izin;
use App\Models\PengaturanSistem;
use App\Models\User;
use App\Notifications\AttendanceReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RemindAttendance extends Command
{
    /**
     * Nama dan signature dari console command.
     * --type: tipe pengingat (masuk atau pulang)
     * --force: memaksa pengiriman notifikasi mengabaikan validasi jam saat ini (sangat berguna untuk testing)
     */
    protected $signature = 'app:remind-attendance {--type= : Tipe pengingat (masuk / pulang)} {--force : Paksa kirim abaikan kecocokan jam}';

    /**
     * Deskripsi dari console command.
     */
    protected $description = 'Kirim notifikasi web push pengingat presensi masuk & pulang ke pegawai secara otomatis';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');
        $force = $this->option('force');

        $now = Carbon::now();
        $todayStr = $now->toDateString();
        $currentTime = $now->format('H:i');

        // Ambil pengaturan jam masuk & pulang dari database PengaturanSistem
        $jamMasukSetting = PengaturanSistem::get('jam_masuk', '07:00');
        $jamPulangSetting = PengaturanSistem::get('jam_pulang', '17:00');

        $this->info("Waktu sekarang: {$currentTime} (Hari: " . $now->translatedFormat('l') . ")");
        $this->info("Pengaturan Jam Masuk: {$jamMasukSetting}");
        $this->info("Pengaturan Jam Pulang: {$jamPulangSetting}");

        // Jika dipaksa (--force) dan type ditentukan, langsung kirim spesifik
        if ($force && $type) {
            $this->info("Menjalankan paksa (--force) untuk tipe: {$type}...");
            $this->sendReminders($type, $todayStr);
            return self::SUCCESS;
        }

        // Tentukan apakah harus mengirim notifikasi masuk atau pulang berdasarkan jam saat ini
        $shouldSendMasuk = ($currentTime === $jamMasukSetting);
        $shouldSendPulang = ($currentTime === $jamPulangSetting);

        // Jika type di-spesifikasi lewat argumen cron, prioritaskan
        if ($type === 'masuk') {
            if ($shouldSendMasuk || $force) {
                $this->sendReminders('masuk', $todayStr);
            } else {
                $this->info("Belum waktunya jam masuk ({$jamMasukSetting}). Gunakan opsi --force untuk memaksa.");
            }
        } elseif ($type === 'pulang') {
            if ($shouldSendPulang || $force) {
                $this->sendReminders('pulang', $todayStr);
            } else {
                $this->info("Belum waktunya jam pulang ({$jamPulangSetting}). Gunakan opsi --force untuk memaksa.");
            }
        } else {
            // Otomatis deteksi jam jika dipanggil tanpa argument type
            if ($shouldSendMasuk) {
                $this->sendReminders('masuk', $todayStr);
            } elseif ($shouldSendPulang) {
                $this->sendReminders('pulang', $todayStr);
            } else {
                $this->info("Waktu saat ini tidak cocok dengan jam masuk ({$jamMasukSetting}) maupun jam pulang ({$jamPulangSetting}).");
            }
        }

        return self::SUCCESS;
    }

    /**
     * Kirim notifikasi ke pegawai yang ditargetkan.
     */
    private function sendReminders(string $type, string $todayStr)
    {
        // Cache lock agar tidak terkirim ganda di menit yang sama (jika scheduler dipanggil rapat)
        $lockKey = "remind_attendance_{$type}_{$todayStr}";
        if (Cache::has($lockKey) && !$this->option('force')) {
            $this->info("Notifikasi tipe '{$type}' untuk hari ini sudah pernah dikirim.");
            return;
        }

        // Ambil semua pegawai aktif yang bukan admin
        $employees = User::where('is_active', true)
            ->where('role', '!=', 'admin')
            ->get();

        $sentCount = 0;

        foreach ($employees as $employee) {
            if ($type === 'masuk') {
                // 1. Cek apakah ada Izin/Cuti/Sakit yang disetujui untuk hari ini
                $hasApprovedIzin = Izin::where('user_id', $employee->id)
                    ->where('status', 'approved')
                    ->whereIn('jenis', ['cuti', 'sakit', 'izin'])
                    ->where('tanggal_mulai', '<=', $todayStr)
                    ->where('tanggal_selesai', '>=', $todayStr)
                    ->exists();

                if ($hasApprovedIzin) {
                    $this->info("Pegawai {$employee->name} dilewati karena sedang Cuti/Sakit/Izin.");
                    continue;
                }

                // 2. Cek apakah sudah absen masuk hari ini
                $hasAbsenMasuk = Absensi::where('user_id', $employee->id)
                    ->whereDate('created_at', $todayStr)
                    ->whereNotNull('jam_masuk')
                    ->exists();

                if ($hasAbsenMasuk) {
                    $this->info("Pegawai {$employee->name} dilewati karena sudah presensi masuk.");
                    continue;
                }

                // Kirim Notifikasi Pengingat Masuk
                $title = "⏰ Waktunya Presensi Masuk!";
                $message = "Halo {$employee->name}, saat ini sudah memasuki jam kerja. Jangan lupa untuk melakukan presensi masuk hari ini ya! Semangat!";
                
                $employee->notify(new AttendanceReminderNotification($type, $title, $message));
                $sentCount++;

            } elseif ($type === 'pulang') {
                // 1. Cek apakah sudah absen masuk tetapi belum absen pulang hari ini
                $hasAbsenMasukWithoutPulang = Absensi::where('user_id', $employee->id)
                    ->whereDate('created_at', $todayStr)
                    ->whereNotNull('jam_masuk')
                    ->whereNull('jam_pulang')
                    ->exists();

                if (!$hasAbsenMasukWithoutPulang) {
                    $this->info("Pegawai {$employee->name} dilewati karena belum absen masuk / sudah absen pulang hari ini.");
                    continue;
                }

                // Kirim Notifikasi Pengingat Pulang
                $title = "👋 Waktunya Presensi Pulang!";
                $message = "Halo {$employee->name}, sudah waktunya jam pulang. Sebelum pulang, pastikan Anda melakukan presensi pulang terlebih dahulu ya!";
                
                $employee->notify(new AttendanceReminderNotification($type, $title, $message));
                $sentCount++;
            }
        }

        $this->info("Berhasil mengirimkan {$sentCount} notifikasi pengingat {$type}.");
        
        // Simpan kunci lock agar tidak mengirim ulang hari ini
        Cache::put($lockKey, true, now()->endOfDay());
    }
}
