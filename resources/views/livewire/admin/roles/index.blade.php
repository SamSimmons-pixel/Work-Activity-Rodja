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

    <!-- Page Header -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <h2 class="text-xl font-bold text-slate-900 tracking-tight">Manajemen Peran & Izin Akses (RBAC)</h2>
        <p class="text-xs text-slate-500 mt-0.5">Atur hak akses granular berbasis peran (Role-Based Access Control) sesuai kebijakan keamanan modul internal.</p>
    </div>

    <!-- Roles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($roles as $role)
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex items-start justify-between gap-2 pb-3 border-b border-slate-100">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                <span>{{ $role->name }}</span>
                                @if($role->name === 'Administrator')
                                    <span class="text-2xs font-semibold px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-100">Superadmin</span>
                                @endif
                            </h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $role->description ?? 'Tidak ada deskripsi.' }}</p>
                        </div>
                        <span class="shrink-0 text-xs font-semibold px-2.5 py-1 rounded-xl bg-slate-100 text-slate-700">
                            {{ $role->users->count() }} Pengguna
                        </span>
                    </div>

                    <!-- Assigned Permissions -->
                    <div class="pt-3 space-y-2">
                        <span class="text-2xs font-bold uppercase tracking-wider text-slate-400">Daftar Izin Aktif ({{ $role->name === 'Administrator' ? 'Semua Izin' : $role->permissions->count() }}):</span>
                        <div class="flex flex-wrap gap-1.5">
                            @if($role->name === 'Administrator')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-100">
                                    ✓ Akses Penuh ke Seluruh Fitur (Superadmin Bypass)
                                </span>
                            @else
                                @forelse($role->permissions as $p)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-700">
                                        {{ $p->name }}
                                    </span>
                                @empty
                                    <span class="text-xs text-slate-400 italic">Belum ada izin yang diberikan.</span>
                                @endforelse
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-end">
                    <button
                        wire:click="openEditModal({{ $role->id }})"
                        type="button"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-xl transition cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                        <span>Kelola Izin Akses</span>
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal Edit Permissions -->
    @if($isEditModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/75 backdrop-blur-xs transition-opacity" wire:click="closeEditModal"></div>

            <div class="relative bg-white w-full max-w-2xl rounded-2xl shadow-2xl ring-1 ring-slate-900/10 text-left overflow-hidden flex flex-col max-h-[88vh] z-10">
                <!-- Modal Header -->
                <div class="px-5 py-3.5 border-b border-slate-200 flex items-center justify-between shrink-0 bg-slate-50/70">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">
                            Kelola Izin Akses Peran: {{ $role_name }}
                        </h3>
                        <p class="text-xs text-slate-500">Pilih izin yang diizinkan untuk peran ini.</p>
                    </div>
                    <button type="button" wire:click="closeEditModal" class="text-slate-400 hover:text-slate-600 rounded-lg p-1.5 hover:bg-slate-200/50 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-5 space-y-4 overflow-y-auto flex-1 text-slate-800">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Deskripsi Peran
                        </label>
                        <textarea
                            wire:model="role_description"
                            rows="2"
                            class="block w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600"
                        ></textarea>
                    </div>

                    <!-- Grouped Permissions Checkboxes -->
                    <div class="space-y-4 pt-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                            Daftar Izin Granular
                        </label>

                        @foreach($groupedPermissions as $groupName => $permissions)
                            <div class="bg-slate-50/80 rounded-xl p-3.5 border border-slate-200/80 space-y-2.5">
                                <h4 class="text-xs font-bold text-indigo-700 uppercase tracking-wide flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    <span>Modul: {{ strtoupper($groupName) }}</span>
                                </h4>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach($permissions as $perm)
                                        <label class="flex items-start gap-2 p-2 rounded-lg bg-white border border-slate-200 hover:border-indigo-300 cursor-pointer transition select-none">
                                            <input
                                                type="checkbox"
                                                wire:model="selectedPermissions"
                                                value="{{ $perm->id }}"
                                                class="mt-0.5 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 cursor-pointer"
                                            />
                                            <div class="text-xs">
                                                <p class="font-semibold text-slate-800">{{ $perm->name }}</p>
                                                <p class="text-2xs text-slate-500">{{ $perm->description }}</p>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2.5 shrink-0">
                    <button type="button" wire:click="closeEditModal" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-200/70 rounded-xl transition cursor-pointer">
                        Batal
                    </button>
                    <button type="button" wire:click="updateRolePermissions" class="px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-xs transition cursor-pointer">
                        Simpan Izin Akses
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
