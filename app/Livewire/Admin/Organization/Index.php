<?php

namespace App\Livewire\Admin\Organization;

use App\Models\Division;
use App\Models\Position;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Divisi & Posisi')]
class Index extends Component
{
    public string $activeTab = 'divisions'; // 'divisions' or 'positions'

    // Division Form State
    public bool $isDivisionModalOpen = false;
    public string $divisionFormMode = 'create';
    public ?int $editingDivisionId = null;
    public string $division_name = '';
    public ?int $division_head_user_id = null;
    public string $division_status = 'Active';

    // Position Form State
    public bool $isPositionModalOpen = false;
    public string $positionFormMode = 'create';
    public ?int $editingPositionId = null;
    public string $position_name = '';
    public ?int $position_division_id = null;
    public ?string $position_level = '3';
    public string $position_status = 'Active';

    public function mount(): void
    {
        Gate::authorize('division.manage');
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    /**
     * DIVISION ACTIONS
     */
    public function openCreateDivisionModal(): void
    {
        $this->resetDivisionForm();
        $this->divisionFormMode = 'create';
        $this->division_status = 'Active';
        $this->isDivisionModalOpen = true;
    }

    public function openEditDivisionModal(int $id): void
    {
        $division = Division::findOrFail($id);

        $this->resetDivisionForm();
        $this->divisionFormMode = 'edit';
        $this->editingDivisionId = $division->id;
        $this->division_name = $division->name;
        $this->division_head_user_id = $division->head_user_id;
        $this->division_status = $division->status;

        $this->isDivisionModalOpen = true;
    }

    public function closeDivisionModal(): void
    {
        $this->isDivisionModalOpen = false;
        $this->resetDivisionForm();
    }

    public function resetDivisionForm(): void
    {
        $this->reset([
            'editingDivisionId',
            'division_name',
            'division_head_user_id',
            'division_status',
        ]);
        $this->resetValidation();
    }

    public function saveDivision(): void
    {
        Gate::authorize('division.manage');

        $this->validate([
            'division_name' => ['required', 'string', 'max:255'],
            'division_head_user_id' => ['nullable', 'exists:users,id'],
            'division_status' => ['required', 'in:Active,Inactive'],
        ], [
            'division_name.required' => 'Nama divisi wajib diisi.',
            'division_status.required' => 'Status divisi wajib ditentukan.',
        ]);

        if ($this->divisionFormMode === 'create') {
            Division::create([
                'name' => $this->division_name,
                'head_user_id' => $this->division_head_user_id ?: null,
                'status' => $this->division_status,
            ]);
            session()->flash('message', 'Divisi baru berhasil ditambahkan.');
        } else {
            $division = Division::findOrFail($this->editingDivisionId);
            $division->update([
                'name' => $this->division_name,
                'head_user_id' => $this->division_head_user_id ?: null,
                'status' => $this->division_status,
            ]);
            session()->flash('message', 'Data divisi berhasil diperbarui.');
        }

        $this->closeDivisionModal();
    }

    public function toggleDivisionStatus(int $id): void
    {
        Gate::authorize('division.manage');

        $division = Division::findOrFail($id);
        $newStatus = $division->status === 'Active' ? 'Inactive' : 'Active';
        $division->update(['status' => $newStatus]);

        session()->flash('message', "Status divisi {$division->name} berhasil diubah.");
    }

    /**
     * POSITION ACTIONS
     */
    public function openCreatePositionModal(): void
    {
        $this->resetPositionForm();
        $this->positionFormMode = 'create';
        $this->position_status = 'Active';

        $firstDivision = Division::first();
        $this->position_division_id = $firstDivision?->id;
        $this->isPositionModalOpen = true;
    }

    public function openEditPositionModal(int $id): void
    {
        $position = Position::findOrFail($id);

        $this->resetPositionForm();
        $this->positionFormMode = 'edit';
        $this->editingPositionId = $position->id;
        $this->position_name = $position->name;
        $this->position_division_id = $position->division_id;
        $this->position_level = (string) $position->level;
        $this->position_status = $position->status;

        $this->isPositionModalOpen = true;
    }

    public function closePositionModal(): void
    {
        $this->isPositionModalOpen = false;
        $this->resetPositionForm();
    }

    public function resetPositionForm(): void
    {
        $this->reset([
            'editingPositionId',
            'position_name',
            'position_division_id',
            'position_level',
            'position_status',
        ]);
        $this->resetValidation();
    }

    public function savePosition(): void
    {
        Gate::authorize('position.manage');

        $this->validate([
            'position_name' => ['required', 'string', 'max:255'],
            'position_division_id' => ['required', 'exists:divisions,id'],
            'position_level' => ['nullable', 'string'],
            'position_status' => ['required', 'in:Active,Inactive'],
        ], [
            'position_name.required' => 'Nama posisi/jabatan wajib diisi.',
            'position_division_id.required' => 'Divisi wajib dipilih.',
            'position_status.required' => 'Status posisi wajib ditentukan.',
        ]);

        if ($this->positionFormMode === 'create') {
            Position::create([
                'name' => $this->position_name,
                'division_id' => $this->position_division_id,
                'level' => $this->position_level,
                'status' => $this->position_status,
            ]);
            session()->flash('message', 'Posisi/jabatan baru berhasil ditambahkan.');
        } else {
            $position = Position::findOrFail($this->editingPositionId);
            $position->update([
                'name' => $this->position_name,
                'division_id' => $this->position_division_id,
                'level' => $this->position_level,
                'status' => $this->position_status,
            ]);
            session()->flash('message', 'Data posisi/jabatan berhasil diperbarui.');
        }

        $this->closePositionModal();
    }

    public function togglePositionStatus(int $id): void
    {
        Gate::authorize('position.manage');

        $position = Position::findOrFail($id);
        $newStatus = $position->status === 'Active' ? 'Inactive' : 'Active';
        $position->update(['status' => $newStatus]);

        session()->flash('message', "Status posisi {$position->name} berhasil diubah.");
    }

    public function render()
    {
        $divisions = Division::with(['headUser', 'positions', 'users'])->orderBy('name')->get();
        $positions = Position::with(['division', 'users'])->orderBy('division_id')->orderBy('level')->get();
        $users = User::where('status', 'Active')->orderBy('full_name')->get();

        return view('livewire.admin.organization.index', [
            'divisions' => $divisions,
            'positions' => $positions,
            'users' => $users,
        ]);
    }
}
