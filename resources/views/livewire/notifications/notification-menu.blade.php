<div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
    <!-- Bell Button with Red Circle Badge Counter -->
    <button
        type="button"
        @click.stop="open = !open"
        class="relative p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition cursor-pointer focus:outline-none focus:ring-2 focus:ring-indigo-100"
        title="Notifikasi"
        aria-label="Lihat Notifikasi"
        :aria-expanded="open"
    >
        <!-- Bell Icon -->
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>

        <!-- Red Circle Badge for Unread Notifications -->
        @if($unreadCount > 0)
            <span class="absolute -top-0.5 -right-0.5 flex items-center justify-center min-w-[1.25rem] h-5 px-1 text-3xs font-black text-white bg-rose-600 rounded-full ring-2 ring-white shadow-xs animate-pulse">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    <!-- YouTube-style Notification Dropdown Panel -->
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-1 scale-95"
        class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-slate-200/90 overflow-hidden z-50 flex flex-col max-h-[85vh] ring-1 ring-slate-900/10"
        role="menu"
        aria-orientation="vertical"
    >
        <!-- Panel Header -->
        <div class="px-4 py-3.5 border-b border-slate-100 bg-slate-50/80 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2">
                <h3 class="text-sm font-bold text-slate-900">Notifikasi</h3>
                @if($unreadCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-3xs font-extrabold bg-rose-100 text-rose-700">
                        {{ $unreadCount }} Baru
                    </span>
                @endif
            </div>

            @if($unreadCount > 0)
                <button
                    wire:click="markAllAsRead"
                    type="button"
                    class="text-2xs font-semibold text-indigo-600 hover:text-indigo-800 transition cursor-pointer hover:underline"
                >
                    Tandai Semua Dibaca
                </button>
            @endif
        </div>

        <!-- Filter Tabs: Semua vs Belum Dibaca -->
        <div class="px-3 pt-2 pb-1 bg-white border-b border-slate-100 flex items-center gap-1 shrink-0">
            <button
                wire:click="setFilter('all')"
                type="button"
                class="px-3 py-1 text-2xs font-bold rounded-lg transition cursor-pointer {{ $filter === 'all' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50' }}"
            >
                Semua
            </button>
            <button
                wire:click="setFilter('unread')"
                type="button"
                class="px-3 py-1 text-2xs font-bold rounded-lg transition cursor-pointer {{ $filter === 'unread' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50' }}"
            >
                Belum Dibaca ({{ $unreadCount }})
            </button>
        </div>

        <!-- Notifications Scrollable List Area -->
        <div class="overflow-y-auto flex-1 divide-y divide-slate-100">
            @forelse($notifications as $notif)
                @php
                    $data = $notif->data;
                    $isUnread = is_null($notif->read_at);
                    $type = $data['type'] ?? 'general';
                    $url = $data['url'] ?? null;
                @endphp

                <!-- Notification Row Item -->
                <div
                    wire:key="notif-{{ $notif->id }}"
                    class="group relative flex items-start gap-3 p-3.5 transition cursor-pointer {{ $isUnread ? 'bg-indigo-50/50 hover:bg-indigo-50/80 border-l-4 border-indigo-600' : 'bg-white hover:bg-slate-50 border-l-4 border-transparent' }}"
                >
                    <!-- Type Icon Badge -->
                    <div class="shrink-0 mt-0.5">
                        @if($type === 'comment')
                            <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center shadow-2xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                </svg>
                            </div>
                        @elseif($type === 'verified')
                            <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shadow-2xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                        @elseif($type === 'review')
                            <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shadow-2xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                            </div>
                        @else
                            <div class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center shadow-2xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                        @endif
                    </div>

                    <!-- Content Details -->
                    <div
                        class="flex-1 min-w-0"
                        wire:click="markAsRead('{{ $notif->id }}', '{{ $url }}')"
                    >
                        <div class="flex items-center justify-between gap-1">
                            <h4 class="text-xs {{ $isUnread ? 'font-black text-slate-900' : 'font-semibold text-slate-700' }} truncate">
                                {{ $data['title'] ?? 'Pemberitahuan Sistem' }}
                            </h4>
                            <span class="text-3xs text-slate-400 shrink-0">
                                {{ $notif->created_at->diffForHumans(null, true) }}
                            </span>
                        </div>

                        <p class="text-2xs {{ $isUnread ? 'text-slate-800 font-medium' : 'text-slate-500' }} line-clamp-2 mt-0.5 leading-relaxed">
                            {{ $data['message'] ?? '-' }}
                        </p>

                        @if(!empty($data['sender_name']))
                            <div class="flex items-center gap-1.5 mt-1 text-3xs text-slate-400">
                                <span class="font-bold text-slate-600">{{ $data['sender_name'] }}</span>
                                @if(!empty($data['sender_role']))
                                    <span>&bull;</span>
                                    <span>{{ $data['sender_role'] }}</span>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Right Column: Unread Dot Indicator & Delete Button -->
                    <div class="flex flex-col items-end justify-between self-stretch shrink-0">
                        @if($isUnread)
                            <span
                                class="w-2.5 h-2.5 rounded-full bg-indigo-600 shadow-xs mt-1 shrink-0"
                                title="Belum dibaca"
                            ></span>
                        @endif

                        <button
                            wire:click.stop="deleteNotification('{{ $notif->id }}')"
                            type="button"
                            class="text-slate-300 hover:text-rose-500 rounded-md p-1 transition cursor-pointer opacity-0 group-hover:opacity-100"
                            title="Hapus notifikasi"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            @empty
                <!-- Empty State (No Notifications) -->
                <div class="p-8 text-center space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-700">Belum ada notifikasi</p>
                        <p class="text-3xs text-slate-400 mt-0.5">
                            {{ $filter === 'unread' ? 'Semua notifikasi telah Anda baca.' : 'Aktivitas, komentar, dan verifikasi baru akan muncul di sini.' }}
                        </p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
