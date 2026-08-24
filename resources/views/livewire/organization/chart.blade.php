<div class="space-y-8">
    <!-- Page Header -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Bagan Struktur Organisasi</h2>
            <p class="text-xs text-slate-500 mt-0.5">Visualisasi diagram hierarki kepemimpinan, hubungan atasan-bawahan (*supervisor-subordinate*), dan distribusi divisi.</p>
        </div>

        @can('division.manage')
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('admin.organization') }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span>Kelola Divisi & Posisi</span>
                </a>
            </div>
        @endcan
    </div>

    <!-- Division Summary Badges -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @foreach($divisions as $div)
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-900 truncate">{{ $div->name }}</span>
                    <span class="text-2xs font-semibold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">
                        {{ $div->users->count() }} Anggota
                    </span>
                </div>
                <p class="text-2xs text-slate-400 mt-1">
                    Kepala: <strong class="text-slate-600">{{ $div->headUser?->full_name ?? 'Belum ditentukan' }}</strong>
                </p>
            </div>
        @endforeach
    </div>

    <!-- Interactive Hierarchy Tree Container -->
    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200/80 shadow-xs overflow-x-auto">
        <div class="min-w-[700px] flex flex-col items-center space-y-8">
            @forelse($rootUsers as $root)
                <div class="flex flex-col items-center w-full">
                    <!-- Root Node Card -->
                    <div class="bg-gradient-to-br from-indigo-700 to-indigo-900 text-white p-4 rounded-2xl shadow-md ring-4 ring-indigo-100 max-w-xs w-full text-center space-y-2 relative">
                        <div class="w-12 h-12 rounded-xl bg-white/20 text-white font-bold text-base flex items-center justify-center mx-auto shadow-inner">
                            {{ strtoupper(substr($root->full_name, 0, 2)) }}
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-white">{{ $root->full_name }}</h3>
                            <p class="text-2xs text-indigo-200 font-mono">&#64;{{ $root->username }}</p>
                        </div>
                        <div class="pt-1 border-t border-indigo-500/40 flex items-center justify-center gap-1.5 text-2xs">
                            <span class="px-2 py-0.5 rounded-full bg-white/15 font-semibold text-white">
                                {{ $root->position?->name ?? ($root->role?->name ?? 'Pimpinan') }}
                            </span>
                            <span class="px-2 py-0.5 rounded-full bg-white/10 text-indigo-100">
                                {{ $root->division?->name ?? 'Eksekutif' }}
                            </span>
                        </div>
                    </div>

                    <!-- Children Branch Connector -->
                    @if($root->subordinates->isNotEmpty())
                        <div class="w-0.5 h-6 bg-slate-300"></div>

                        <div class="flex flex-wrap justify-center gap-6 relative pt-4 before:content-[''] before:absolute before:top-0 before:left-1/4 before:right-1/4 before:h-0.5 before:bg-slate-300">
                            @foreach($root->subordinates as $sub)
                                <div class="flex flex-col items-center space-y-3">
                                    <div class="w-0.5 h-4 bg-slate-300 -mt-4"></div>

                                    <!-- Level 2 Subordinate Card -->
                                    <div class="bg-white border-2 border-indigo-100 hover:border-indigo-300 p-4 rounded-2xl shadow-xs max-w-[240px] w-full text-center space-y-2 transition">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-700 font-bold text-sm flex items-center justify-center mx-auto">
                                            {{ strtoupper(substr($sub->full_name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-bold text-slate-900">{{ $sub->full_name }}</h4>
                                            <p class="text-3xs text-slate-400 font-mono">&#64;{{ $sub->username }}</p>
                                        </div>
                                        <div class="pt-1 border-t border-slate-100 flex flex-col gap-0.5 text-2xs">
                                            <span class="font-semibold text-indigo-700">{{ $sub->position?->name ?? 'Staff' }}</span>
                                            <span class="text-slate-500 text-3xs">{{ $sub->division?->name ?? '-' }}</span>
                                        </div>

                                        @if($sub->subordinates->isNotEmpty())
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-3xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100 mt-1">
                                                {{ $sub->subordinates->count() }} Anggota Tim
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Level 3 Subordinates -->
                                    @if($sub->subordinates->isNotEmpty())
                                        <div class="w-0.5 h-4 bg-slate-300"></div>
                                        <div class="flex flex-wrap justify-center gap-3">
                                            @foreach($sub->subordinates as $leaf)
                                                <div class="bg-slate-50 hover:bg-white border border-slate-200 hover:border-indigo-200 p-2.5 rounded-xl text-center max-w-[180px] w-full space-y-1 shadow-2xs transition">
                                                    <div class="w-7 h-7 rounded-lg bg-slate-200 text-slate-700 font-bold text-2xs flex items-center justify-center mx-auto">
                                                        {{ strtoupper(substr($leaf->full_name, 0, 2)) }}
                                                    </div>
                                                    <p class="text-2xs font-bold text-slate-800 truncate">{{ $leaf->full_name }}</p>
                                                    <p class="text-3xs text-slate-500 truncate">{{ $leaf->position?->name ?? 'Staff' }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-8 text-center text-xs text-slate-500">
                    Belum ada data struktur kepemimpinan yang dapat ditampilkan.
                </div>
            @endforelse
        </div>
    </div>
</div>
