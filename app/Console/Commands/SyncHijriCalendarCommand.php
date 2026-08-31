<?php

namespace App\Console\Commands;

use App\Services\HijriCalendarService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SyncHijriCalendarCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hijri:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi kalender Hijriah dari endpoint hijri-ticker';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai sinkronisasi Kalender Hijriah dari ticker API...');

        // 1. Ambil data hari ini dari ticker
        $todayHijri = HijriCalendarService::fetchTodayFromTicker();

        if ($todayHijri) {
            $today = now();
            $todayKey = $today->format('Y-m-d');
            Cache::put("hijri_calendar_{$todayKey}", $todayHijri, now()->addDays(30));

            $this->info("✓ Berhasil sinkronisasi tanggal hari ini [{$todayKey}] => {$todayHijri}");

            // Pre-calculate untuk 30 hari ke belakang dan 7 hari ke depan
            for ($i = -30; $i <= 7; $i++) {
                $d = $today->copy()->addDays($i);
                $dKey = $d->format('Y-m-d');
                $calculated = HijriCalendarService::calculateHijriForDate($d);
                Cache::put("hijri_calendar_{$dKey}", $calculated, now()->addDays(30));
            }

            $this->info("✓ Cache kalender Hijriah untuk 37 hari berhasil diperbarui.");
            return Command::SUCCESS;
        }

        $this->error('Gagal mengambil data dari endpoint hijri-ticker.');
        return Command::FAILURE;
    }
}
