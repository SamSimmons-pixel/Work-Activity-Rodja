<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <h2 class="text-xl font-bold text-slate-900 tracking-tight">Profil Pengguna</h2>
        <p class="text-xs text-slate-500 mt-0.5">Lihat informasi akun, struktur penugasan organisasi, dan kelola keamanan kata sandi Anda.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Organization & Account Details Card -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs text-center">
                <!-- User Avatar / Initials -->
                <div class="w-20 h-20 rounded-2xl bg-indigo-600 text-white text-2xl font-bold flex items-center justify-center mx-auto shadow-md shadow-indigo-200 mb-4">
                    {{ strtoupper(substr($user->full_name, 0, 2)) }}
                </div>

                <h3 class="text-lg font-bold text-slate-900">{{ $user->full_name }}</h3>
                <p class="text-xs text-slate-500 font-mono">&#64;{{ $user->username }}</p>

                <div class="mt-3 flex items-center justify-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                        {{ $user->role?->name ?? 'User' }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $user->status === 'Active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                        {{ $user->status === 'Active' ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>

                <!-- Organization Info List -->
                <div class="mt-6 pt-6 border-t border-slate-100 text-left space-y-3.5 text-xs">
                    <div>
                        <span class="text-slate-400 font-bold uppercase tracking-wider block text-2xs">Divisi Kerja</span>
                        <span class="font-semibold text-slate-800">{{ $user->division?->name ?? 'Belum ditentukan' }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 font-bold uppercase tracking-wider block text-2xs">Jabatan / Posisi</span>
                        <span class="font-semibold text-slate-800">{{ $user->position?->name ?? 'Belum ditentukan' }}</span>
                        @if($user->position?->level)
                            <span class="text-slate-500 block text-2xs">(Level {{ $user->position->level }})</span>
                        @endif
                    </div>

                    <div>
                        <span class="text-slate-400 font-bold uppercase tracking-wider block text-2xs">Atasan Langsung (Supervisor)</span>
                        @if($user->supervisor)
                            <span class="font-semibold text-indigo-700">{{ $user->supervisor->full_name }}</span>
                            <span class="text-slate-500 block text-2xs">{{ $user->supervisor->position?->name ?? 'Supervisor' }}</span>
                        @else
                            <span class="text-slate-400 italic">Tidak ada (Pucuk Pimpinan)</span>
                        @endif
                    </div>

                    <div>
                        <span class="text-slate-400 font-bold uppercase tracking-wider block text-2xs">Terakhir Masuk Sistem</span>
                        <span class="font-medium text-slate-700">
                            {{ $user->last_login_at ? $user->last_login_at->translatedFormat('d F Y, H:i') : 'Sesi ini' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Profile Settings & Security Forms -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Form 1: Edit Profile Name -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-900">Informasi Pribadi</h3>
                    <p class="text-xs text-slate-500">Perbarui nama lengkap yang ditampilkan pada laporan dan riwayat aktivitas.</p>
                </div>

                @if (session()->has('profile_message'))
                    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>{{ session('profile_message') }}</span>
                    </div>
                @endif

                <form wire:submit="updateProfile" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                Nama Lengkap <span class="text-rose-500">*</span>
                            </label>
                            <input
                                wire:model="full_name"
                                type="text"
                                required
                                class="block w-full px-3.5 py-2 bg-slate-50 border @error('full_name') border-rose-400 @else border-slate-300 @enderror rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600"
                            />
                            @error('full_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                Username @if(auth()->user()->hasRole('Administrator')) <span class="text-rose-500">*</span> @endif
                            </label>
                            @if(auth()->user()->hasRole('Administrator'))
                                <input
                                    wire:model="username"
                                    type="text"
                                    required
                                    class="block w-full px-3.5 py-2 bg-slate-50 border @error('username') border-rose-400 @else border-slate-300 @enderror rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600"
                                />
                                @error('username') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                <p class="mt-1 text-2xs text-indigo-600 font-medium">Khusus Administrator dapat mengubah username.</p>
                            @else
                                <input
                                    type="text"
                                    disabled
                                    value="{{ $username }}"
                                    class="block w-full px-3.5 py-2 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-500 cursor-not-allowed"
                                />
                                <p class="mt-1 text-2xs text-slate-400">Username dikelola oleh Administrator.</p>
                            @endif
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-xl text-xs font-semibold shadow-xs transition cursor-pointer"
                        >
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Form 2: Change Password -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-900">Keamanan Kata Sandi</h3>
                    <p class="text-xs text-slate-500">Pastikan akun Anda menggunakan kata sandi yang aman dan tidak mudah ditebak.</p>
                </div>

                @if (session()->has('password_message'))
                    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>{{ session('password_message') }}</span>
                    </div>
                @endif

                <form wire:submit="updatePassword" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Kata Sandi Saat Ini <span class="text-rose-500">*</span>
                        </label>
                        <input
                            wire:model="current_password"
                            type="password"
                            required
                            placeholder="Masukkan kata sandi saat ini"
                            class="block w-full px-3.5 py-2 bg-slate-50 border @error('current_password') border-rose-400 @else border-slate-300 @enderror rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600"
                        />
                        @error('current_password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                Kata Sandi Baru <span class="text-rose-500">*</span>
                            </label>
                            <input
                                wire:model="password"
                                type="password"
                                required
                                placeholder="Minimal 6 karakter"
                                class="block w-full px-3.5 py-2 bg-slate-50 border @error('password') border-rose-400 @else border-slate-300 @enderror rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600"
                            />
                            @error('password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                Konfirmasi Kata Sandi Baru <span class="text-rose-500">*</span>
                            </label>
                            <input
                                wire:model="password_confirmation"
                                type="password"
                                required
                                placeholder="Ulangi kata sandi baru"
                                class="block w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-600"
                            />
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-xl text-xs font-semibold shadow-xs transition cursor-pointer"
                        >
                            <span>Perbarui Kata Sandi</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
