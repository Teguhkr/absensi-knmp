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
     * --user: ID atau email user target untuk testing
     */
    protected $signature = 'app:remind-attendance {--type= : Tipe pengingat (masuk / pulang)} {--force : Paksa kirim abaikan kecocokan jam} {--user= : ID atau email user target untuk testing}';

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
        $userOption = $this->option('user');

        $now = Carbon::now();
        $todayStr = $now->toDateString();
        $currentTime = $now->format('H:i');

        // Ambil pengaturan jam masuk & pulang dari database PengaturanSistem
        $jamMasukSettingRaw = PengaturanSistem::get('jam_masuk', '07:00');
        $jamPulangSettingRaw = PengaturanSistem::get('jam_pulang', '17:00');

        // Format ke H:i agar perbandingan string selalu akurat meskipun di DB tersimpan dengan/tanpa detik
        $jamMasukSetting = Carbon::parse($jamMasukSettingRaw)->format('H:i');
        $jamPulangSetting = Carbon::parse($jamPulangSettingRaw)->format('H:i');

        $this->info("Waktu sekarang: {$currentTime} (Hari: " . $now->translatedFormat('l') . ")");
        $this->info("Pengaturan Jam Masuk: {$jamMasukSetting} (Raw DB: {$jamMasukSettingRaw})");
        $this->info("Pengaturan Jam Pulang: {$jamPulangSetting} (Raw DB: {$jamPulangSettingRaw})");

        // Jika dipaksa (--force atau ada user target) dan type ditentukan, langsung kirim spesifik
        if (($force || $userOption) && $type) {
            $this->info("Menjalankan paksa untuk tipe: {$type}...");
            $this->sendReminders($type, $todayStr);
            return self::SUCCESS;
        }

        // Tentukan apakah harus mengirim notifikasi masuk atau pulang berdasarkan jam saat ini
        $shouldSendMasuk = ($currentTime === $jamMasukSetting);
        $shouldSendPulang = ($currentTime === $jamPulangSetting);

        // Jika type di-spesifikasi lewat argumen cron, prioritaskan
        if ($type === 'masuk') {
            if ($shouldSendMasuk || $force || $userOption) {
                $this->sendReminders('masuk', $todayStr);
            } else {
                $this->info("Belum waktunya jam masuk ({$jamMasukSetting}). Gunakan opsi --force atau --user untuk memaksa.");
            }
        } elseif ($type === 'pulang') {
            if ($shouldSendPulang || $force || $userOption) {
                $this->sendReminders('pulang', $todayStr);
            } else {
                $this->info("Belum waktunya jam pulang ({$jamPulangSetting}). Gunakan opsi --force atau --user untuk memaksa.");
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
        $userOption = $this->option('user');
        $isSingleUserTest = !empty($userOption);

        // Cache lock agar tidak terkirim ganda di menit yang sama (jika scheduler dipanggil rapat)
        $lockKey = "remind_attendance_{$type}_{$todayStr}";
        if (Cache::has($lockKey) && !$this->option('force') && !$isSingleUserTest) {
            $this->info("Notifikasi tipe '{$type}' untuk hari ini sudah pernah dikirim.");
            return;
        }

        // Filter user jika mengirim ke 1 user saja
        if ($isSingleUserTest) {
            $employees = User::where('id', $userOption)
                ->orWhere('email', $userOption)
                ->get();

            if ($employees->isEmpty()) {
                $this->error("Pegawai dengan ID/Email '{$userOption}' tidak ditemukan.");
                return;
            }
        } else {
            // Ambil semua pegawai aktif yang bukan admin
            $employees = User::where('is_active', true)
                ->where('role', '!=', 'admin')
                ->get();
        }

        $sentCount = 0;

        foreach ($employees as $employee) {
            if ($type === 'masuk') {
                // Jika testing 1 orang, lewati filter skip cuti/absen
                if (!$isSingleUserTest) {
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
                        ->whereDate('tanggal', $todayStr)
                        ->whereNotNull('jam_masuk')
                        ->exists();

                    if ($hasAbsenMasuk) {
                        $this->info("Pegawai {$employee->name} dilewati karena sudah presensi masuk.");
                        continue;
                    }
                }

                // Kirim Notifikasi Pengingat Masuk
                $title = "⏰ Waktunya Presensi Masuk!";
                $message = "Halo {$employee->name}, saat ini sudah memasuki jam kerja. Jangan lupa untuk melakukan presensi masuk hari ini ya! Semangat!";
                
                $employee->notify(new AttendanceReminderNotification($type, $title, $message));
                $sentCount++;

            } elseif ($type === 'pulang') {
                if (!$isSingleUserTest) {
                    // 1. Cek apakah sudah absen masuk tetapi belum absen pulang hari ini
                    $hasAbsenMasukWithoutPulang = Absensi::where('user_id', $employee->id)
                        ->whereDate('tanggal', $todayStr)
                        ->whereNotNull('jam_masuk')
                        ->whereNull('jam_pulang')
                        ->exists();

                    if (!$hasAbsenMasukWithoutPulang) {
                        $this->info("Pegawai {$employee->name} dilewati karena belum absen masuk / sudah absen pulang hari ini.");
                        continue;
                    }
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
