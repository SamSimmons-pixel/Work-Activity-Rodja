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
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Struktur Organisasi</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola data divisi perusahaan, kepala divisi, dan jabatan struktural karyawan.</p>
        </div>

        <div>
            @if($activeTab === 'divisions')
                <button
                    wire:click="openCreateDivisionModal"
                    type="button"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-xl text-sm font-semibold shadow-xs hover:shadow shadow-indigo-200 transition cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Divisi</span>
                </button>
            @else
                <button
                    wire:click="openCreatePositionModal"
                    type="button"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-xl text-sm font-semibold shadow-xs hover:shadow shadow-indigo-200 transition cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Posisi</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
        <button
            wire:click="setTab('divisions')"
            type="button"
            class="px-4 py-2 rounded-xl text-sm font-semibold transition cursor-pointer {{ $activeTab === 'divisions' ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}"
        >
            Divisi Kerja ({{ $divisions->count() }})
        </button>

        <button
            wire:click="setTab('positions')"
            type="button"
            class="px-4 py-2 rounded-xl text-sm font-semibold transition cursor-pointer {{ $activeTab === 'positions' ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}"
        >
            Posisi / Jabatan ({{ $positions->count() }})
        </button>
    </div>

    <!-- TAB 1: DIVISIONS -->
    @if($activeTab === 'divisions')
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50/70 text-slate-600 font-semibold text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">Nama Divisi</th>
                            <th class="px-5 py-3.5">Kepala Divisi</th>
                            <th class="px-5 py-3.5 text-center">Jumlah Jabatan</th>
                            <th class="px-5 py-3.5 text-center">Jumlah Anggota</th>
                            <th class="px-5 py-3.5 text-center">Status</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-800">
                        @forelse($divisions as $div)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-5 py-3.5 font-bold text-slate-900">
                                    {{ $div->name }}
                                </td>
                                <td class="px-5 py-3.5">
                                    @if($div->headUser)
                                        <span class="font-semibold text-indigo-700">{{ $div->headUser->full_name }}</span>
                                        <span class="text-xs text-slate-500 block">&#64;{{ $div->headUser->username }}</span>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Belum ditentukan</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-center font-medium">
                                    {{ $div->positions->count() }} Posisi
                                </td>
                                <td class="px-5 py-3.5 text-center font-medium">
                                    {{ $div->users->count() }} Orang
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $div->status === 'Active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                        {{ $div->status === 'Active' ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button
                                            wire:click="openEditDivisionModal({{ $div->id }})"
                                            type="button"
                                            title="Ubah divisi"
                                            class="p-1.5 rounded-lg text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 transition cursor-pointer"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button
                                            wire:click="toggleDivisionStatus({{ $div->id }})"
                                            type="button"
                                            title="{{ $div->status === 'Active' ? 'Nonaktifkan divisi' : 'Aktifkan divisi' }}"
                                            class="p-1.5 rounded-lg {{ $div->status === 'Active' ? 'text-slate-600 hover:text-rose-600 hover:bg-rose-50' : 'text-slate-600 hover:text-emerald-600 hover:bg-emerald-50' }} transition cursor-pointer"
                                        >
                                            @if($div->status === 'Active')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            @endif
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-slate-500">
                                    Belum ada divisi yang dibuat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- TAB 2: POSITIONS -->
    @if($activeTab === 'positions')
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50/70 text-slate-600 font-semibold text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">Nama Jabatan / Posisi</th>
                            <th class="px-5 py-3.5">Divisi</th>
                            <th class="px-5 py-3.5 text-center">Tingkat / Level</th>
                            <th class="px-5 py-3.5 text-center">Jumlah Pemegang</th>
                            <th class="px-5 py-3.5 text-center">Status</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-800">
                        @forelse($positions as $pos)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-5 py-3.5 font-bold text-slate-900">
                                    {{ $pos->name }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                        {{ $pos->division?->name ?? 'Tanpa Divisi' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-center text-xs font-medium text-slate-600">
                                    Level {{ $pos->level ?? '-' }}
                                </td>
                                <td class="px-5 py-3.5 text-center font-medium">
                                    {{ $pos->users->count() }} Orang
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $pos->status === 'Active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                        {{ $pos->status === 'Active' ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button
                                            wire:click="openEditPositionModal({{ $pos->id }})"
                                            type="button"
                                            title="Ubah posisi"
                                            class="p-1.5 rounded-lg text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 transition cursor-pointer"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button
                                            wire:click="togglePositionStatus({{ $pos->id }})"
                                            type="button"
                                            title="{{ $pos->status === 'Active' ? 'Nonaktifkan posisi' : 'Aktifkan posisi' }}"
                                            class="p-1.5 rounded-lg {{ $pos->status === 'Active' ? 'text-slate-600 hover:text-rose-600 hover:bg-rose-50' : 'text-slate-600 hover:text-emerald-600 hover:bg-emerald-50' }} transition cursor-pointer"
                                        >
                                            @if($pos->status === 'Active')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            @endif
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-slate-500">
                                    Belum ada posisi/jabatan yang dibuat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Modal Add / Edit Division -->
    @if($isDivisionModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/75 backdrop-blur-xs transition-opacity" wire:click="closeDivisionModal"></div>

            <div class="relative bg-white w-full max-w-md rounded-2xl shadow-2xl ring-1 ring-slate-900/10 text-left overflow-hidden z-10">
                <div class="px-5 py-3.5 border-b border-slate-200 flex items-center justify-between shrink-0 bg-slate-50/70">
                    <h3 class="text-base font-bold text-slate-900">
                        {{ $divisionFormMode === 'create' ? 'Tambah Divisi Baru' : 'Ubah Data Divisi' }}
                    </h3>
                    <button type="button" wire:click="closeDivisionModal" class="text-slate-400 hover:text-slate-600 rounded-lg p-1.5 hover:bg-slate-200/50 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-5 space-y-4 text-slate-800">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Nama Divisi <span class="text-rose-500">*</span>
                        </label>
                        <input
                            wire:model="division_name"
                            type="text"
                            required
                            placeholder="Contoh: Information Technology"
                            class="block w-full px-3 py-2 bg-slate-50 border @error('division_name') border-rose-400 @else border-slate-300 @enderror rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600"
                        />
                        @error('division_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Kepala Divisi
                        </label>
                        <select
                            wire:model="division_head_user_id"
                            class="block w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600"
                        >
                            <option value="">-- Belum Ditentukan --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->full_name }} ({{ $u->position?->name ?? 'Staff' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Status Divisi <span class="text-rose-500">*</span>
                        </label>
                        <select
                            wire:model="division_status"
                            class="block w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600"
                        >
                            <option value="Active">Aktif</option>
                            <option value="Inactive">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2.5 shrink-0">
                    <button type="button" wire:click="closeDivisionModal" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-200/70 rounded-xl transition cursor-pointer">
                        Batal
                    </button>
                    <button type="button" wire:click="saveDivision" class="px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-xs transition cursor-pointer">
                        {{ $divisionFormMode === 'create' ? 'Simpan Divisi' : 'Perbarui Divisi' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Add / Edit Position -->
    @if($isPositionModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/75 backdrop-blur-xs transition-opacity" wire:click="closePositionModal"></div>

            <div class="relative bg-white w-full max-w-md rounded-2xl shadow-2xl ring-1 ring-slate-900/10 text-left overflow-hidden z-10">
                <div class="px-5 py-3.5 border-b border-slate-200 flex items-center justify-between shrink-0 bg-slate-50/70">
                    <h3 class="text-base font-bold text-slate-900">
                        {{ $positionFormMode === 'create' ? 'Tambah Posisi / Jabatan' : 'Ubah Data Posisi' }}
                    </h3>
                    <button type="button" wire:click="closePositionModal" class="text-slate-400 hover:text-slate-600 rounded-lg p-1.5 hover:bg-slate-200/50 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-5 space-y-4 text-slate-800">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Nama Posisi / Jabatan <span class="text-rose-500">*</span>
                        </label>
                        <input
                            wire:model="position_name"
                            type="text"
                            required
                            placeholder="Contoh: Developer"
                            class="block w-full px-3 py-2 bg-slate-50 border @error('position_name') border-rose-400 @else border-slate-300 @enderror rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600"
                        />
                        @error('position_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Divisi <span class="text-rose-500">*</span>
                        </label>
                        <select
                            wire:model="position_division_id"
                            required
                            class="block w-full px-3 py-2 bg-slate-50 border @error('position_division_id') border-rose-400 @else border-slate-300 @enderror rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600"
                        >
                            <option value="">-- Pilih Divisi --</option>
                            @foreach($divisions as $div)
                                <option value="{{ $div->id }}">{{ $div->name }}</option>
                            @endforeach
                        </select>
                        @error('position_division_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Tingkat / Level Jabatan
                        </label>
                        <select
                            wire:model="position_level"
                            class="block w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600"
                        >
                            <option value="1">Level 1 - Manager / Head</option>
                            <option value="2">Level 2 - Supervisor / Lead</option>
                            <option value="3">Level 3 - Staff / Specialist</option>
                            <option value="4">Level 4 - Support / Junior</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Status Posisi <span class="text-rose-500">*</span>
                        </label>
                        <select
                            wire:model="position_status"
                            class="block w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600"
                        >
                            <option value="Active">Aktif</option>
                            <option value="Inactive">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2.5 shrink-0">
                    <button type="button" wire:click="closePositionModal" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-200/70 rounded-xl transition cursor-pointer">
                        Batal
                    </button>
                    <button type="button" wire:click="savePosition" class="px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-xs transition cursor-pointer">
                        {{ $positionFormMode === 'create' ? 'Simpan Posisi' : 'Perbarui Posisi' }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
