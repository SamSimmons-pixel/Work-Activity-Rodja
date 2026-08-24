<div class="max-w-5xl mx-auto py-6 px-4 sm:px-6 text-slate-900 bg-white sm:rounded-2xl sm:shadow-lg sm:border sm:border-slate-200 my-4 print:my-0 print:border-none print:shadow-none print:p-0">
    <!-- Top Action Bar (Hidden in Print) -->
    <div class="flex items-center justify-between gap-4 pb-6 border-b border-slate-200 print:hidden">
        <a
            href="{{ route('dashboard') }}"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-xl transition cursor-pointer"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Dashboard</span>
        </a>

        <button
            onclick="window.print()"
            type="button"
            class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-xs transition cursor-pointer"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span>Cetak / Simpan PDF</span>
        </button>
    </div>

    <!-- Official Document Header -->
    <div class="mt-6 flex items-center justify-between border-b-2 border-slate-900 pb-4">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold text-xl">
                WA
            </div>
            <div>
                <h1 class="text-xl font-extrabold tracking-tight text-slate-900 uppercase">Work Activity Report</h1>
                <p class="text-xs text-slate-500 font-mono">https://work.rodja.studio &bull; Sistem Dokumentasi Pekerjaan</p>
            </div>
        </div>
        <div class="text-right">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Periode Laporan</span>
            <span class="text-base font-extrabold text-indigo-700">{{ $periodDate->translatedFormat('F Y') }}</span>
        </div>
    </div>

    <!-- Employee & Organization Meta -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 py-4 border-b border-slate-200 text-xs">
        <div>
            <span class="text-slate-400 font-bold uppercase tracking-wider block text-2xs">Nama Karyawan</span>
            <span class="font-bold text-slate-900 text-sm">{{ $targetUser->full_name }}</span>
            <span class="text-slate-500 font-mono block">&#64;{{ $targetUser->username }}</span>
        </div>
        <div>
            <span class="text-slate-400 font-bold uppercase tracking-wider block text-2xs">Divisi Kerja</span>
            <span class="font-semibold text-slate-800">{{ $targetUser->division?->name ?? '-' }}</span>
        </div>
        <div>
            <span class="text-slate-400 font-bold uppercase tracking-wider block text-2xs">Jabatan / Posisi</span>
            <span class="font-semibold text-slate-800">{{ $targetUser->position?->name ?? '-' }}</span>
        </div>
        <div>
            <span class="text-slate-400 font-bold uppercase tracking-wider block text-2xs">Atasan Langsung</span>
            <span class="font-semibold text-indigo-700">{{ $targetUser->supervisor?->full_name ?? 'Pucuk Pimpinan' }}</span>
        </div>
    </div>

    <!-- Summary Box -->
    <div class="grid grid-cols-3 gap-3 py-4 border-b border-slate-200 text-center">
        <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200/60">
            <span class="text-2xs font-bold uppercase text-slate-500">Total Aktivitas</span>
            <p class="text-lg font-bold text-slate-900">{{ $totalActivities }}</p>
        </div>
        <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200/60">
            <span class="text-2xs font-bold uppercase text-slate-500">Terselesaikan</span>
            <p class="text-lg font-bold text-emerald-600">{{ $completedActivities }}</p>
        </div>
        <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200/60">
            <span class="text-2xs font-bold uppercase text-slate-500">Terdapat Kendala</span>
            <p class="text-lg font-bold text-amber-600">{{ $hasConstraintCount }}</p>
        </div>
    </div>

    <!-- Activities Table -->
    <div class="py-6 space-y-4">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700">Rincian Catatan Aktivitas Bulanan</h2>

        @if($activities->isEmpty())
            <div class="p-8 text-center bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-500">
                Tidak ada aktivitas yang tercatat pada periode bulan ini.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-300 text-left text-xs border border-slate-200 rounded-xl">
                    <thead class="bg-slate-100 text-slate-700 font-bold uppercase text-2xs">
                        <tr>
                            <th class="px-3 py-2.5 w-10 text-center">No</th>
                            <th class="px-3 py-2.5 w-28">Tanggal</th>
                            <th class="px-3 py-2.5 w-32">Diminta Oleh</th>
                            <th class="px-3 py-2.5">Aktivitas & Hasil Pekerjaan</th>
                            <th class="px-3 py-2.5 w-28 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-slate-800">
                        @foreach($activities as $index => $act)
                            <tr class="align-top hover:bg-slate-50">
                                <td class="px-3 py-3 text-center font-medium text-slate-500">{{ $index + 1 }}</td>
                                <td class="px-3 py-3 font-semibold whitespace-nowrap text-slate-900">
                                    {{ $act->activity_date->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-3 py-3 text-slate-700">
                                    <span class="font-medium">{{ $act->requested_by }}</span>
                                </td>
                                <td class="px-3 py-3 space-y-2">
                                    <!-- Activity Content -->
                                    <div>
                                        <span class="font-bold text-slate-600 uppercase text-2xs block">Pekerjaan:</span>
                                        <div class="prose-content text-slate-800 leading-relaxed">
                                            {!! $act->activity !!}
                                        </div>
                                    </div>

                                    <!-- Result Content -->
                                    <div class="bg-slate-50 p-2 rounded-lg border border-slate-100">
                                        <span class="font-bold text-emerald-700 uppercase text-2xs block">Hasil / Luaran:</span>
                                        <div class="prose-content text-slate-700 leading-relaxed">
                                            {!! $act->result !!}
                                        </div>
                                    </div>

                                    <!-- Constraint if any -->
                                    @if(!empty($act->constraint))
                                        <div class="bg-amber-50 p-2 rounded-lg border border-amber-100">
                                            <span class="font-bold text-amber-800 uppercase text-2xs block">Kendala / Masalah:</span>
                                            <div class="prose-content text-amber-900 leading-relaxed">
                                                {!! $act->constraint !!}
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Supervisor Feedback if any -->
                                    @if($act->comments->isNotEmpty())
                                        <div class="bg-indigo-50/70 p-2 rounded-lg border border-indigo-100">
                                            <span class="font-bold text-indigo-800 uppercase text-2xs block">Catatan / Komentar Atasan:</span>
                                            @foreach($act->comments as $comment)
                                                <p class="text-slate-700 text-2xs mt-0.5">
                                                    <strong>{{ $comment->user->full_name }}:</strong> {{ $comment->comment }}
                                                </p>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-2xs font-bold uppercase {{ $act->status === 'Verified' ? 'bg-indigo-100 text-indigo-800' : ($act->status === 'Reviewed' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800') }}">
                                        {{ $act->status === 'Verified' ? 'Terverifikasi' : ($act->status === 'Reviewed' ? 'Ditinjau' : 'Terkirim') }}
                                    </span>
                                    @if($act->verified_at)
                                        <span class="text-3xs text-slate-400 block mt-1">{{ $act->verified_at->translatedFormat('d/m/Y') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Signature Sign-off Section -->
    <div class="pt-8 mt-6 border-t-2 border-slate-300 grid grid-cols-2 gap-8 text-center text-xs break-inside-avoid">
        <div>
            <p class="text-slate-500 mb-16">Dibuat Oleh,</p>
            <p class="font-bold text-slate-900 underline">{{ $targetUser->full_name }}</p>
            <p class="text-2xs text-slate-500">{{ $targetUser->position?->name ?? 'Karyawan' }}</p>
        </div>
        <div>
            <p class="text-slate-500 mb-16">Mengetahui / Menyetujui,</p>
            <p class="font-bold text-slate-900 underline">{{ $targetUser->supervisor?->full_name ?? 'Pimpinan Perusahaan' }}</p>
            <p class="text-2xs text-slate-500">{{ $targetUser->supervisor?->position?->name ?? 'Atasan Langsung' }}</p>
        </div>
    </div>
</div>
