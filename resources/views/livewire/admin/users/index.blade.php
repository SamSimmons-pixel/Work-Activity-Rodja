<div class="space-y-6">
    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('message') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 text-lg leading-none cursor-pointer">&times;</button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-medium flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-rose-600 hover:text-rose-800 text-lg leading-none cursor-pointer">&times;</button>
        </div>
    @endif

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Manajemen Pengguna</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola akun karyawan, jabatan, divisi, penugasan atasan, dan peran akses sistem.</p>
        </div>

        <button
            wire:click="openCreateModal"
            type="button"
            class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-xl text-sm font-semibold shadow-xs hover:shadow shadow-indigo-200 transition cursor-pointer"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            <span>Tambah Pengguna</span>
        </button>
    </div>

    <!-- Filter & Search Controls -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Search -->
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Cari nama atau username..."
                    class="block w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm transition focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600"
                />
            </div>

            <!-- Division Filter -->
            <div>
                <select
                    wire:model.live="filterDivision"
                    class="block w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600"
                >
                    <option value="">Semua Divisi</option>
                    @foreach($divisions as $div)
                        <option value="{{ $div->id }}">{{ $div->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Role Filter -->
            <div>
                <select
                    wire:model.live="filterRole"
                    class="block w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600"
                >
                    <option value="">Semua Peran</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->id }}">{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <select
                    wire:model.live="filterStatus"
                    class="block w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600"
                >
                    <option value="">Semua Status</option>
                    <option value="Active">Aktif</option>
                    <option value="Inactive">Nonaktif</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50/70 text-slate-600 font-semibold text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Nama & Username</th>
                        <th class="px-5 py-3.5">Divisi & Posisi</th>
                        <th class="px-5 py-3.5">Atasan Langsung</th>
                        <th class="px-5 py-3.5">Peran (Role)</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-800">
                    @forelse($users as $u)
                        <tr class="hover:bg-slate-50/60 transition">
                            <!-- Name & Username -->
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-900">{{ $u->full_name }}</div>
                                <div class="text-xs text-slate-500">&#64;{{ $u->username }}</div>
                            </td>

                            <!-- Division & Position -->
                            <td class="px-5 py-3.5">
                                <div class="font-medium text-slate-800">{{ $u->position?->name ?? '-' }}</div>
                                <div class="text-xs text-slate-500">{{ $u->division?->name ?? 'Belum ditentukan' }}</div>
                            </td>

                            <!-- Supervisor -->
                            <td class="px-5 py-3.5">
                                @if($u->supervisor)
                                    <div class="font-medium text-indigo-700">{{ $u->supervisor->full_name }}</div>
                                    <div class="text-xs text-slate-500">{{ $u->supervisor->position?->name ?? 'Atasan' }}</div>
                                @else
                                    <span class="text-xs text-slate-400 italic">Tidak ada (Pucuk Pimpinan)</span>
                                @endif
                            </td>

                            <!-- Role -->
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    {{ $u->role?->name ?? 'Tanpa Peran' }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $u->status === 'Active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                    {{ $u->status === 'Active' ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-3.5 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <!-- Edit -->
                                    <button
                                        wire:click="openEditModal({{ $u->id }})"
                                        type="button"
                                        title="Ubah data pengguna"
                                        class="p-1.5 rounded-lg text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 transition cursor-pointer"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>

                                    <!-- Reset Password -->
                                    <button
                                        wire:click="openResetPasswordModal({{ $u->id }})"
                                        type="button"
                                        title="Reset kata sandi"
                                        class="p-1.5 rounded-lg text-slate-600 hover:text-amber-600 hover:bg-amber-50 transition cursor-pointer"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                        </svg>
                                    </button>

                                    <!-- Toggle Status (Activate / Deactivate) -->
                                    @if($u->id !== auth()->id())
                                        <button
                                            wire:click="toggleStatus({{ $u->id }})"
                                            type="button"
                                            title="{{ $u->status === 'Active' ? 'Nonaktifkan akun' : 'Aktifkan akun' }}"
                                            class="p-1.5 rounded-lg {{ $u->status === 'Active' ? 'text-slate-600 hover:text-rose-600 hover:bg-rose-50' : 'text-slate-600 hover:text-emerald-600 hover:bg-emerald-50' }} transition cursor-pointer"
                                        >
                                            @if($u->status === 'Active')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            @endif
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-slate-500">
                                Tidak ada data pengguna yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-5 py-3.5 border-t border-slate-200">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Add / Edit User -->
    @if($isFormModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/75 backdrop-blur-xs transition-opacity" wire:click="closeFormModal"></div>

            <div class="relative bg-white w-full max-w-xl rounded-2xl shadow-2xl ring-1 ring-slate-900/10 text-left overflow-hidden flex flex-col max-h-[88vh] z-10">
                <!-- Header -->
                <div class="px-5 py-3.5 border-b border-slate-200 flex items-center justify-between shrink-0 bg-slate-50/70">
                    <h3 class="text-base font-bold text-slate-900">
                        {{ $formMode === 'create' ? 'Tambah Pengguna Baru' : 'Ubah Data Pengguna' }}
                    </h3>
                    <button type="button" wire:click="closeFormModal" class="text-slate-400 hover:text-slate-600 rounded-lg p-1.5 hover:bg-slate-200/50 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Body -->
                <div class="p-5 space-y-4 overflow-y-auto flex-1 text-slate-800">
                    <!-- Full Name & Username -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                Nama Lengkap <span class="text-rose-500">*</span>
                            </label>
                            <input
                                wire:model="full_name"
                                type="text"
                                required
                                placeholder="Contoh: Ahmad Fauzi"
                                class="block w-full px-3 py-2 bg-slate-50 border @error('full_name') border-rose-400 @else border-slate-300 @enderror rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600"
                            />
                            @error('full_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                Username <span class="text-rose-500">*</span>
                            </label>
                            <input
                                wire:model="username"
                                type="text"
                                required
                                placeholder="Contoh: ahmad"
                                class="block w-full px-3 py-2 bg-slate-50 border @error('username') border-rose-400 @else border-slate-300 @enderror rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600"
                            />
                            @error('username') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Password (Only on Create or Optional on Edit) -->
                    @if($formMode === 'create')
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                    Kata Sandi <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    wire:model="password"
                                    type="password"
                                    required
                                    placeholder="Minimal 6 karakter"
                                    class="block w-full px-3 py-2 bg-slate-50 border @error('password') border-rose-400 @else border-slate-300 @enderror rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600"
                                />
                                @error('password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                    Konfirmasi Kata Sandi <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    wire:model="password_confirmation"
                                    type="password"
                                    required
                                    placeholder="Ulangi kata sandi"
                                    class="block w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600"
                                />
                            </div>
                        </div>
                    @else
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                            <p class="text-xs text-slate-500">
                                <em>Gunakan fitur <strong>Reset Password</strong> jika hanya ingin memperbarui kata sandi pengguna ini.</em>
                            </p>
                        </div>
                    @endif

                    <!-- Division & Position -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                Divisi
                            </label>
                            <select
                                wire:model.live="division_id"
                                class="block w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600"
                            >
                                <option value="">-- Pilih Divisi --</option>
                                @foreach($divisions as $div)
                                    <option value="{{ $div->id }}">{{ $div->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                Jabatan / Posisi
                            </label>
                            <select
                                wire:model="position_id"
                                class="block w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600"
                            >
                                <option value="">-- Pilih Posisi --</option>
                                @foreach($positions as $pos)
                                    <option value="{{ $pos->id }}">{{ $pos->name }} ({{ $pos->division?->name }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Supervisor & Role -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                Atasan Langsung (Supervisor)
                            </label>
                            <select
                                wire:model="supervisor_id"
                                class="block w-full px-3 py-2 bg-slate-50 border @error('supervisor_id') border-rose-400 @else border-slate-300 @enderror rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600"
                            >
                                <option value="">-- Tidak Ada Atasan --</option>
                                @foreach($supervisors as $s)
                                    @if(!$editingUserId || $s->id !== $editingUserId)
                                        <option value="{{ $s->id }}">{{ $s->full_name }} ({{ $s->position?->name ?? 'Staff' }})</option>
                                    @endif
                                @endforeach
                            </select>
                            @error('supervisor_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                Peran (Role) <span class="text-rose-500">*</span>
                            </label>
                            <select
                                wire:model="role_id"
                                required
                                class="block w-full px-3 py-2 bg-slate-50 border @error('role_id') border-rose-400 @else border-slate-300 @enderror rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600"
                            >
                                <option value="">-- Pilih Peran --</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r->id }}">{{ $r->name }}</option>
                                @endforeach
                            </select>
                            @error('role_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Status Akun <span class="text-rose-500">*</span>
                        </label>
                        <select
                            wire:model="status"
                            class="block w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600"
                        >
                            <option value="Active">Aktif (Dapat Masuk ke Sistem)</option>
                            <option value="Inactive">Nonaktif (Akses Masuk Dinonaktifkan)</option>
                        </select>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2.5 shrink-0">
                    <button
                        type="button"
                        wire:click="closeFormModal"
                        class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-200/70 rounded-xl transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="{{ $formMode === 'create' ? 'createUser' : 'updateUser' }}"
                        class="px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-xs transition cursor-pointer"
                    >
                        {{ $formMode === 'create' ? 'Simpan Pengguna' : 'Perbarui Pengguna' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Reset Password -->
    @if($isResetPasswordModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/75 backdrop-blur-xs transition-opacity" wire:click="closeResetPasswordModal"></div>

            <div class="relative bg-white rounded-2xl max-w-md w-full p-5 text-left shadow-2xl ring-1 ring-slate-900/10 overflow-hidden z-10">
                <div class="flex items-center gap-3.5 mb-4">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Reset Kata Sandi</h3>
                        <p class="text-xs text-slate-500">{{ $resetUserName }}</p>
                    </div>
                </div>

                <div class="space-y-3 mb-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Kata Sandi Baru <span class="text-rose-500">*</span>
                        </label>
                        <input
                            wire:model="new_password"
                            type="password"
                            required
                            placeholder="Minimal 6 karakter"
                            class="block w-full px-3 py-2 bg-slate-50 border @error('new_password') border-rose-400 @else border-slate-300 @enderror rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600"
                        />
                        @error('new_password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Konfirmasi Kata Sandi Baru <span class="text-rose-500">*</span>
                        </label>
                        <input
                            wire:model="new_password_confirmation"
                            type="password"
                            required
                            placeholder="Ulangi kata sandi baru"
                            class="block w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600"
                        />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2">
                    <button
                        type="button"
                        wire:click="closeResetPasswordModal"
                        class="px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="resetPassword"
                        class="px-3.5 py-1.5 text-xs font-semibold text-white bg-amber-600 hover:bg-amber-700 active:bg-amber-800 rounded-xl transition shadow-xs cursor-pointer"
                    >
                        Simpan Kata Sandi
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
