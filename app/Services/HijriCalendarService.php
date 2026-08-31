<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HijriCalendarService
{
    /**
     * Nama-nama bulan Hijriah standar.
     */
    const HIJRI_MONTHS = [
        1 => "Muharram",
        2 => "Safar",
        3 => "Rabi'ul Awal",
        4 => "Rabi'ul Akhir",
        5 => "Jumadil Awal",
        6 => "Jumadil Akhir",
        7 => "Rajab",
        8 => "Sya'ban",
        9 => "Ramadhan",
        10 => "Syawwal",
        11 => "Dzulqa'dah",
        12 => "Dzulhijjah",
    ];

    /**
     * Ambil teks tanggal Hijriah untuk tanggal Masehi tertentu.
     * Menggunakan cache 30 hari agar pengambilan di timeline berjalan instan.
     *
     * @param string|Carbon|\DateTimeInterface $date
     * @return string Contoh: "18 Rabi'ul Awal 1448 H"
     */
    public static function getHijriDate(string|Carbon|\DateTimeInterface $date): string
    {
        $carbon = is_string($date) ? Carbon::parse($date) : Carbon::instance($date);
        $dateKey = $carbon->format('Y-m-d');
        $cacheKey = "hijri_calendar_{$dateKey}";

        $result = Cache::remember($cacheKey, now()->addDays(30), function () use ($carbon) {
            // Jika tanggal adalah hari ini, ambil langsung dari ticker API
            if ($carbon->isToday()) {
                $todayHijri = self::fetchTodayFromTicker();
                if ($todayHijri) {
                    return $todayHijri;
                }
            }

            // Untuk tanggal lain atau jika API tidak merespons, hitung berdasarkan kalender terkalibrasi
            return self::calculateHijriForDate($carbon);
        });

        return trim(preg_replace('/\s*H$/i', '', (string) $result));
    }

    /**
     * Ambil data tanggal hari ini dari API endpoint vMix Hijri Ticker.
     */
    public static function fetchTodayFromTicker(): ?string
    {
        try {
            $apiUrl = config('services.hijri.ticker_url', 'http://10.112.115.18:8088/api/vmix/hijri-ticker');

            $response = Http::timeout(3)->get($apiUrl);
            if (!$response->successful()) {
                Log::warning("Gagal memuat Hijri ticker API. Status: {$response->status()}");
                return null;
            }

            $data = $response->json();

            // 1. Cek dari array 'Kalender Terpisah' index 1 (format lengkap dengan H)
            if (isset($data['Kalender Terpisah'][1]['tanggal_hijriyah']) && !empty($data['Kalender Terpisah'][1]['tanggal_hijriyah'])) {
                $hijriString = trim(preg_replace('/\s*H$/i', '', trim($data['Kalender Terpisah'][1]['tanggal_hijriyah'])));
                
                // Simpan referensi kalibrasi tanggal hari ini untuk perhitungan tanggal lain
                if (isset($data['Kalender Terpisah'][1]['hijri_tanggal'], $data['Kalender Terpisah'][1]['hijri_bulan'], $data['Kalender Terpisah'][1]['hijri_tahun'])) {
                    Cache::put('hijri_calibration_base', [
                        'masehi_date'   => now()->toDateString(),
                        'hijri_day'     => (int) $data['Kalender Terpisah'][1]['hijri_tanggal'],
                        'hijri_month'   => (int) $data['Kalender Terpisah'][1]['hijri_bulan'],
                        'hijri_year'    => (int) $data['Kalender Terpisah'][1]['hijri_tahun'],
                    ], now()->addDays(7));
                }

                return $hijriString;
            }

            // 2. Cek dari 'tanggal_hijriah' (contoh: "Senin, 18 Rabi'ul Awal 1448")
            if (isset($data['tanggal_hijriah']) && !empty($data['tanggal_hijriah'])) {
                $cleaned = preg_replace('/^[a-zA-Z]+,\s*/', '', trim($data['tanggal_hijriah']));
                return trim(preg_replace('/\s*H$/i', '', $cleaned));
            }

            return null;
        } catch (\Throwable $e) {
            Log::error("Error fetch Hijri ticker: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Hitung tanggal Hijriah untuk tanggal tertentu dengan kalibrasi base dari API.
     */
    public static function calculateHijriForDate(Carbon $targetDate): string
    {
        $base = Cache::get('hijri_calibration_base');

        if (!$base) {
            // Jika belum ada cache kalibrasi, coba fetch hari ini terlebih dahulu
            self::fetchTodayFromTicker();
            $base = Cache::get('hijri_calibration_base');
        }

        if ($base && isset($base['masehi_date'])) {
            $baseMasehi = Carbon::parse($base['masehi_date'])->startOfDay();
            $target = $targetDate->copy()->startOfDay();
            $diffDays = (int) $baseMasehi->diffInDays($target, false);

            if ($diffDays === 0) {
                $monthName = self::HIJRI_MONTHS[$base['hijri_month']] ?? "Bulan {$base['hijri_month']}";
                return "{$base['hijri_day']} {$monthName} {$base['hijri_year']}";
            }

            // Hitung tanggal hijriah berdasarkan selisih hari
            $day = $base['hijri_day'] + $diffDays;
            $month = $base['hijri_month'];
            $year = $base['hijri_year'];

            while ($day > 30) {
                $day -= 30;
                $month++;
                if ($month > 12) {
                    $month = 1;
                    $year++;
                }
            }

            while ($day < 1) {
                $day += 30;
                $month--;
                if ($month < 1) {
                    $month = 12;
                    $year--;
                }
            }

            $monthName = self::HIJRI_MONTHS[$month] ?? "Bulan {$month}";
            return "{$day} {$monthName} {$year}";
        }

        // Algoritma astronomis cadangan jika API belum pernah diakses
        return self::calculateFallbackHijri($targetDate->year, $targetDate->month, $targetDate->day);
    }

    /**
     * Format tanggal gabungan: Hijriah • Masehi.
     * Contoh: "18 Rabi'ul Awal 1448 • Senin, 31 Agustus 2026"
     */
    public static function formatWithGregorian(string|Carbon|\DateTimeInterface $date, string $separator = ' • '): string
    {
        $carbon = is_string($date) ? Carbon::parse($date) : Carbon::instance($date);
        $hijri = self::getHijriDate($carbon);
        $gregorian = $carbon->translatedFormat('l, d F Y');

        return "{$hijri}{$separator}{$gregorian}";
    }

    /**
     * Algoritma standar konversi Hijriah (cadangan independen).
     */
    public static function calculateFallbackHijri(int $year, int $month, int $day): string
    {
        $m = $month;
        $y = $year;
        $d = $day;
        if (($y > 1582) || (($y == 1582) && ($m > 10)) || (($y == 1582) && ($m == 10) && ($d > 14))) {
            $jd = (int)((1461 * ($y + 4800 + (int)(($m - 14) / 12))) / 4) +
                  (int)((367 * ($m - 2 - 12 * ((int)(($m - 14) / 12)))) / 12) -
                  (int)((3 * (int)((($y + 4900 + (int)(($m - 14) / 12)) / 100))) / 4) +
                  $d - 32075;
        } else {
            $jd = 367 * $y - (int)((7 * ($y + 5001 + (int)(($m - 9) / 7))) / 4) +
                  (int)((275 * $m) / 9) + $d + 1729777;
        }
        $l = $jd - 1948440 + 10632;
        $n = (int)(($l - 1) / 10631);
        $l = $l - 10631 * $n + 354;
        $j = ((int)((10985 - $l) / 5316)) * ((int)((50 * $l) / 17719)) +
             ((int)($l / 5670)) * ((int)((43 * $l) / 15238));
        $l = $l - ((int)((30 - $j) / 15)) * ((int)((17719 * $j) / 50)) -
             ((int)($j / 16)) * ((int)((15238 * $j) / 43)) + 29;
        $hMonth = (int)((24 * $l) / 709);
        $hDay = $l - (int)((709 * $hMonth) / 24);
        $hYear = 30 * $n + $j - 30;

        $monthName = self::HIJRI_MONTHS[$hMonth] ?? 'Hijriah';
        return "{$hDay} {$monthName} {$hYear}";
    }
}
