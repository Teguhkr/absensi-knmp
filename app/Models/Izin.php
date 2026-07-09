<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Izin extends Model
{
    protected $table = 'izin';

    protected $fillable = [
        'user_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'jenis',
        'alasan',
        'lampiran',
        'nomor_spt',
        // Lokasi penugasan
        'latitude',
        'longitude',
        // Request perubahan lokasi
        'req_latitude',
        'req_longitude',
        'req_lokasi_status',
        'req_lokasi_alasan',
        'req_lokasi_catatan',
        // Status & approval
        'status',
        'approved_by',
        'catatan_admin',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date:Y-m-d',
        'tanggal_selesai' => 'date:Y-m-d',
        'latitude'        => 'float',
        'longitude'       => 'float',
        'req_latitude'    => 'float',
        'req_longitude'   => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getJumlahHariAttribute(): int
    {
        return $this->tanggal_mulai->diffInDays($this->tanggal_selesai) + 1;
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'success',
            'rejected' => 'danger',
            'pending'  => 'warning',
            default    => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'pending'  => 'Menunggu',
            default    => 'Unknown',
        };
    }

    /**
     * Mengembalikan latitude efektif lokasi penugasan.
     * Jika ada request perubahan lokasi yang sudah disetujui, gunakan yang baru.
     */
    public function getLokasiAktifLatAttribute(): ?float
    {
        if ($this->req_lokasi_status === 'approved' && $this->req_latitude) {
            return $this->req_latitude;
        }
        return $this->latitude;
    }

    /**
     * Mengembalikan longitude efektif lokasi penugasan.
     */
    public function getLokasiAktifLngAttribute(): ?float
    {
        if ($this->req_lokasi_status === 'approved' && $this->req_longitude) {
            return $this->req_longitude;
        }
        return $this->longitude;
    }

    /**
     * Apakah ada request perubahan lokasi yang masih pending?
     */
    public function getHasRequestLokasiPendingAttribute(): bool
    {
        return $this->req_lokasi_status === 'pending'
            && $this->req_latitude !== null
            && $this->req_longitude !== null;
    }

    /**
     * Hook model untuk mendengarkan perubahan data Izin.
     */
    protected static function booted()
    {
        static::created(function ($izin) {
            $izin->notifyAdminsOnCreation();
        });

        static::updated(function ($izin) {
            // 1. Notif jika status pengajuan utama (cuti/sakit/penugasan) disetujui atau ditolak
            if ($izin->wasChanged('status') && in_array($izin->status, ['approved', 'rejected'])) {
                $izin->notifyUserOnStatusChange();
            }

            // 2. Notif jika ada pengajuan perubahan lokasi penugasan dari pegawai
            if ($izin->wasChanged('req_lokasi_status') && $izin->req_lokasi_status === 'pending') {
                $izin->notifyAdminsOnLocationRequest();
            }

            // 3. Notif jika pengajuan perubahan lokasi penugasan disetujui atau ditolak oleh admin
            if ($izin->wasChanged('req_lokasi_status') && in_array($izin->req_lokasi_status, ['approved', 'rejected'])) {
                $izin->notifyUserOnLocationRequestStatusChange();
            }
        });
    }

    /**
     * Kirim notifikasi ke semua admin saat pengajuan izin baru dibuat.
     */
    public function notifyAdminsOnCreation()
    {
        $user = $this->user;
        $namaPegawai = $user ? $user->name : 'Pegawai';
        $jenisLabel = match ($this->jenis) {
            'cuti' => 'Cuti',
            'sakit' => 'Sakit',
            'dinas' => 'Penugasan/Dinas',
            default => 'Izin/Penugasan',
        };

        $tglMulai = $this->tanggal_mulai ? $this->tanggal_mulai->format('d M Y') : '-';
        $tglSelesai = $this->tanggal_selesai ? $this->tanggal_selesai->format('d M Y') : '-';

        $title = "📝 Pengajuan {$jenisLabel} Baru";
        $message = "{$namaPegawai} mengajukan {$jenisLabel} baru dari {$tglMulai} s.d {$tglSelesai}.";
        
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\AdminNotification($title, $message, '/admin/izins'));
        }
    }

    /**
     * Kirim notifikasi ke semua admin saat ada permohonan perubahan lokasi penugasan.
     */
    public function notifyAdminsOnLocationRequest()
    {
        $user = $this->user;
        $namaPegawai = $user ? $user->name : 'Pegawai';

        $title = "📍 Permohonan Perubahan Lokasi";
        $message = "{$namaPegawai} mengajukan perubahan lokasi penugasan dengan alasan: " . ($this->req_lokasi_alasan ?? '-');
        
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\AdminNotification($title, $message, '/admin/izins'));
        }
    }

    /**
     * Kirim notifikasi ke pegawai saat pengajuan (cuti/sakit/penugasan) disetujui/ditolak.
     */
    public function notifyUserOnStatusChange()
    {
        $user = $this->user;
        if (!$user) {
            return;
        }

        $jenisLabel = match ($this->jenis) {
            'cuti' => 'Cuti',
            'sakit' => 'Sakit',
            'dinas' => 'Penugasan/Dinas',
            default => 'Izin/Penugasan',
        };

        $statusLabel = $this->status === 'approved' ? 'Disetujui' : 'Ditolak';
        $icon = $this->status === 'approved' ? '✅' : '❌';
        
        $title = "{$icon} Pengajuan {$jenisLabel} {$statusLabel}";
        
        $tglMulai = $this->tanggal_mulai ? $this->tanggal_mulai->format('d M Y') : '-';
        $tglSelesai = $this->tanggal_selesai ? $this->tanggal_selesai->format('d M Y') : '-';

        if ($this->status === 'approved') {
            $message = "Halo {$user->name}, pengajuan {$jenisLabel} Anda dari {$tglMulai} s.d {$tglSelesai} telah disetujui.";
        } else {
            $catatan = $this->catatan_admin ? " Catatan: " . $this->catatan_admin : "";
            $message = "Halo {$user->name}, pengajuan {$jenisLabel} Anda dari {$tglMulai} s.d {$tglSelesai} ditolak.{$catatan}";
        }

        $user->notify(new \App\Notifications\IzinStatusNotification($title, $message, '/pegawai/izins'));
    }

    /**
     * Kirim notifikasi ke pegawai saat request perubahan lokasi disetujui/ditolak.
     */
    public function notifyUserOnLocationRequestStatusChange()
    {
        $user = $this->user;
        if (!$user) {
            return;
        }

        $statusLabel = $this->req_lokasi_status === 'approved' ? 'Disetujui' : 'Ditolak';
        $icon = $this->req_lokasi_status === 'approved' ? '✅' : '❌';

        $title = "{$icon} Perubahan Lokasi Penugasan {$statusLabel}";

        if ($this->req_lokasi_status === 'approved') {
            $message = "Halo {$user->name}, permohonan perubahan lokasi penugasan Anda telah disetujui oleh admin.";
        } else {
            $catatan = $this->req_lokasi_catatan ? " Catatan: " . $this->req_lokasi_catatan : "";
            $message = "Halo {$user->name}, permohonan perubahan lokasi penugasan Anda ditolak.{$catatan}";
        }

        $user->notify(new \App\Notifications\IzinStatusNotification($title, $message, '/pegawai/absensi-saya'));
    }
}
