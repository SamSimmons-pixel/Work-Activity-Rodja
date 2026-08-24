<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    /**
     * Export monthly activities to a formatted native Excel spreadsheet (.xls).
     */
    public function exportMonthlyActivities(Request $request): StreamedResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        $targetUserId = (int) $request->query('user_id', $currentUser->id);
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);

        // Authorize access
        if ($targetUserId !== $currentUser->id) {
            $isAuthorized = $currentUser->hasRole('Administrator') ||
                $currentUser->hasPermission('activity.read.all') ||
                ($currentUser->hasPermission('activity.read.subordinate') && in_array($targetUserId, $currentUser->getSubordinateIds()));

            if (!$isAuthorized) {
                abort(403, 'Anda tidak memiliki izin untuk mengunduh laporan karyawan ini.');
            }
        }

        $targetUser = User::with(['position', 'division'])->findOrFail($targetUserId);

        $activities = Activity::with(['verifier', 'comments.user'])
            ->where('user_id', $targetUserId)
            ->forMonth($year, $month)
            ->orderBy('activity_date', 'asc')
            ->get();

        $periodDate = Carbon::createFromDate($year, $month, 1);
        $filename = "Rekap_Aktivitas_{$targetUser->username}_{$periodDate->format('Y_m')}.xls";

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($activities, $targetUser, $periodDate) {
            // Output Excel-compatible HTML Spreadsheet Table
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head>';
            echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
            echo '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Aktivitas Kerja</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
            echo '<style>';
            echo 'body { font-family: Calibri, Arial, sans-serif; font-size: 11pt; }';
            echo 'table { border-collapse: collapse; width: 100%; }';
            echo 'th, td { border: 1px solid #cbd5e1; padding: 8px 10px; vertical-align: top; }';
            echo 'th { background-color: #4f46e5; color: #ffffff; font-weight: bold; text-align: center; }';
            echo '.meta-title { font-size: 14pt; font-weight: bold; color: #1e1b4b; margin-bottom: 5px; }';
            echo '.meta-table td { border: none; padding: 3px 6px; }';
            echo '.badge { display: inline-block; padding: 2px 6px; font-weight: bold; border-radius: 4px; font-size: 9pt; }';
            echo '.badge-verified { background-color: #e0e7ff; color: #3730a3; }';
            echo '.badge-reviewed { background-color: #dbeafe; color: #1e40af; }';
            echo '.badge-submitted { background-color: #d1fae5; color: #065f46; }';
            echo '.text-center { text-align: center; }';
            echo '</style>';
            echo '</head>';
            echo '<body>';

            // Meta Info Header
            echo '<table class="meta-table" style="margin-bottom: 15px;">';
            echo '<tr><td colspan="6" class="meta-title">REKAPITULASI AKTIVITAS KERJA KARYAWAN</td></tr>';
            echo '<tr><td style="width: 130px; font-weight: bold;">Periode:</td><td>' . htmlspecialchars($periodDate->translatedFormat('F Y')) . '</td></tr>';
            echo '<tr><td style="font-weight: bold;">Nama Karyawan:</td><td>' . htmlspecialchars($targetUser->full_name) . ' (' . htmlspecialchars('@' . $targetUser->username) . ')</td></tr>';
            echo '<tr><td style="font-weight: bold;">Divisi / Jabatan:</td><td>' . htmlspecialchars(($targetUser->division?->name ?? '-') . ' / ' . ($targetUser->position?->name ?? '-')) . '</td></tr>';
            echo '<tr><td style="font-weight: bold;">Total Aktivitas:</td><td>' . $activities->count() . ' catatan</td></tr>';
            echo '</table>';

            // Data Table
            echo '<table>';
            echo '<thead>';
            echo '<tr>';
            echo '<th style="width: 40px;">No</th>';
            echo '<th style="width: 90px;">Tanggal</th>';
            echo '<th style="width: 100px;">Kategori</th>';
            echo '<th style="width: 110px;">Label / Tags</th>';
            echo '<th style="width: 120px;">Diminta Oleh</th>';
            echo '<th style="width: 250px;">Deskripsi Aktivitas</th>';
            echo '<th style="width: 220px;">Hasil / Luaran</th>';
            echo '<th style="width: 180px;">Kendala / Masalah</th>';
            echo '<th style="width: 100px;">Status</th>';
            echo '<th style="width: 120px;">Diverifikasi Oleh</th>';
            echo '<th style="width: 200px;">Catatan Atasan</th>';
            echo '<th style="width: 110px;">Waktu Dibuat</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            foreach ($activities as $index => $act) {
                $plainActivity = nl2br(htmlspecialchars(trim(strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n"], $act->activity)))));
                $plainResult = nl2br(htmlspecialchars(trim(strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n"], $act->result)))));
                $plainConstraint = !empty($act->constraint)
                    ? nl2br(htmlspecialchars(trim(strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n"], $act->constraint)))))
                    : '-';

                $commentsList = $act->comments->map(function ($c) {
                    return '<strong>' . htmlspecialchars($c->user->full_name) . ':</strong> ' . htmlspecialchars($c->comment);
                })->implode('<br>');

                $tagsList = is_array($act->tags) && count($act->tags) > 0
                    ? htmlspecialchars(implode(', ', $act->tags))
                    : '-';

                $statusLabel = $act->status === 'Verified' ? 'Terverifikasi' : ($act->status === 'Reviewed' ? 'Ditinjau' : ($act->status === 'Submitted' ? 'Terkirim' : 'Draf'));
                $badgeClass = $act->status === 'Verified' ? 'badge-verified' : ($act->status === 'Reviewed' ? 'badge-reviewed' : 'badge-submitted');

                $rowBg = ($index % 2 === 1) ? 'background-color: #f8fafc;' : 'background-color: #ffffff;';

                echo "<tr style=\"{$rowBg}\">";
                echo '<td class="text-center">' . ($index + 1) . '</td>';
                echo '<td class="text-center">' . htmlspecialchars($act->activity_date->translatedFormat('d/m/Y')) . '</td>';
                echo '<td class="text-center font-bold">' . htmlspecialchars($act->category ?? 'Umum') . '</td>';
                echo '<td>' . $tagsList . '</td>';
                echo '<td>' . htmlspecialchars($act->requested_by) . '</td>';
                echo '<td>' . $plainActivity . '</td>';
                echo '<td>' . $plainResult . '</td>';
                echo '<td>' . $plainConstraint . '</td>';
                echo "<td class=\"text-center\"><span class=\"badge {$badgeClass}\">" . $statusLabel . '</span></td>';
                echo '<td class="text-center">' . htmlspecialchars($act->verifier ? $act->verifier->full_name : '-') . '</td>';
                echo '<td>' . ($commentsList ?: '-') . '</td>';
                echo '<td class="text-center">' . htmlspecialchars($act->created_at->translatedFormat('d/m/Y H:i')) . '</td>';
                echo '</tr>';
            }

            if ($activities->isEmpty()) {
                echo '<tr><td colspan="12" class="text-center" style="padding: 20px; color: #64748b;">Tidak ada catatan aktivitas pada periode bulan ini.</td></tr>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '</body>';
            echo '</html>';
        }, 200, $headers);
    }
}
