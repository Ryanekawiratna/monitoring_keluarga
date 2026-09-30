<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwal pengingat tagihan WhatsApp otomatis setiap hari pukul 08:00
Illuminate\Support\Facades\Schedule::command('reminder:send')->dailyAt('08:00');
