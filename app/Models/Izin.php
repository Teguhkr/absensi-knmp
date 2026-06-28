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
}
