<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwal pengingat tagihan WhatsApp otomatis setiap hari pukul 08:00 WIB
Schedule::command('reminder:send')->dailyAt('08:00')->timezone('Asia/Jakarta');

// Jadwal pengingat pencatatan keuangan setiap hari pukul 10:00 (siang) dan 19:00 (7 sore) WIB
Schedule::command('expense:reminder-send --session=siang')->dailyAt('10:00')->timezone('Asia/Jakarta');
Schedule::command('expense:reminder-send --session=sore')->dailyAt('19:00')->timezone('Asia/Jakarta');
