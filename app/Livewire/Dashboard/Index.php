<?php

namespace App\Livewire\Dashboard;

use App\Events\ActivityCommentPosted;
use App\Models\Activity;
use App\Models\ActivityComment;
use App\Models\User;
use App\Notifications\ActivityCommentedNotification;
use App\Notifications\ActivitySubmittedNotification;
use App\Notifications\ActivityVerifiedNotification;
use App\Services\HtmlSanitizer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Aktivitas Kerja')]
class Index extends Component
{
    use WithFileUploads;

    // View mode: 'timeline' or 'analytics'
    public string $viewMode = 'timeline';

    // Month navigation state
    public int $selectedYear;
    public int $selectedMonth;

    // Employee selector state
    public string $selectedUserId = 'myself';

    // Search and Category filter
    public string $search = '';
    public string $selectedCategory = 'all';

    // Modal state
    public bool $isFormModalOpen = false;
    public string $formMode = 'create'; // 'create' or 'edit'
    public ?int $editingActivityId = null;

    public bool $isDeleteModalOpen = false;
    public ?int $deletingActivityId = null;

    // Form fields
    public string $activity_date = '';
    public string $category = 'Development';
    public string $tags_input = '';
    public string $activity = '';
    public string $requested_by_option = 'Inisiatif Sendiri';
    public string $requested_by_custom = '';
    public string $result = '';
    public ?string $constraint = '';
    public string $status = 'Submitted';

    // Attachment fields
    public $attachment = null;
    public ?string $existingAttachmentName = null;
    public ?string $existingAttachmentPath = null;
    public bool $removeAttachment = false;

    // Comments state
    public ?int $commentingActivityId = null;
    public string $newCommentText = '';

    /**
     * Standard category options as per specification.
     */
    public array $categoryOptions = [
        'Development',
        'Project',
        'Infrastructure',
        'Support',
        'Administration',
        'Security',
        'Maintenance',
        'Lainnya',
    ];

    /**
     * Standard requested-by options as per specification.
     */
    public array $requestedByOptions = [
        'Inisiatif Sendiri',
        'Atasan Langsung',
        'Direktur',
        'Divisi Keuangan',
        'Divisi HRD',
        'Lainnya',
    ];

    public function mount(): void
    {
        $now = now();
        $this->selectedYear = (int) $now->format('Y');
        $this->selectedMonth = (int) $now->format('n');
        $this->activity_date = $now->toDateString();
    }

    /**
     * Dynamic Echo listeners.
     * NOTE: Notification bell ($unreadCount, dropdown list) is handled by the
     * separate NotificationMenu Livewire component which has its own listener —
     * no need to refresh the entire dashboard for that.
     *
     * Comment listener: subscribe to the active activity's private channel ONLY
     * when a comment box is open. Automatically removed when box is closed.
     */
    public function getListeners(): array
    {
        $listeners = [];

        // Real-time comment updates — only subscribe when a comment box is open
        if ($this->commentingActivityId) {
            $listeners["echo-private:activity.{$this->commentingActivityId},.ActivityCommentPosted"] = '$refresh';
        }

        return $listeners;
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
    }

    /**
     * Month Navigation Methods
     */
    public function prevMonth(): void
    {
        $date = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->subMonth();
        $this->selectedYear = $date->year;
        $this->selectedMonth = $date->month;
    }

    public function nextMonth(): void
    {
        $date = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->addMonth();
        $this->selectedYear = $date->year;
        $this->selectedMonth = $date->month;
    }

    public function currentMonth(): void
    {
        $now = now();
        $this->selectedYear = (int) $now->format('Y');
        $this->selectedMonth = (int) $now->format('n');
    }

    public function setMonth(int $year, int $month): void
    {
        $this->selectedYear = $year;
        $this->selectedMonth = $month;
    }

    /**
     * Form Validation Rules in Indonesian.
     */
    protected function rules(): array
    {
        return [
            'activity_date' => ['required', 'date'],
            'category' => ['required', 'string'],
            'activity' => ['required', 'string'],
            'requested_by_option' => ['required', 'string'],
            'requested_by_custom' => ['required_if:requested_by_option,Lainnya', 'nullable', 'string', 'max:255'],
            'result' => ['required', 'string'],
            'constraint' => ['nullable', 'string'],
            'status' => ['required', 'in:Draft,Submitted,Reviewed,Verified,Completed'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,webp,pdf'],
        ];
    }

    protected function messages(): array
    {
        return [
            'activity_date.required' => 'Tanggal aktivitas wajib diisi.',
            'activity_date.date' => 'Format tanggal aktivitas tidak valid.',
            'category.required' => 'Kategori pekerjaan wajib dipilih.',
            'activity.required' => 'Deskripsi aktivitas wajib diisi.',
            'requested_by_option.required' => 'Sumber permintaan wajib dipilih.',
            'requested_by_custom.required_if' => 'Mohon sebutkan sumber permintaan lainnya.',
            'result.required' => 'Hasil / luaran pekerjaan wajib diisi.',
            'status.required' => 'Status aktivitas wajib ditentukan.',
            'attachment.max' => 'Ukuran file lampiran maksimal 5MB.',
            'attachment.mimes' => 'Format lampiran harus berupa gambar (JPG, PNG, WebP) atau dokumen PDF.',
        ];
    }

    /**
     * Open Modal for Creating Activity.
     */
    public function openCreateModal(): void
    {
        Gate::authorize('activity.create');

        $this->resetForm();
        $this->formMode = 'create';
        $this->activity_date = now()->toDateString();
        $this->category = 'Development';
        $this->status = 'Submitted';
        $this->requested_by_option = 'Inisiatif Sendiri';
        $this->isFormModalOpen = true;

        $this->dispatch('init-form-editors', [
            'activity' => '',
            'result' => '',
            'constraint' => '',
        ]);
    }

    /**
     * Open Modal for Editing Activity.
     */
    public function openEditModal(int $id): void
    {
        $activity = Activity::findOrFail($id);

        Gate::authorize('update-activity', $activity);

        $this->resetForm();
        $this->formMode = 'edit';
        $this->editingActivityId = $activity->id;
        $this->activity_date = $activity->activity_date->toDateString();
        $this->category = $activity->category ?? 'Development';
        $this->tags_input = is_array($activity->tags) ? implode(', ', $activity->tags) : '';
        $this->activity = $activity->activity;
        $this->result = $activity->result;
        $this->constraint = $activity->constraint ?? '';
        $this->status = $activity->status;
        $this->existingAttachmentName = $activity->attachment_name;
        $this->existingAttachmentPath = $activity->attachment_path;

        if (in_array($activity->requested_by, $this->requestedByOptions)) {
            $this->requested_by_option = $activity->requested_by;
            $this->requested_by_custom = '';
        } else {
            $this->requested_by_option = 'Lainnya';
            $this->requested_by_custom = $activity->requested_by;
        }

        $this->isFormModalOpen = true;

        $this->dispatch('init-form-editors', [
            'activity' => $this->activity,
            'result' => $this->result,
            'constraint' => $this->constraint,
        ]);
    }

    public function closeFormModal(): void
    {
        $this->isFormModalOpen = false;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset([
            'editingActivityId',
            'activity_date',
            'category',
            'tags_input',
            'activity',
            'requested_by_option',
            'requested_by_custom',
            'result',
            'constraint',
            'status',
            'attachment',
            'existingAttachmentName',
            'existingAttachmentPath',
            'removeAttachment',
        ]);
        $this->resetValidation();
    }

    protected function parseTags(): ?array
    {
        if (empty($this->tags_input)) {
            return null;
        }

        $tags = array_map('trim', explode(',', $this->tags_input));
        return array_values(array_filter($tags));
    }

    protected function getEffectiveRequestedBy(): string
    {
        return $this->requested_by_option === 'Lainnya'
            ? trim($this->requested_by_custom)
            : $this->requested_by_option;
    }

    public function saveActivity(): void
    {
        Gate::authorize('activity.create');

        $this->validate();

        $user = Auth::user();
        $attachmentPath = null;
        $attachmentName = null;

        if ($this->attachment) {
            $attachmentPath = $this->attachment->store('activities/attachments', 'public');
            $attachmentName = $this->attachment->getClientOriginalName();
        }

        $activity = Activity::create([
            'user_id' => $user->id,
            'activity_date' => $this->activity_date,
            'category' => $this->category,
            'tags' => $this->parseTags(),
            'activity' => HtmlSanitizer::clean($this->activity),
            'requested_by' => $this->getEffectiveRequestedBy(),
            'result' => HtmlSanitizer::clean($this->result),
            'constraint' => !empty($this->constraint) ? HtmlSanitizer::clean($this->constraint) : null,
            'status' => $this->status,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        // Notify supervisor if user has one
        if ($user->supervisor_id && $user->supervisor) {
            $user->supervisor->notify(new ActivitySubmittedNotification($activity, $user));
        }

        $this->closeFormModal();
        session()->flash('message', 'Aktivitas berhasil disimpan.');
    }

    public function updateActivity(): void
    {
        $activity = Activity::findOrFail($this->editingActivityId);

        Gate::authorize('update-activity', $activity);

        $this->validate();

        $user = Auth::user();
        $attachmentPath = $activity->attachment_path;
        $attachmentName = $activity->attachment_name;

        if ($this->removeAttachment) {
            if ($activity->attachment_path) {
                Storage::disk('public')->delete($activity->attachment_path);
            }
            $attachmentPath = null;
            $attachmentName = null;
        }

        if ($this->attachment) {
            if ($activity->attachment_path) {
                Storage::disk('public')->delete($activity->attachment_path);
            }
            $attachmentPath = $this->attachment->store('activities/attachments', 'public');
            $attachmentName = $this->attachment->getClientOriginalName();
        }

        $activity->update([
            'activity_date' => $this->activity_date,
            'category' => $this->category,
            'tags' => $this->parseTags(),
            'activity' => HtmlSanitizer::clean($this->activity),
            'requested_by' => $this->getEffectiveRequestedBy(),
            'result' => HtmlSanitizer::clean($this->result),
            'constraint' => !empty($this->constraint) ? HtmlSanitizer::clean($this->constraint) : null,
            'status' => $this->status,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'updated_by' => $user->id,
        ]);

        $this->closeFormModal();
        session()->flash('message', 'Aktivitas berhasil diperbarui.');
    }

    public function confirmDelete(int $id): void
    {
        $activity = Activity::findOrFail($id);

        Gate::authorize('delete-activity', $activity);

        $this->deletingActivityId = $activity->id;
        $this->isDeleteModalOpen = true;
    }

    public function closeDeleteModal(): void
    {
        $this->isDeleteModalOpen = false;
        $this->deletingActivityId = null;
    }

    public function deleteActivity(): void
    {
        if (!$this->deletingActivityId) {
            return;
        }

        $activity = Activity::findOrFail($this->deletingActivityId);

        Gate::authorize('delete-activity', $activity);

        $user = Auth::user();
        $activity->update(['deleted_by' => $user->id]);
        $activity->delete();

        $this->closeDeleteModal();
        session()->flash('message', 'Aktivitas berhasil dihapus.');
    }

    public function addComment(int $activityId): void
    {
        $activity = Activity::findOrFail($activityId);
        Gate::authorize('view-activity', $activity);

        $currentUser = Auth::user();
        $commentText = trim($this->newCommentText);

        $comment = ActivityComment::create([
            'activity_id' => $activity->id,
            'user_id'     => $currentUser->id,
            'comment'     => $commentText,
        ]);

        // Load relationships so broadcastWith() can serialize them
        $comment->load('user.role');

        // Broadcast to the other user viewing this activity's comment section
        broadcast(new ActivityCommentPosted($comment))->toOthers();

        // Notify via queue (bell icon) — activity owner or supervisor
        if ($activity->user_id !== $currentUser->id && $activity->user) {
            $activity->user->notify(new ActivityCommentedNotification($activity, $currentUser, $commentText));
        } elseif ($activity->user_id === $currentUser->id && $currentUser->supervisor_id && $currentUser->supervisor) {
            $currentUser->supervisor->notify(new ActivityCommentedNotification($activity, $currentUser, $commentText));
        }

        // Keep comment box open so sender sees their own new comment immediately
        $this->newCommentText = '';
        session()->flash('message', 'Komentar catatan berhasil ditambahkan.');
    }

    public function toggleCommentBox(int $activityId): void
    {
        if ($this->commentingActivityId === $activityId) {
            $this->commentingActivityId = null;
        } else {
            $this->commentingActivityId = $activityId;
            $this->newCommentText = '';
        }
    }

    public function verifyActivity(int $activityId, string $newStatus): void
    {
        $activity = Activity::findOrFail($activityId);

        /** @var User $currentUser */
        $currentUser = Auth::user();

        $canVerify = $currentUser->hasRole('Administrator') ||
            ($currentUser->hasPermission('activity.read.subordinate') && in_array($activity->user_id, $currentUser->getSubordinateIds()));

        if (!$canVerify) {
            abort(403, 'Hanya Atasan Langsung atau Administrator yang dapat memverifikasi aktivitas ini.');
        }

        if (!in_array($newStatus, ['Reviewed', 'Verified', 'Submitted'])) {
            return;
        }

        $activity->update([
            'status' => $newStatus,
            'verified_at' => now(),
            'verified_by' => $currentUser->id,
        ]);

        // Notify activity owner
        if ($activity->user_id !== $currentUser->id && $activity->user) {
            $activity->user->notify(new ActivityVerifiedNotification($activity, $currentUser, $newStatus));
        }

        $statusText = $newStatus === 'Verified' ? 'Terverifikasi' : ($newStatus === 'Reviewed' ? 'Ditinjau' : 'Terkirim');
        session()->flash('message', "Aktivitas berhasil ditandai sebagai {$statusText}.");
    }

    protected function getEffectiveUserId(): int
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if ($this->selectedUserId === 'myself' || empty($this->selectedUserId)) {
            return $currentUser->id;
        }

        $targetId = (int) $this->selectedUserId;

        if ($currentUser->hasRole('Administrator') || $currentUser->hasPermission('activity.read.all')) {
            return $targetId;
        }

        if ($currentUser->hasPermission('activity.read.subordinate') && in_array($targetId, $currentUser->getSubordinateIds())) {
            return $targetId;
        }

        abort(403, 'Anda tidak memiliki izin untuk melihat aktivitas karyawan ini.');
    }

    public function render()
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $effectiveUserId = $this->getEffectiveUserId();
        $selectedUser = User::with(['position', 'division'])->find($effectiveUserId);

        // Fetch activities for selected month & user
        $allMonthActivities = Activity::with(['creator', 'updater', 'verifier', 'comments.user.role'])
            ->where('user_id', $effectiveUserId)
            ->forMonth($this->selectedYear, $this->selectedMonth)
            ->orderBy('activity_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Apply Search & Category filters for timeline
        $filteredActivities = $allMonthActivities;
        if (!empty($this->search)) {
            $term = strtolower($this->search);
            $filteredActivities = $filteredActivities->filter(function ($act) use ($term) {
                return str_contains(strtolower($act->activity), $term)
                    || str_contains(strtolower($act->result), $term)
                    || str_contains(strtolower($act->constraint ?? ''), $term)
                    || str_contains(strtolower($act->requested_by), $term)
                    || str_contains(strtolower($act->category ?? ''), $term);
            });
        }

        if ($this->selectedCategory !== 'all' && !empty($this->selectedCategory)) {
            $filteredActivities = $filteredActivities->where('category', $this->selectedCategory);
        }

        // Summary Metrics
        $totalActivities = $allMonthActivities->count();
        $completedActivities = $allMonthActivities->whereIn('status', ['Submitted', 'Reviewed', 'Verified'])->count();
        $openIssuesCount = $allMonthActivities->filter(fn ($act) => !empty($act->constraint))->count();

        // Group filtered activities by date
        $groupedActivities = $filteredActivities->groupBy(function ($activity) {
            return $activity->activity_date->translatedFormat('l, d F Y');
        });

        // Analytics Computations
        $categoryBreakdown = $allMonthActivities->groupBy(fn ($a) => $a->category ?: 'Umum')->map->count();
        $requestSourceBreakdown = $allMonthActivities->groupBy('requested_by')->map->count();
        $dailyTrend = $allMonthActivities->groupBy(fn ($a) => (int) $a->activity_date->format('j'))->map->count();

        $subordinates = $currentUser->hasPermission('activity.read.subordinate') || $currentUser->hasRole(['Supervisor', 'Administrator', 'Management'])
            ? $currentUser->subordinates()->with(['position', 'division'])->get()
            : collect();

        $selectedMonthDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1);
        $daysInMonth = $selectedMonthDate->daysInMonth;

        return view('livewire.dashboard.index', [
            'currentUser' => $currentUser,
            'selectedUser' => $selectedUser,
            'groupedActivities' => $groupedActivities,
            'totalActivities' => $totalActivities,
            'completedActivities' => $completedActivities,
            'openIssuesCount' => $openIssuesCount,
            'subordinates' => $subordinates,
            'selectedMonthDate' => $selectedMonthDate,
            'categoryBreakdown' => $categoryBreakdown,
            'requestSourceBreakdown' => $requestSourceBreakdown,
            'dailyTrend' => $dailyTrend,
            'daysInMonth' => $daysInMonth,
        ]);
    }
}
