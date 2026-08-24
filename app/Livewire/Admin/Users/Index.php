<?php

namespace App\Livewire\Admin\Users;

use App\Models\Division;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Manajemen Pengguna')]
class Index extends Component
{
    use WithPagination;

    // Search and filters
    public string $search = '';
    public string $filterDivision = '';
    public string $filterRole = '';
    public string $filterStatus = '';

    // Modal Add / Edit User state
    public bool $isFormModalOpen = false;
    public string $formMode = 'create'; // 'create' or 'edit'
    public ?int $editingUserId = null;

    // Form fields
    public string $full_name = '';
    public string $username = '';
    public string $password = '';
    public string $password_confirmation = '';
    public ?int $division_id = null;
    public ?int $position_id = null;
    public ?int $supervisor_id = null;
    public ?int $role_id = null;
    public string $status = 'Active';

    // Reset Password Modal state
    public bool $isResetPasswordModalOpen = false;
    public ?int $resetUserId = null;
    public string $resetUserName = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    public function mount(): void
    {
        Gate::authorize('user.manage');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterDivision(): void
    {
        $this->resetPage();
    }

    public function updatingFilterRole(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    /**
     * Open Modal for Creating a New User.
     */
    public function openCreateModal(): void
    {
        Gate::authorize('user.manage');

        $this->resetForm();
        $this->formMode = 'create';
        $this->status = 'Active';

        $defaultRole = Role::where('name', 'Employee')->first();
        $this->role_id = $defaultRole?->id;

        $this->isFormModalOpen = true;
    }

    /**
     * Open Modal for Editing an Existing User.
     */
    public function openEditModal(int $id): void
    {
        Gate::authorize('user.manage');

        $user = User::findOrFail($id);

        $this->resetForm();
        $this->formMode = 'edit';
        $this->editingUserId = $user->id;
        $this->full_name = $user->full_name;
        $this->username = $user->username;
        $this->division_id = $user->division_id;
        $this->position_id = $user->position_id;
        $this->supervisor_id = $user->supervisor_id;
        $this->role_id = $user->role_id;
        $this->status = $user->status;

        $this->isFormModalOpen = true;
    }

    public function closeFormModal(): void
    {
        $this->isFormModalOpen = false;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset([
            'editingUserId',
            'full_name',
            'username',
            'password',
            'password_confirmation',
            'division_id',
            'position_id',
            'supervisor_id',
            'role_id',
            'status',
        ]);
        $this->resetValidation();
    }

    /**
     * Create New User.
     */
    public function createUser(): void
    {
        Gate::authorize('user.manage');

        $this->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'max:100', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'division_id' => ['nullable', 'exists:divisions,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'supervisor_id' => ['nullable', 'exists:users,id'],
            'role_id' => ['required', 'exists:roles,id'],
            'status' => ['required', 'in:Active,Inactive'],
        ], [
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username ini sudah digunakan.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'role_id.required' => 'Peran (Role) wajib dipilih.',
            'status.required' => 'Status akun wajib ditentukan.',
        ]);

        User::create([
            'full_name' => $this->full_name,
            'username' => $this->username,
            'password' => Hash::make($this->password),
            'division_id' => $this->division_id ?: null,
            'position_id' => $this->position_id ?: null,
            'supervisor_id' => $this->supervisor_id ?: null,
            'role_id' => $this->role_id,
            'status' => $this->status,
        ]);

        $this->closeFormModal();
        session()->flash('message', 'Pengguna baru berhasil ditambahkan.');
    }

    /**
     * Update Existing User.
     */
    public function updateUser(): void
    {
        Gate::authorize('user.manage');

        $user = User::findOrFail($this->editingUserId);

        $rules = [
            'full_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'max:100', Rule::unique('users')->ignore($user->id)],
            'division_id' => ['nullable', 'exists:divisions,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'supervisor_id' => ['nullable', 'exists:users,id', 'not_in:' . $user->id],
            'role_id' => ['required', 'exists:roles,id'],
            'status' => ['required', 'in:Active,Inactive'],
        ];

        if (!empty($this->password)) {
            $rules['password'] = ['required', 'string', 'min:6', 'confirmed'];
        }

        $this->validate($rules, [
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username ini sudah digunakan.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'supervisor_id.not_in' => 'Pengguna tidak dapat menjadi atasan bagi dirinya sendiri.',
            'role_id.required' => 'Peran (Role) wajib dipilih.',
        ]);

        $data = [
            'full_name' => $this->full_name,
            'username' => $this->username,
            'division_id' => $this->division_id ?: null,
            'position_id' => $this->position_id ?: null,
            'supervisor_id' => $this->supervisor_id ?: null,
            'role_id' => $this->role_id,
            'status' => $this->status,
        ];

        if (!empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        $user->update($data);

        $this->closeFormModal();
        session()->flash('message', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Open Modal for Resetting Password.
     */
    public function openResetPasswordModal(int $id): void
    {
        Gate::authorize('user.manage');

        $user = User::findOrFail($id);

        $this->resetUserId = $user->id;
        $this->resetUserName = $user->full_name . " (@{$user->username})";
        $this->new_password = '';
        $this->new_password_confirmation = '';
        $this->resetValidation();

        $this->isResetPasswordModalOpen = true;
    }

    public function closeResetPasswordModal(): void
    {
        $this->isResetPasswordModalOpen = false;
        $this->resetUserId = null;
    }

    /**
     * Submit Reset Password.
     */
    public function resetPassword(): void
    {
        Gate::authorize('user.manage');

        $this->validate([
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'new_password.required' => 'Kata sandi baru wajib diisi.',
            'new_password.min' => 'Kata sandi baru minimal 6 karakter.',
            'new_password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $user = User::findOrFail($this->resetUserId);
        $user->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->closeResetPasswordModal();
        session()->flash('message', "Kata sandi untuk {$user->full_name} berhasil direset.");
    }

    /**
     * Toggle User Status (Activate / Deactivate).
     */
    public function toggleStatus(int $id): void
    {
        Gate::authorize('user.manage');

        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            session()->flash('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
            return;
        }

        $newStatus = $user->status === 'Active' ? 'Inactive' : 'Active';
        $user->update(['status' => $newStatus]);

        $statusText = $newStatus === 'Active' ? 'diaktifkan' : 'dinonaktifkan';
        session()->flash('message', "Akun {$user->full_name} berhasil {$statusText}.");
    }

    public function render()
    {
        Gate::authorize('user.manage');

        // Fetch users query
        $query = User::with(['role', 'division', 'position', 'supervisor'])
            ->when(!empty($this->search), function ($q) {
                $term = $this->search;
                $q->where(function ($sub) use ($term) {
                    $sub->where('full_name', 'like', "%{$term}%")
                        ->orWhere('username', 'like', "%{$term}%");
                });
            })
            ->when(!empty($this->filterDivision), function ($q) {
                $q->where('division_id', $this->filterDivision);
            })
            ->when(!empty($this->filterRole), function ($q) {
                $q->where('role_id', $this->filterRole);
            })
            ->when(!empty($this->filterStatus), function ($q) {
                $q->where('status', $this->filterStatus);
            })
            ->orderBy('full_name', 'asc');

        $users = $query->paginate(15);

        // Supporting data for dropdowns
        $divisions = Division::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
        $supervisors = User::where('status', 'Active')->orderBy('full_name')->get();

        // Filter positions based on selected division if present
        $positionsQuery = Position::where('status', 'Active');
        if ($this->division_id) {
            $positionsQuery->where('division_id', $this->division_id);
        }
        $positions = $positionsQuery->orderBy('name')->get();

        return view('livewire.admin.users.index', [
            'users' => $users,
            'divisions' => $divisions,
            'positions' => $positions,
            'roles' => $roles,
            'supervisors' => $supervisors,
        ]);
    }
}
