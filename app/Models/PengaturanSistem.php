<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PengaturanSistem extends Model
{
    protected $table = 'pengaturan_sistem';

    protected $fillable = ['key', 'value', 'label', 'tipe'];

    /**
     * Ambil nilai setting berdasarkan key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Simpan/update nilai setting.
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'label' => $key]
        );
        Cache::forget("setting_{$key}");
    }

    /**
     * Hitung jarak antara dua koordinat GPS (dalam meter).
     * Menggunakan formula Haversine.
     */
    public static function hitungJarak(
        float $lat1, float $lng1,
        float $lat2, float $lng2
    ): float {
        $earthRadius = 6371000; // meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    /**
     * Cek apakah koordinat dalam radius kantor.
     */
    public static function dalamRadius(float $lat, float $lng): bool
    {
        $kantorLat    = (float) static::get('kantor_latitude', 0);
        $kantorLng    = (float) static::get('kantor_longitude', 0);
        $radiusMeter  = (float) static::get('radius_absensi', 500);

        if ($kantorLat === 0.0 && $kantorLng === 0.0) {
            return true; // Jika belum diatur, loloskan semua
        }

        $jarak = static::hitungJarak($lat, $lng, $kantorLat, $kantorLng);
        return $jarak <= $radiusMeter;
    }
}
