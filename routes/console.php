<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

// Jadwalkan pengingat presensi berjalan setiap menit pada hari kerja
// (Jam masuk & pulang dibaca dinamis di dalam command dari Pengaturan Sistem)
Schedule::command('app:remind-attendance')
    ->everyMinute()
    ->weekdays();
