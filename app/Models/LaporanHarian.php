<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class LaporanHarian extends Model
{
    use HasFactory;

    protected $table = 'laporan_harians';

    protected $fillable = [
        'user_id',
        'tanggal',
        'operasional',
        'lokasi_knmp',
        'dokumentasi',
        'keterangan_dokumentasi',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'operasional' => 'array',
            'lokasi_knmp' => 'array',
        ];
    }

    /**
     * Relasi ke User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper untuk mendapatkan nama hari dalam Bahasa Indonesia
     */
    public function getHariIndonesian(): string
    {
        if (!$this->tanggal) {
            return '';
        }

        $days = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];

        $englishDay = $this->tanggal->format('l');
        return $days[$englishDay] ?? $englishDay;
    }

    /**
     * Helper untuk mendapatkan format tanggal lengkap dalam Bahasa Indonesia (contoh: 25 Mei 2026)
     */
    public function getTanggalIndonesian(): string
    {
        if (!$this->tanggal) {
            return '';
        }

        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        $day = $this->tanggal->format('j');
        $monthNum = (int)$this->tanggal->format('n');
        $year = $this->tanggal->format('Y');

        return "{$day} {$months[$monthNum]} {$year}";
    }
}
