<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Absensi extends Model
{
    protected $table = 'absensi';

    protected $fillable = [
        'user_id',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'status',
        'latitude_masuk',
        'longitude_masuk',
        'latitude_pulang',
        'longitude_pulang',
        'foto_masuk',
        'foto_pulang',
        'qr_scan_masuk',
        'qr_scan_pulang',
        'keterangan',
    ];

    protected $casts = [
        'tanggal'       => 'date:Y-m-d',
        'qr_scan_masuk' => 'boolean',
        'qr_scan_pulang' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'hadir'     => 'success',
            'terlambat' => 'warning',
            'izin'      => 'info',
            'sakit'     => 'warning',
            'alpha'     => 'danger',
            default     => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'hadir'     => 'Hadir',
            'terlambat' => 'Terlambat',
            'izin'      => 'Izin',
            'sakit'     => 'Sakit',
            'alpha'     => 'Alpha',
            default     => 'Unknown',
        };
    }

    public function getDurasiKerjaAttribute(): ?string
    {
        if (!$this->jam_masuk || !$this->jam_pulang) {
            return null;
        }
        $masuk  = Carbon::createFromTimeString($this->jam_masuk);
        $pulang = Carbon::createFromTimeString($this->jam_pulang);
        $diff   = $masuk->diff($pulang);
        return $diff->format('%H jam %I menit');
    }

    public static function getAbsensiHariIni(int $userId): ?self
    {
        return static::where('user_id', $userId)
            ->whereDate('tanggal', Carbon::today()->toDateString())
            ->first();
    }
}
