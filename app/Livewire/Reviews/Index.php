<?php

namespace App\Livewire\Reviews;

use App\Models\PerformanceReview;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Review Kinerja')]
class Index extends Component
{
    public bool $isFormModalOpen = false;
    public string $formMode = 'create';
    public ?int $editingReviewId = null;

    // Form fields
    public ?int $user_id = null;
    public string $period_type = 'Quarterly';
    public string $period_label = '';
    public string $start_date = '';
    public string $end_date = '';
    public string $rating = 'Sangat Baik (A)';
    public string $summary = '';
    public string $strengths = '';
    public string $improvements = '';
    public string $status = 'Final';

    public array $ratingOptions = [
        'Sangat Baik (A)' => 'Sangat Baik (A) - Melebihi target dan ekspektasi',
        'Baik (B)' => 'Baik (B) - Memenuhi seluruh target dengan konsisten',
        'Cukup (C)' => 'Cukup (C) - Memenuhi target dasar dengan sedikit kendala',
        'Perlu Peningkatan (D)' => 'Perlu Peningkatan (D) - Terdapat kendala signifikan',
        'Kurang (E)' => 'Kurang (E) - Belum memenuhi ekspektasi minimum',
    ];

    public function mount(): void
    {
        $now = now();
        $this->start_date = $now->copy()->startOfQuarter()->toDateString();
        $this->end_date = $now->copy()->endOfQuarter()->toDateString();
        $this->period_label = 'Kuartal ' . $now->quarter . ' ' . $now->year;
    }

    public function openCreateModal(): void
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        $subordinates = $currentUser->subordinates;
        if ($subordinates->isEmpty() && !$currentUser->hasRole('Administrator')) {
            session()->flash('error', 'Hanya Atasan Langsung atau Administrator yang dapat membuat review kinerja.');
            return;
        }

        $this->resetForm();
        $this->formMode = 'create';
        $this->user_id = $subordinates->first()?->id;
        $this->isFormModalOpen = true;
    }

    public function openEditModal(int $id): void
    {
        $review = PerformanceReview::findOrFail($id);

        /** @var User $currentUser */
        $currentUser = Auth::user();

        // Check if author or admin
        if ($review->reviewer_id !== $currentUser->id && !$currentUser->hasRole('Administrator')) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit review kinerja ini.');
        }

        $this->resetForm();
        $this->formMode = 'edit';
        $this->editingReviewId = $review->id;
        $this->user_id = $review->user_id;
        $this->period_type = $review->period_type;
        $this->period_label = $review->period_label;
        $this->start_date = $review->start_date->toDateString();
        $this->end_date = $review->end_date->toDateString();
        $this->rating = $review->rating;
        $this->summary = $review->summary;
        $this->strengths = $review->strengths ?? '';
        $this->improvements = $review->improvements ?? '';
        $this->status = $review->status;

        $this->isFormModalOpen = true;
    }

    public function closeFormModal(): void
    {
        $this->isFormModalOpen = false;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $now = now();
        $this->reset([
            'editingReviewId',
            'user_id',
            'summary',
            'strengths',
            'improvements',
        ]);
        $this->period_type = 'Quarterly';
        $this->period_label = 'Kuartal ' . $now->quarter . ' ' . $now->year;
        $this->start_date = $now->copy()->startOfQuarter()->toDateString();
        $this->end_date = $now->copy()->endOfQuarter()->toDateString();
        $this->rating = 'Sangat Baik (A)';
        $this->status = 'Final';
        $this->resetValidation();
    }

    public function saveReview(): void
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        $this->validate([
            'user_id' => ['required', 'exists:users,id'],
            'period_type' => ['required', 'string'],
            'period_label' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'rating' => ['required', 'string'],
            'summary' => ['required', 'string'],
            'strengths' => ['nullable', 'string'],
            'improvements' => ['nullable', 'string'],
            'status' => ['required', 'in:Draft,Final'],
        ], [
            'user_id.required' => 'Pilih karyawan yang dievaluasi.',
            'period_label.required' => 'Label periode evaluasi wajib diisi.',
            'summary.required' => 'Ringkasan evaluasi kinerja wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal akhir harus sama atau setelah tanggal mulai.',
        ]);

        // Authorization check: User must be supervisor of the target employee or Administrator
        $canReview = $currentUser->hasRole('Administrator') || in_array($this->user_id, $currentUser->getSubordinateIds());
        if (!$canReview) {
            abort(403, 'Anda hanya dapat memberikan review untuk bawahan langsung Anda.');
        }

        if ($this->formMode === 'create') {
            PerformanceReview::create([
                'user_id' => $this->user_id,
                'reviewer_id' => $currentUser->id,
                'period_type' => $this->period_type,
                'period_label' => $this->period_label,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'rating' => $this->rating,
                'summary' => $this->summary,
                'strengths' => $this->strengths ?: null,
                'improvements' => $this->improvements ?: null,
                'status' => $this->status,
            ]);
            session()->flash('message', 'Review kinerja berhasil disimpan.');
        } else {
            $review = PerformanceReview::findOrFail($this->editingReviewId);
            $review->update([
                'user_id' => $this->user_id,
                'period_type' => $this->period_type,
                'period_label' => $this->period_label,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'rating' => $this->rating,
                'summary' => $this->summary,
                'strengths' => $this->strengths ?: null,
                'improvements' => $this->improvements ?: null,
                'status' => $this->status,
            ]);
            session()->flash('message', 'Review kinerja berhasil diperbarui.');
        }

        $this->closeFormModal();
    }

    public function render()
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        // If supervisor / admin: get reviews created + reviews received
        $subordinates = $currentUser->hasRole('Administrator')
            ? User::where('id', '!=', $currentUser->id)->where('status', 'Active')->get()
            : $currentUser->subordinates()->where('status', 'Active')->get();

        $reviewsGiven = PerformanceReview::with(['user.position', 'user.division', 'reviewer'])
            ->when(!$currentUser->hasRole('Administrator'), function ($q) use ($currentUser) {
                $q->where('reviewer_id', $currentUser->id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $reviewsReceived = PerformanceReview::with(['reviewer.position', 'reviewer.division'])
            ->where('user_id', $currentUser->id)
            ->where('status', 'Final')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.reviews.index', [
            'currentUser' => $currentUser,
            'subordinates' => $subordinates,
            'reviewsGiven' => $reviewsGiven,
            'reviewsReceived' => $reviewsReceived,
        ]);
    }
}
