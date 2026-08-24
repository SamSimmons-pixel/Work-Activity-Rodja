<div class="sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0">
    <div class="text-center">
        <!-- Logo / Icon -->
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-indigo-600 shadow-md shadow-indigo-200 mb-4">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
        </div>
        <h2 class="text-2xl font-bold tracking-tight text-slate-900">
            Work Activity
        </h2>
        <p class="mt-1 text-sm text-slate-600">
            Dokumentasi & Monitoring Aktivitas Pekerjaan
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-6 sm:px-10 shadow-sm border border-slate-200/80 rounded-2xl">
            <form wire:submit="authenticate" class="space-y-5">
                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Username
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input
                            wire:model="username"
                            type="text"
                            id="username"
                            autocomplete="username"
                            required
                            placeholder="Masukkan username Anda"
                            class="block w-full pl-10 pr-3.5 py-2.5 bg-slate-50/50 border @error('username') border-rose-400 focus:border-rose-500 focus:ring-rose-200 @else border-slate-300 focus:border-indigo-600 focus:ring-indigo-100 @enderror rounded-xl text-sm transition shadow-sm placeholder:text-slate-400 focus:bg-white focus:outline-none focus:ring-4"
                        />
                    </div>
                    @error('username')
                        <p class="mt-1.5 text-xs font-medium text-rose-600 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 inline shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Kata Sandi
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input
                            wire:model="password"
                            type="password"
                            id="password"
                            autocomplete="current-password"
                            required
                            placeholder="••••••••"
                            class="block w-full pl-10 pr-3.5 py-2.5 bg-slate-50/50 border @error('password') border-rose-400 focus:border-rose-500 focus:ring-rose-200 @else border-slate-300 focus:border-indigo-600 focus:ring-indigo-100 @enderror rounded-xl text-sm transition shadow-sm placeholder:text-slate-400 focus:bg-white focus:outline-none focus:ring-4"
                        />
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs font-medium text-rose-600 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 inline shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input
                            wire:model="remember"
                            type="checkbox"
                            class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                        />
                        <span class="text-sm font-medium text-slate-600 select-none">
                            Ingat Saya
                        </span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div>
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl font-semibold text-sm text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 transition shadow-sm hover:shadow shadow-indigo-200 focus:outline-none focus:ring-4 focus:ring-indigo-100 disabled:opacity-70 disabled:cursor-not-allowed"
                    >
                        <span wire:loading.remove wire:target="authenticate">
                            Masuk ke Akun
                        </span>
                        <span wire:loading wire:target="authenticate" class="inline-flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Memproses...
                        </span>
                    </button>
                </div>
            </form>
        </div>

        <p class="mt-6 text-center text-xs text-slate-500">
            Work Activity &bull; Internal Platform &bull; Sistem Dokumentasi Kerja
        </p>
    </div>
</div>
