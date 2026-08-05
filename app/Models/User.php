<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use NotificationChannels\WebPush\HasPushSubscriptions;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasFactory, Notifiable, HasPushSubscriptions;

    protected $fillable = [
        'name',
        'email',
        'password',
        'nik',
        'role',
        'jabatan',
        'departemen',
        'no_hp',
        'foto',
        'qr_token',
        'is_active',
        'latitude',
        'longitude',
        'timezone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
            'latitude'          => 'float',
            'longitude'         => 'float',
        ];
    }

    public static function getTimezoneFromCoordinates(?float $lat, ?float $lng, ?string $fallbackTz = 'Asia/Jakarta'): string
    {
        if ($lng === null) {
            return $fallbackTz ?: 'Asia/Jakarta';
        }
        if ($lng >= 124.5) {
            return 'Asia/Jayapura';
        } elseif ($lng >= 114.0) {
            return 'Asia/Makassar';
        } else {
            return 'Asia/Jakarta';
        }
    }

    public function getTimezoneCodeAttribute(): string
    {
        return match ($this->timezone) {
            'Asia/Makassar' => 'WITA',
            'Asia/Jayapura' => 'WIT',
            default         => 'WIB',
        };
    }

    public function getTimezoneLabelAttribute(): string
    {
        return match ($this->timezone) {
            'Asia/Makassar' => 'WITA (UTC+8)',
            'Asia/Jayapura' => 'WIT (UTC+9)',
            default         => 'WIB (UTC+7)',
        };
    }

    public function detectAndUpdateTimezone(?float $lat, ?float $lng): string
    {
        $tz = static::getTimezoneFromCoordinates($lat, $lng, $this->timezone ?: 'Asia/Jakarta');
        if ($this->timezone !== $tz) {
            $this->update(['timezone' => $tz]);
        }
        return $tz;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->qr_token)) {
                $user->qr_token = Str::uuid()->toString();
            }
        });
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->role === 'admin' && $this->is_active;
        }

        if ($panel->getId() === 'pegawai') {
            return $this->is_active;
        }

        return false;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class);
    }

    public function izin(): HasMany
    {
        return $this->hasMany(Izin::class);
    }

    public function getQrUrlAttribute(): string
    {
        return route('absensi.scan', $this->qr_token);
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->foto ? asset('storage/' . $this->foto) : null;
    }
}
