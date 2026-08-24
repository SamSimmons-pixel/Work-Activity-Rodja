<?php

namespace App\Livewire\Reports;

use App\Models\Activity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

use Livewire\Attributes\Url;

#[Layout('layouts.auth')] // uses standalone layout without top navbar for clean print
#[Title('Laporan Aktivitas Bulanan')]
class MonthlyReport extends Component
{
    #[Url]
    public ?int $user_id = null;

    #[Url]
    public ?int $year = null;

    #[Url]
    public ?int $month = null;

    public int $userId;

    public function mount(): void
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        $targetUserId = (int) ($this->user_id ?: request()->query('user_id', $currentUser->id));

        // Authorize access
        if ($targetUserId !== $currentUser->id) {
            $isAuthorized = $currentUser->hasRole('Administrator') ||
                $currentUser->hasPermission('activity.read.all') ||
                ($currentUser->hasPermission('activity.read.subordinate') && in_array($targetUserId, $currentUser->getSubordinateIds()));

            if (!$isAuthorized) {
                abort(403, 'Anda tidak memiliki izin untuk melihat laporan karyawan ini.');
            }
        }

        $this->userId = $targetUserId;
        $this->year = (int) ($this->year ?: request()->query('year', now()->format('Y')));
        $this->month = (int) ($this->month ?: request()->query('month', now()->format('n')));
    }

    public function render()
    {
        $targetUser = User::with(['position', 'division', 'supervisor.position'])->findOrFail($this->userId);

        $activities = Activity::with(['creator', 'verifier', 'comments'])
            ->where('user_id', $this->userId)
            ->forMonth($this->year, $this->month)
            ->orderBy('activity_date', 'asc')
            ->get();

        $periodDate = Carbon::createFromDate($this->year, $this->month, 1);

        $totalActivities = $activities->count();
        $completedActivities = $activities->whereIn('status', ['Submitted', 'Reviewed', 'Verified'])->count();
        $hasConstraintCount = $activities->filter(fn ($a) => !empty($a->constraint))->count();

        return view('livewire.reports.monthly-report', [
            'targetUser' => $targetUser,
            'activities' => $activities,
            'periodDate' => $periodDate,
            'totalActivities' => $totalActivities,
            'completedActivities' => $completedActivities,
            'hasConstraintCount' => $hasConstraintCount,
        ]);
    }
}
