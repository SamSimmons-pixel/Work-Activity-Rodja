<div class="space-y-6">
    <!-- Flash Notification Messages -->
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

    <!-- Top Action & Filter Bar (Sections 7, 8, 28) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <!-- Month Navigator (Section 7) -->
            <div class="flex items-center gap-3">
                <div class="inline-flex items-center bg-slate-100/90 rounded-xl p-1 shadow-inner">
                    <button
                        wire:click="prevMonth"
                        type="button"
                        class="p-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-white transition cursor-pointer"
                        title="Bulan sebelumnya"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <span class="px-3.5 py-1 text-sm font-bold text-slate-800 select-none min-w-[140px] text-center">
                        {{ $selectedMonthDate->translatedFormat('F Y') }}
                    </span>

                    <button
                        wire:click="nextMonth"
                        type="button"
                        class="p-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-white transition cursor-pointer"
                        title="Bulan berikutnya"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

                @if($selectedYear !== (int) now()->format('Y') || $selectedMonth !== (int) now()->format('n'))
                    <button
                        wire:click="currentMonth"
                        type="button"
                        class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition cursor-pointer"
                    >
                        Bulan Berjalan
                    </button>
                @endif
            </div>

            <!-- Employee Selector, Export Buttons & Add Button -->
            <div class="flex flex-wrap items-center gap-2.5">
                @if($subordinates->isNotEmpty() || $currentUser->hasRole(['Supervisor', 'Administrator', 'Management']))
                    <div class="flex items-center gap-1.5">
                        <label for="employeeSelector" class="text-xs font-bold text-slate-500 uppercase tracking-wider">Karyawan:</label>
                        <select
                            id="employeeSelector"
                            wire:model.live="selectedUserId"
                            class="bg-slate-50 border border-slate-300 text-slate-800 text-sm font-medium rounded-xl px-3 py-1.5 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600 shadow-xs outline-none cursor-pointer"
                        >
                            <option value="myself">Diri Sendiri ({{ $currentUser->full_name }})</option>
                            @foreach($subordinates as $sub)
                                <option value="{{ $sub->id }}">{{ $sub->full_name }} ({{ $sub->position?->name ?? 'Staff' }})</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- Excel / CSV Export Button (Section 40.8) -->
                <a
                    href="{{ route('activities.export-excel', ['user_id' => $selectedUser->id, 'year' => $selectedYear, 'month' => $selectedMonth]) }}"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-emerald-700 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 rounded-xl transition cursor-pointer border border-emerald-200"
                    title="Unduh Rekap Spreadsheet Excel / CSV"
                >
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Excel</span>
                </a>

                <!-- Print / Export Monthly Report Button (Section 40.8) -->
                <a
                    href="{{ route('reports.monthly', ['user_id' => $selectedUser->id, 'year' => $selectedYear, 'month' => $selectedMonth]) }}"
                    target="_blank"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-xl transition cursor-pointer border border-slate-200"
                    title="Cetak atau Ekspor Laporan Bulanan"
                >
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Cetak PDF</span>
                </a>

                @can('activity.create')
                    @if($selectedUserId === 'myself' || $selectedUserId == $currentUser->id)
                        <button
                            wire:click="openCreateModal"
                            type="button"
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-xl text-sm font-semibold shadow-xs hover:shadow shadow-indigo-200 transition cursor-pointer"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Tambah Aktivitas</span>
                        </button>
                    @endif
                @endcan
            </div>
        </div>

        <!-- Search & Category Filter Bar (Sections 27, 40.6) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4 pt-4 border-t border-slate-100">
            <div class="sm:col-span-2 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Cari aktivitas, hasil, kendala, kategori, atau tag..."
                    class="block w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600 transition"
                />
            </div>

            <div>
                <select
                    wire:model.live="selectedCategory"
                    class="block w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600 transition cursor-pointer"
                >
                    <option value="all">Semua Kategori</option>
                    @foreach($categoryOptions as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Active Filter Info Banner (When viewing subordinate) -->
    @if($selectedUser && $selectedUser->id !== $currentUser->id)
        <div class="bg-indigo-50/80 border border-indigo-100 rounded-2xl p-4 flex items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white font-bold flex items-center justify-center text-sm shadow-xs">
                    {{ strtoupper(substr($selectedUser->full_name, 0, 2)) }}
                </div>
                <div>
                    <span class="text-indigo-900 font-bold text-sm block">{{ $selectedUser->full_name }}</span>
                    <span class="text-indigo-600">{{ $selectedUser->position?->name ?? 'Staff' }} &bull; Divisi {{ $selectedUser->division?->name ?? '-' }}</span>
                </div>
            </div>
            <button
                wire:click="$set('selectedUserId', 'myself')"
                type="button"
                class="text-xs font-semibold text-indigo-700 hover:text-indigo-900 bg-white px-3 py-1.5 rounded-lg border border-indigo-200 shadow-2xs hover:bg-indigo-50 transition cursor-pointer"
            >
                Kembali ke Aktivitas Saya
            </button>
        </div>
    @endif

    <!-- Dashboard Summary Cards (Section 26) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Total Activities -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Aktivitas</p>
                <h3 class="text-2xl font-black text-slate-900 mt-1">{{ $totalActivities }}</h3>
                <p class="text-2xs text-slate-400 mt-0.5">{{ $selectedMonthDate->translatedFormat('F Y') }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
            </div>
        </div>

        <!-- Submitted & Verified Activities -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Terkirim & Terverifikasi</p>
                <h3 class="text-2xl font-black text-emerald-600 mt-1">{{ $completedActivities }}</h3>
                <p class="text-2xs text-slate-400 mt-0.5">Aktivitas diserahkan</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- Open Issues / Constraints -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Terdapat Kendala</p>
                <h3 class="text-2xl font-black text-amber-600 mt-1">{{ $openIssuesCount }}</h3>
                <p class="text-2xs text-slate-400 mt-0.5">Perlu perhatian atasan</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- View Mode Switcher Tabs (Linimasa vs Analisis) -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
        <button
            wire:click="setViewMode('timeline')"
            type="button"
            class="px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer flex items-center gap-2 {{ $viewMode === 'timeline' ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
            <span>Linimasa Aktivitas (Timeline)</span>
        </button>

        <button
            wire:click="setViewMode('analytics')"
            type="button"
            class="px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer flex items-center gap-2 {{ $viewMode === 'analytics' ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <span>Analisis & Metrik Produktivitas</span>
        </button>
    </div>

    <!-- TAB 1: TIMELINE VIEW -->
    @if($viewMode === 'timeline')
        @if($groupedActivities->isEmpty())
            <!-- Empty State (Section 29) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-12 text-center shadow-xs space-y-4">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800">Tidak ada aktivitas yang tercatat untuk kriteria ini.</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                        @if(!empty($search) || $selectedCategory !== 'all')
                            Coba sesuaikan kata kunci pencarian atau filter kategori yang dipilih.
                        @else
                            Mulai dokumentasikan pekerjaan dan pencapaian Anda pada bulan {{ $selectedMonthDate->translatedFormat('F Y') }}.
                        @endif
                    </p>
                </div>

                @can('activity.create')
                    @if(($selectedUserId === 'myself' || $selectedUserId == $currentUser->id) && empty($search) && $selectedCategory === 'all')
                        <div class="pt-2">
                            <button
                                wire:click="openCreateModal"
                                type="button"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-xs hover:shadow shadow-indigo-200 transition cursor-pointer"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span>Tambah Aktivitas Pertama</span>
                            </button>
                        </div>
                    @endif
                @endcan
            </div>
        @else
            <!-- Timeline Grouped by Date (Section 9) -->
            <div class="space-y-8">
                @foreach($groupedActivities as $dateString => $items)
                    <div class="space-y-4">
                        <!-- Date Header Badge -->
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-200/80 text-slate-800 font-bold text-xs rounded-lg uppercase tracking-wide">
                                <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                {{ $dateString }}
                            </span>
                            <div class="flex-1 h-px bg-slate-200"></div>
                            <span class="text-xs font-semibold text-slate-400">{{ $items->count() }} Aktivitas</span>
                        </div>

                        <!-- Cards for this date -->
                        <div class="grid grid-cols-1 gap-4">
                            @foreach($items as $act)
                                <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs hover:border-slate-300 transition space-y-4">
                                    <!-- Card Header: Category, Requested By, Tags & Actions -->
                                    <div class="flex flex-wrap items-start justify-between gap-2 border-b border-slate-100 pb-3">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <!-- Category Badge -->
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-600 text-white shadow-2xs">
                                                {{ $act->category ?? 'Umum' }}
                                            </span>

                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                                <span class="text-slate-400 font-normal">Pemohon:</span> {{ $act->requested_by }}
                                            </span>

                                            <!-- Tags if any (Section 40.6) -->
                                            @if(is_array($act->tags) && count($act->tags) > 0)
                                                @foreach($act->tags as $tag)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-3xs font-medium bg-slate-100 text-slate-600">
                                                        #{{ $tag }}
                                                    </span>
                                                @endforeach
                                            @endif

                                            <!-- Status Badge -->
                                            @if($act->status === 'Verified')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 border border-indigo-200">
                                                    <svg class="w-3 h-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                    </svg>
                                                    <span>Terverifikasi</span>
                                                </span>
                                            @elseif($act->status === 'Reviewed')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                                    <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    <span>Ditinjau</span>
                                                </span>
                                            @elseif($act->status === 'Submitted')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                                    Terkirim
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                                    Draf
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Actions: Edit, Delete & Supervisor Verification -->
                                        <div class="flex items-center gap-2">
                                            <!-- Supervisor / Admin Verification Dropdown/Action -->
                                            @if(($currentUser->hasRole('Administrator') || in_array($act->user_id, $currentUser->getSubordinateIds())) && $act->user_id !== $currentUser->id)
                                                <div class="inline-flex items-center rounded-lg bg-slate-100 p-0.5 text-xs font-medium">
                                                    <button
                                                        wire:click="verifyActivity({{ $act->id }}, 'Reviewed')"
                                                        type="button"
                                                        title="Tandai Ditinjau"
                                                        class="px-2 py-1 rounded-md transition cursor-pointer {{ $act->status === 'Reviewed' ? 'bg-white text-blue-700 font-bold shadow-2xs' : 'text-slate-600 hover:text-blue-700' }}"
                                                    >
                                                        Ditinjau
                                                    </button>
                                                    <button
                                                        wire:click="verifyActivity({{ $act->id }}, 'Verified')"
                                                        type="button"
                                                        title="Tandai Terverifikasi"
                                                        class="px-2 py-1 rounded-md transition cursor-pointer {{ $act->status === 'Verified' ? 'bg-white text-indigo-700 font-bold shadow-2xs' : 'text-slate-600 hover:text-indigo-700' }}"
                                                    >
                                                        Verifikasi
                                                    </button>
                                                </div>
                                            @endif

                                            @can('update-activity', $act)
                                                <button
                                                    wire:click="openEditModal({{ $act->id }})"
                                                    type="button"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition cursor-pointer"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    <span>Ubah</span>
                                                </button>
                                            @endcan

                                            @can('delete-activity', $act)
                                                <button
                                                    wire:click="confirmDelete({{ $act->id }})"
                                                    type="button"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-rose-600 hover:text-rose-800 hover:bg-rose-50 rounded-lg transition cursor-pointer"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    <span>Hapus</span>
                                                </button>
                                            @endcan
                                        </div>
                                    </div>

                                    <!-- Activity Content -->
                                    <div>
                                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Aktivitas / Pekerjaan</h4>
                                        <div class="text-sm text-slate-800 leading-relaxed prose-content">
                                            {!! $act->activity !!}
                                        </div>
                                    </div>

                                    <!-- Result / Outcome -->
                                    <div class="bg-slate-50/80 rounded-xl p-3.5 border border-slate-100">
                                        <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-1 flex items-center gap-1">
                                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>Hasil / Luaran Pekerjaan</span>
                                        </h4>
                                        <div class="text-sm text-slate-700 leading-relaxed prose-content">
                                            {!! $act->result !!}
                                        </div>
                                    </div>

                                    <!-- Constraint / Issue (Optional) -->
                                    @if(!empty($act->constraint))
                                        <div class="bg-amber-50/70 rounded-xl p-3.5 border border-amber-200/70">
                                            <h4 class="text-xs font-bold text-amber-800 uppercase tracking-wider mb-1 flex items-center gap-1">
                                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                <span>Kendala / Masalah yang Ditemui</span>
                                            </h4>
                                            <div class="text-sm text-amber-900 leading-relaxed prose-content">
                                                {!! $act->constraint !!}
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Attachment Section (Section 40.5) -->
                                    @if($act->attachment_path)
                                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 flex items-center justify-between gap-3">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0">
                                                    @if($act->hasImageAttachment())
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    @else
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                        </svg>
                                                    @endif
                                                </div>
                                                <div class="truncate">
                                                    <span class="text-xs font-bold text-slate-800 block truncate">{{ $act->attachment_name ?? 'Lampiran Dokumen' }}</span>
                                                    <span class="text-2xs text-slate-400">Bukti Pendukung Pekerjaan</span>
                                                </div>
                                            </div>

                                            <a
                                                href="{{ Storage::url($act->attachment_path) }}"
                                                target="_blank"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:bg-slate-50 transition shrink-0 cursor-pointer shadow-2xs"
                                            >
                                                <span>Lihat / Unduh</span>
                                            </a>
                                        </div>
                                    @endif

                                    <!-- Supervisor Feedback & Comments (Section 40.3) -->
                                    <div class="pt-2 space-y-3" data-activity-card="{{ $act->id }}">
                                        <div id="activity-comments-container-{{ $act->id }}" class="space-y-2 {{ $act->comments->isEmpty() ? 'hidden' : '' }}">
                                            <span class="text-2xs font-bold uppercase tracking-wider text-slate-400">Catatan & Umpan Balik Atasan:</span>
                                            <div id="activity-comments-list-{{ $act->id }}" class="space-y-2">
                                                @foreach($act->comments as $comment)
                                                    <div id="comment-item-{{ $comment->id }}" class="bg-indigo-50/60 rounded-xl p-3 border border-indigo-100 text-xs text-slate-800 space-y-1">
                                                        <div class="flex items-center justify-between gap-2">
                                                            <div class="flex items-center gap-1.5">
                                                                <span class="font-bold text-indigo-900">{{ $comment->user?->full_name ?? 'User' }}</span>
                                                                <span class="text-2xs px-1.5 py-0.2 rounded bg-indigo-100 text-indigo-700 font-semibold">{{ $comment->user?->role?->name ?? 'Supervisor' }}</span>
                                                            </div>
                                                            <span class="text-2xs text-slate-400">{{ $comment->created_at->diffForHumans() }}</span>
                                                        </div>
                                                        <p class="text-slate-700 leading-relaxed">{{ $comment->comment }}</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        <!-- Add Comment Form for Supervisor / Admin -->
                                        @if($currentUser->hasRole('Administrator') || in_array($act->user_id, $currentUser->getSubordinateIds()) || $act->user_id === $currentUser->id)
                                            <div>
                                                @if($commentingActivityId === $act->id)
                                                    <div class="mt-2 bg-slate-50 rounded-xl p-3 border border-slate-200 space-y-2">
                                                        <textarea
                                                            wire:model="newCommentText"
                                                            rows="2"
                                                            placeholder="Tuliskan catatan evaluasi atau umpan balik untuk aktivitas ini..."
                                                            class="block w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600 outline-none"
                                                        ></textarea>
                                                        @error('newCommentText') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror

                                                        <div class="flex items-center justify-end gap-2">
                                                            <button
                                                                wire:click="toggleCommentBox({{ $act->id }})"
                                                                type="button"
                                                                class="px-2.5 py-1 text-xs text-slate-600 hover:text-slate-800 font-medium cursor-pointer"
                                                            >
                                                                Batal
                                                            </button>
                                                            <button
                                                                wire:click="addComment({{ $act->id }})"
                                                                type="button"
                                                                class="px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-2xs transition cursor-pointer"
                                                            >
                                                                Kirim Catatan
                                                            </button>
                                                        </div>
                                                    </div>
                                                @else
                                                    <button
                                                        wire:click="toggleCommentBox({{ $act->id }})"
                                                        type="button"
                                                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition cursor-pointer"
                                                    >
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                                        </svg>
                                                        <span>Tambah Catatan / Komentar</span>
                                                    </button>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Audit Trail Footer (Section 17) -->
                                    <div class="flex flex-wrap items-center justify-between text-2xs text-slate-400 pt-2 border-t border-slate-100 gap-2">
                                        <span>Dicatat pada: {{ $act->created_at->translatedFormat('d M Y, H:i') }}</span>
                                        @if($act->verifier)
                                            <span class="text-indigo-600 font-medium">Diverifikasi oleh {{ $act->verifier->full_name }} ({{ $act->verified_at?->translatedFormat('d M Y') }})</span>
                                        @elseif($act->updated_at && $act->updated_at->ne($act->created_at))
                                            <span>Terakhir diperbarui: {{ $act->updated_at->translatedFormat('d M Y, H:i') }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @else
        <!-- TAB 2: ANALYTICS & METRICS VIEW (Section 40.7) -->
        <div class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Card 1: Distribusi Kategori Pekerjaan -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Distribusi Kategori Pekerjaan</h3>
                        <span class="text-xs text-slate-400">{{ $selectedMonthDate->translatedFormat('F Y') }}</span>
                    </div>

                    @if($categoryBreakdown->isEmpty())
                        <p class="text-xs text-slate-400 py-6 text-center">Belum ada data aktivitas untuk bulan ini.</p>
                    @else
                        <div class="space-y-3 pt-2">
                            @foreach($categoryBreakdown as $catName => $count)
                                @php $pct = $totalActivities > 0 ? round(($count / $totalActivities) * 100) : 0; @endphp
                                <div class="space-y-1">
                                    <div class="flex justify-between text-xs font-semibold">
                                        <span class="text-slate-800">{{ $catName }}</span>
                                        <span class="text-slate-600">{{ $count }} Tugas ({{ $pct }}%)</span>
                                    </div>
                                    <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-indigo-600 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Card 2: Sumber Permintaan Teratas -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Pihak Pemohon Aktivitas</h3>
                        <span class="text-xs text-slate-400">Paling Sering</span>
                    </div>

                    @if($requestSourceBreakdown->isEmpty())
                        <p class="text-xs text-slate-400 py-6 text-center">Belum ada data aktivitas untuk bulan ini.</p>
                    @else
                        <div class="space-y-3 pt-2">
                            @foreach($requestSourceBreakdown as $sourceName => $count)
                                @php $pct = $totalActivities > 0 ? round(($count / $totalActivities) * 100) : 0; @endphp
                                <div class="space-y-1">
                                    <div class="flex justify-between text-xs font-semibold">
                                        <span class="text-slate-800">{{ $sourceName }}</span>
                                        <span class="text-slate-600">{{ $count }} Tugas ({{ $pct }}%)</span>
                                    </div>
                                    <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-emerald-600 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Card 3: Tren Aktivitas Harian Sepanjang Bulan -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Tren Frekuensi Aktivitas Harian</h3>
                        <p class="text-2xs text-slate-500 mt-0.5">Jumlah pencatatan pekerjaan dari tanggal 1 s/d {{ $daysInMonth }} {{ $selectedMonthDate->translatedFormat('F Y') }}</p>
                    </div>
                </div>

                <div class="pt-4 overflow-x-auto">
                    <div class="flex items-end gap-1.5 h-36 min-w-[650px] px-2 pb-6 border-b border-slate-200">
                        @for($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $cnt = $dailyTrend[$d] ?? 0;
                                $maxCnt = max(1, $dailyTrend->max() ?? 1);
                                $heightPct = $cnt > 0 ? max(15, round(($cnt / $maxCnt) * 100)) : 4;
                            @endphp
                            <div class="flex-1 flex flex-col items-center gap-1 group relative">
                                <div class="w-full bg-indigo-100 group-hover:bg-indigo-600 rounded-t transition-all" style="height: {{ $heightPct }}%">
                                    @if($cnt > 0)
                                        <div class="absolute -top-7 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-3xs font-bold py-0.5 px-1.5 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap z-10">
                                            {{ $cnt }} Aktivitas
                                        </div>
                                    @endif
                                </div>
                                <span class="text-3xs font-medium text-slate-400 absolute -bottom-5">{{ $d }}</span>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Add / Edit Activity Modal (Sections 11, 12, 14, 30) -->
    @if($isFormModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/75 backdrop-blur-xs transition-opacity" wire:click="closeFormModal"></div>

            <div class="relative bg-white w-full max-w-xl rounded-2xl shadow-2xl ring-1 ring-slate-900/10 text-left overflow-hidden flex flex-col max-h-[88vh] z-10">
                <div class="px-5 py-3.5 border-b border-slate-200 flex items-center justify-between shrink-0 bg-slate-50/70">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">
                            {{ $formMode === 'create' ? 'Tambah Aktivitas Baru' : 'Ubah Data Aktivitas' }}
                        </h3>
                    </div>
                    <button
                        type="button"
                        wire:click="closeFormModal"
                        class="text-slate-400 hover:text-slate-600 rounded-lg p-1.5 hover:bg-slate-200/50 transition cursor-pointer"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="{{ $formMode === 'create' ? 'saveActivity' : 'updateActivity' }}" class="flex flex-col flex-1 overflow-hidden">
                    <div class="p-5 space-y-4 overflow-y-auto flex-1 text-slate-800">
                        <!-- Field 1: Tanggal & Kategori -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                    Tanggal Aktivitas <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    wire:model="activity_date"
                                    type="date"
                                    required
                                    class="block w-full px-3 py-2 bg-slate-50 border @error('activity_date') border-rose-400 @else border-slate-300 @enderror rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600 transition"
                                />
                                @error('activity_date') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                    Kategori Pekerjaan <span class="text-rose-500">*</span>
                                </label>
                                <select
                                    wire:model="category"
                                    required
                                    class="block w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600 transition"
                                >
                                    @foreach($categoryOptions as $cat)
                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Field 2: Label / Tags & Sumber Permintaan -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                    Sumber Permintaan <span class="text-rose-500">*</span>
                                </label>
                                <select
                                    wire:model.live="requested_by_option"
                                    class="block w-full px-3 py-2 bg-slate-50 border @error('requested_by_option') border-rose-400 @else border-slate-300 @enderror rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600 transition"
                                >
                                    @foreach($requestedByOptions as $opt)
                                        <option value="{{ $opt }}">{{ $opt }}</option>
                                    @endforeach
                                </select>

                                @if($requested_by_option === 'Lainnya')
                                    <div class="mt-2">
                                        <input
                                            wire:model="requested_by_custom"
                                            type="text"
                                            placeholder="Sebutkan pemohon lainnya..."
                                            class="block w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:bg-white focus:outline-none"
                                        />
                                    </div>
                                @endif
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                    Label / Tags <span class="text-slate-400 font-normal lowercase">(pisahkan koma)</span>
                                </label>
                                <input
                                    wire:model="tags_input"
                                    type="text"
                                    placeholder="Contoh: Server, Backup, Database"
                                    class="block w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600 transition"
                                />
                            </div>
                        </div>

                        <!-- Field 3: Deskripsi Aktivitas (WYSIWYG) -->
                        <div wire:ignore>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                Deskripsi Aktivitas / Pekerjaan <span class="text-rose-500">*</span>
                            </label>
                            <div id="editor-activity" class="bg-white rounded-xl min-h-[120px] text-sm"></div>
                        </div>
                        @error('activity') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror

                        <!-- Field 4: Hasil / Outcome (WYSIWYG) -->
                        <div wire:ignore>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                Hasil / Luaran Pekerjaan <span class="text-rose-500">*</span>
                            </label>
                            <div id="editor-result" class="bg-white rounded-xl min-h-[100px] text-sm"></div>
                        </div>
                        @error('result') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror

                        <!-- Field 5: Kendala / Masalah (WYSIWYG Optional) -->
                        <div wire:ignore>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                Kendala / Masalah yang Ditemui <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                            </label>
                            <div id="editor-constraint" class="bg-white rounded-xl min-h-[80px] text-sm"></div>
                        </div>

                        <!-- Field 6: Lampiran Bukti Pekerjaan (Attachment) -->
                        <div class="pt-2 border-t border-slate-100">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                Lampiran Bukti Pekerjaan <span class="text-slate-400 font-normal lowercase">(opsional: gambar PNG/JPG atau PDF maks. 5MB)</span>
                            </label>

                            @if($existingAttachmentName && !$removeAttachment)
                                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between text-xs mb-2">
                                    <div class="flex items-center gap-2 truncate">
                                        <svg class="w-4 h-4 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                        </svg>
                                        <span class="font-medium text-slate-800 truncate">{{ $existingAttachmentName }}</span>
                                    </div>
                                    <button
                                        wire:click="$set('removeAttachment', true)"
                                        type="button"
                                        class="text-xs text-rose-600 hover:text-rose-800 font-semibold cursor-pointer shrink-0"
                                    >
                                        Hapus File
                                    </button>
                                </div>
                            @endif

                            <input
                                wire:model="attachment"
                                type="file"
                                accept="image/png,image/jpeg,image/webp,application/pdf"
                                class="block w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer"
                            />
                            @error('attachment') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2.5 shrink-0">
                        <button
                            type="button"
                            wire:click="closeFormModal"
                            class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-200/70 rounded-xl transition cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-xs hover:shadow shadow-indigo-200 transition cursor-pointer"
                        >
                            {{ $formMode === 'create' ? 'Simpan Aktivitas' : 'Perbarui Aktivitas' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal (Section 15) -->
    @if($isDeleteModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/75 backdrop-blur-xs transition-opacity" wire:click="closeDeleteModal"></div>

            <div class="relative bg-white w-full max-w-md rounded-2xl shadow-2xl ring-1 ring-slate-900/10 text-left overflow-hidden z-10 p-6 space-y-4">
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Hapus Aktivitas Kerja?</h3>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                            Aktivitas ini akan dihapus secara lunak (*soft delete*) dari riwayat timeline pekerjaan Anda.
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button
                        type="button"
                        wire:click="closeDeleteModal"
                        class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="deleteActivity"
                        class="px-4 py-2 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 active:bg-rose-800 rounded-xl shadow-xs transition cursor-pointer"
                    >
                        Ya, Hapus Aktivitas
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- WYSIWYG Quill Editor Integration Script (Section 12) -->
<script>
    document.addEventListener('livewire:initialized', () => {
        let quillActivity, quillResult, quillConstraint;

        const quillOptions = {
            theme: 'snow',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    ['link', 'clean']
                ]
            }
        };

        function setupQuillEditors() {
            const elActivity = document.getElementById('editor-activity');
            const elResult = document.getElementById('editor-result');
            const elConstraint = document.getElementById('editor-constraint');

            if (elActivity && !elActivity.__quill) {
                quillActivity = new Quill(elActivity, quillOptions);
                elActivity.__quill = quillActivity;
                quillActivity.on('text-change', () => {
                    const html = quillActivity.root.innerHTML === '<p><br></p>' ? '' : quillActivity.root.innerHTML;
                    @this.set('activity', html, false);
                });
            }

            if (elResult && !elResult.__quill) {
                quillResult = new Quill(elResult, quillOptions);
                elResult.__quill = quillResult;
                quillResult.on('text-change', () => {
                    const html = quillResult.root.innerHTML === '<p><br></p>' ? '' : quillResult.root.innerHTML;
                    @this.set('result', html, false);
                });
            }

            if (elConstraint && !elConstraint.__quill) {
                quillConstraint = new Quill(elConstraint, quillOptions);
                elConstraint.__quill = quillConstraint;
                quillConstraint.on('text-change', () => {
                    const html = quillConstraint.root.innerHTML === '<p><br></p>' ? '' : quillConstraint.root.innerHTML;
                    @this.set('constraint', html, false);
                });
            }
        }

        Livewire.on('init-form-editors', (payload) => {
            const data = Array.isArray(payload) ? payload[0] : payload;
            setTimeout(() => {
                setupQuillEditors();

                if (quillActivity) {
                    quillActivity.root.innerHTML = data.activity || '';
                }
                if (quillResult) {
                    quillResult.root.innerHTML = data.result || '';
                }
                if (quillConstraint) {
                    quillConstraint.root.innerHTML = data.constraint || '';
                }
            }, 60);
        });
    });
</script>
