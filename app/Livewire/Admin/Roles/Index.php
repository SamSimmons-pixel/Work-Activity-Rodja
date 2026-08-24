<?php

namespace App\Livewire\Admin\Roles;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Peran & Izin')]
class Index extends Component
{
    public bool $isEditModalOpen = false;
    public ?int $editingRoleId = null;
    public string $role_name = '';
    public string $role_description = '';
    public array $selectedPermissions = [];

    public function mount(): void
    {
        Gate::authorize('role.manage');
    }

    public function openEditModal(int $id): void
    {
        Gate::authorize('role.manage');

        $role = Role::with('permissions')->findOrFail($id);

        $this->editingRoleId = $role->id;
        $this->role_name = $role->name;
        $this->role_description = $role->description ?? '';
        $this->selectedPermissions = $role->permissions->pluck('id')->map(fn($id) => (int)$id)->toArray();

        $this->isEditModalOpen = true;
    }

    public function closeEditModal(): void
    {
        $this->isEditModalOpen = false;
        $this->reset(['editingRoleId', 'role_name', 'role_description', 'selectedPermissions']);
        $this->resetValidation();
    }

    public function updateRolePermissions(): void
    {
        Gate::authorize('role.manage');

        $role = Role::findOrFail($this->editingRoleId);

        $this->validate([
            'role_description' => ['nullable', 'string', 'max:500'],
            'selectedPermissions' => ['array'],
        ]);

        $role->update([
            'description' => $this->role_description,
        ]);

        // Sync permissions
        $role->permissions()->sync($this->selectedPermissions);

        $this->closeEditModal();
        session()->flash('message', "Hak akses untuk peran {$role->name} berhasil diperbarui.");
    }

    public function render()
    {
        $roles = Role::with(['permissions', 'users'])->orderBy('id')->get();
        $allPermissions = Permission::orderBy('name')->get();

        // Group permissions by category prefix (activity.*, user.*, etc.)
        $groupedPermissions = $allPermissions->groupBy(function ($perm) {
            $parts = explode('.', $perm->name);
            return $parts[0] ?? 'other';
        });

        return view('livewire.admin.roles.index', [
            'roles' => $roles,
            'allPermissions' => $allPermissions,
            'groupedPermissions' => $groupedPermissions,
        ]);
    }
}
