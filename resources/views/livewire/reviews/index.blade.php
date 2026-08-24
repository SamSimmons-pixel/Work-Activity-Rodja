<div class="space-y-8">
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
            <span>{{ session('error') }}</span>
            <button type="button" @click="$el.parentElement.remove()" class="text-rose-600 hover:text-rose-800 text-lg leading-none cursor-pointer">&times;</button>
        </div>
    @endif

    <!-- Page Header -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Review Kinerja Berkala</h2>
            <p class="text-xs text-slate-500 mt-0.5">Dokumentasi evaluasi pencapaian kerja, kekuatan utama, dan arahan pengembangan berkala.</p>
        </div>

        @if($subordinates->isNotEmpty())
            <button
                wire:click="openCreateModal"
                type="button"
                class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-xl text-sm font-semibold shadow-xs hover:shadow shadow-indigo-200 transition cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Buat Review Kinerja</span>
            </button>
        @endif
    </div>

    <!-- Section 1: Reviews Given to Subordinates (if supervisor/admin) -->
    @if($subordinates->isNotEmpty())
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide flex items-center gap-2">
                    <span>Evaluasi Kinerja Anggota Tim / Bawahan</span>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700">{{ $reviewsGiven->count() }}</span>
                </h3>
            </div>

            @if($reviewsGiven->isEmpty())
                <div class="bg-white rounded-2xl border border-slate-200/80 p-8 text-center text-xs text-slate-500 shadow-xs">
                    Belum ada review kinerja yang Anda buat untuk anggota tim.
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($reviewsGiven as $rg)
                        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between space-y-4">
                            <div>
                                <div class="flex items-start justify-between gap-2 border-b border-slate-100 pb-3">
                                    <div>
                                        <h4 class="text-sm font-bold text-slate-900">{{ $rg->user->full_name }}</h4>
                                        <p class="text-xs text-slate-500">{{ $rg->user->position?->name ?? 'Staff' }} &bull; {{ $rg->user->division?->name ?? '-' }}</p>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-2xs font-bold uppercase bg-indigo-50 text-indigo-700 border border-indigo-100">
                                        {{ $rg->rating }}
                                    </span>
                                </div>

                                <div class="pt-3 space-y-2 text-xs">
                                    <div class="flex items-center justify-between text-2xs text-slate-400">
                                        <span class="font-bold text-slate-600 uppercase">{{ $rg->period_label }}</span>
                                        <span>{{ $rg->start_date->translatedFormat('d M') }} - {{ $rg->end_date->translatedFormat('d M Y') }}</span>
                                    </div>

                                    <p class="text-slate-700 leading-relaxed pt-1">{{ $rg->summary }}</p>

                                    @if($rg->strengths)
                                        <div class="bg-emerald-50/70 p-2.5 rounded-xl border border-emerald-100 text-emerald-900 text-2xs">
                                            <strong>Kekuatan Utama:</strong> {{ $rg->strengths }}
                                        </div>
                                    @endif

                                    @if($rg->improvements)
                                        <div class="bg-amber-50/70 p-2.5 rounded-xl border border-amber-100 text-amber-900 text-2xs">
                                            <strong>Area Pengembangan:</strong> {{ $rg->improvements }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-2xs text-slate-400">
                                <span>Dibuat: {{ $rg->created_at->translatedFormat('d M Y') }}</span>
                                <button
                                    wire:click="openEditModal({{ $rg->id }})"
                                    type="button"
                                    class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition cursor-pointer"
                                >
                                    Ubah Review
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <!-- Section 2: Reviews Received (Employee Self) -->
    <div class="space-y-4">
        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide flex items-center gap-2">
            <span>Riwayat Review Kinerja Saya</span>
            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700">{{ $reviewsReceived->count() }}</span>
        </h3>

        @if($reviewsReceived->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-200/80 p-8 text-center text-xs text-slate-500 shadow-xs">
                Belum ada catatan review kinerja yang diterima dari atasan langsung.
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($reviewsReceived as $rr)
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs space-y-4">
                        <div class="flex items-start justify-between gap-2 border-b border-slate-100 pb-3">
                            <div>
                                <span class="text-xs font-bold text-indigo-700 uppercase tracking-wide block">{{ $rr->period_label }}</span>
                                <span class="text-2xs text-slate-400">{{ $rr->start_date->translatedFormat('d M') }} s/d {{ $rr->end_date->translatedFormat('d M Y') }}</span>
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-100">
                                {{ $rr->rating }}
                            </span>
                        </div>

                        <div class="space-y-2 text-xs">
                            <p class="text-slate-800 leading-relaxed">{{ $rr->summary }}</p>

                            @if($rr->strengths)
                                <div class="bg-emerald-50/70 p-2.5 rounded-xl border border-emerald-100 text-emerald-900 text-2xs">
                                    <strong>Kekuatan & Pencapaian:</strong> {{ $rr->strengths }}
                                </div>
                            @endif

                            @if($rr->improvements)
                                <div class="bg-amber-50/70 p-2.5 rounded-xl border border-amber-100 text-amber-900 text-2xs">
                                    <strong>Arahan Pengembangan:</strong> {{ $rr->improvements }}
                                </div>
                            @endif
                        </div>

                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-2xs text-slate-400">
                            <span>Penilai: <strong>{{ $rr->reviewer->full_name }}</strong></span>
                            <span>{{ $rr->created_at->translatedFormat('d M Y') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Modal Create / Edit Review -->
    @if($isFormModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/75 backdrop-blur-xs transition-opacity" wire:click="closeFormModal"></div>

            <div class="relative bg-white w-full max-w-xl rounded-2xl shadow-2xl ring-1 ring-slate-900/10 text-left overflow-hidden flex flex-col max-h-[88vh] z-10">
                <div class="px-5 py-3.5 border-b border-slate-200 flex items-center justify-between shrink-0 bg-slate-50/70">
                    <h3 class="text-base font-bold text-slate-900">
                        {{ $formMode === 'create' ? 'Buat Review Kinerja Baru' : 'Ubah Data Review Kinerja' }}
                    </h3>
                    <button type="button" wire:click="closeFormModal" class="text-slate-400 hover:text-slate-600 rounded-lg p-1.5 hover:bg-slate-200/50 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="saveReview" class="flex flex-col flex-1 overflow-hidden">
                    <div class="p-5 space-y-4 overflow-y-auto flex-1 text-slate-800 text-xs">
                        <!-- Select Subordinate -->
                        <div>
                            <label class="block font-bold uppercase tracking-wider text-slate-600 mb-1">
                                Karyawan yang Dinilai <span class="text-rose-500">*</span>
                            </label>
                            <select
                                wire:model="user_id"
                                required
                                class="block w-full px-3 py-2 bg-slate-50 border @error('user_id') border-rose-400 @else border-slate-300 @enderror rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600"
                            >
                                <option value="">-- Pilih Karyawan --</option>
                                @foreach($subordinates as $sub)
                                    <option value="{{ $sub->id }}">{{ $sub->full_name }} ({{ $sub->position?->name ?? 'Staff' }} - {{ $sub->division?->name ?? '-' }})</option>
                                @endforeach
                            </select>
                            @error('user_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <!-- Period Label & Type -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold uppercase tracking-wider text-slate-600 mb-1">
                                    Label Periode <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    wire:model="period_label"
                                    type="text"
                                    placeholder="Contoh: Kuartal 3 2026"
                                    required
                                    class="block w-full px-3 py-2 bg-slate-50 border @error('period_label') border-rose-400 @else border-slate-300 @enderror rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600"
                                />
                                @error('period_label') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block font-bold uppercase tracking-wider text-slate-600 mb-1">
                                    Tingkat / Predikat Kinerja <span class="text-rose-500">*</span>
                                </label>
                                <select
                                    wire:model="rating"
                                    class="block w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600"
                                >
                                    @foreach($ratingOptions as $val => $label)
                                        <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Date Range -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold uppercase tracking-wider text-slate-600 mb-1">
                                    Tanggal Mulai <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    wire:model="start_date"
                                    type="date"
                                    required
                                    class="block w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:bg-white focus:outline-none"
                                />
                            </div>

                            <div>
                                <label class="block font-bold uppercase tracking-wider text-slate-600 mb-1">
                                    Tanggal Akhir <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    wire:model="end_date"
                                    type="date"
                                    required
                                    class="block w-full px-3 py-2 bg-slate-50 border @error('end_date') border-rose-400 @else border-slate-300 @enderror rounded-xl text-sm focus:bg-white focus:outline-none"
                                />
                                @error('end_date') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- Summary -->
                        <div>
                            <label class="block font-bold uppercase tracking-wider text-slate-600 mb-1">
                                Ringkasan Evaluasi Kinerja <span class="text-rose-500">*</span>
                            </label>
                            <textarea
                                wire:model="summary"
                                rows="3"
                                required
                                placeholder="Uraikan penilaian pencapaian target dan kontribusi umum karyawan..."
                                class="block w-full px-3 py-2 bg-slate-50 border @error('summary') border-rose-400 @else border-slate-300 @enderror rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600"
                            ></textarea>
                            @error('summary') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <!-- Strengths -->
                        <div>
                            <label class="block font-bold uppercase tracking-wider text-slate-600 mb-1">
                                Kekuatan & Pencapaian Utama <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                            </label>
                            <textarea
                                wire:model="strengths"
                                rows="2"
                                placeholder="Contoh: Sangat proaktif dalam menyelesaikan kendala jaringan server..."
                                class="block w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:bg-white focus:outline-none"
                            ></textarea>
                        </div>

                        <!-- Improvements -->
                        <div>
                            <label class="block font-bold uppercase tracking-wider text-slate-600 mb-1">
                                Area Peningkatan & Saran Pengembangan <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                            </label>
                            <textarea
                                wire:model="improvements"
                                rows="2"
                                placeholder="Contoh: Tingkatkan dokumentasi teknis dan koordinasi antar divisi..."
                                class="block w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:bg-white focus:outline-none"
                            ></textarea>
                        </div>
                    </div>

                    <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2.5 shrink-0">
                        <button type="button" wire:click="closeFormModal" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-200/70 rounded-xl transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-xs transition cursor-pointer">
                            {{ $formMode === 'create' ? 'Simpan Review' : 'Perbarui Review' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
