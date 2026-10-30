<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Har mahine ki 1 tareekh ko active students ki fee rows banayein aur pichle
 * mahine ka baqi balance next month ki fee me add karein.
 *
 * Daily check rakha gaya hai taake agar 1st ko server down ho to agle din
 * missing rows automatically ban jayein (existing rows skip hoti hain).
 */
Schedule::command('fees:generate')
    ->dailyAt('00:10')
    ->timezone(config('app.timezone'))
    ->withoutOverlapping();
