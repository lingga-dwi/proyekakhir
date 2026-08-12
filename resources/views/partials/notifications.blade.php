@auth
@php
    $headerNotifications = auth()->user()->notifications()->latest()->limit(8)->get();
    $unreadNotificationCount = auth()->user()->unreadNotifications()->count();
@endphp
<div class="relative" x-data="{ notificationOpen: false }">
    <button type="button" @click="notificationOpen = !notificationOpen" @click.outside="notificationOpen = false" class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-amber-300 hover:bg-amber-50 hover:text-amber-700" aria-label="Notifikasi" :aria-expanded="notificationOpen.toString()">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.85 23.85 0 0 0 5.454-1.31A8.97 8.97 0 0 1 18 9.75V9a6 6 0 1 0-12 0v.75a8.97 8.97 0 0 1-2.312 6.022c1.733.562 3.56 1.003 5.455 1.31m5.714 0a24.26 24.26 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/></svg>
        @if($unreadNotificationCount > 0)
            <span class="absolute -right-1.5 -top-1.5 flex min-h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white ring-2 ring-white">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>
        @endif
    </button>

    <div x-cloak x-show="notificationOpen" x-transition.origin.top.right class="absolute right-0 top-12 z-[80] w-[min(22rem,calc(100vw-2rem))] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <div><p class="font-bold text-slate-950">Notifikasi</p><p class="text-xs text-slate-500">{{ $unreadNotificationCount }} belum dibaca</p></div>
            @if($unreadNotificationCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="text-xs font-semibold text-amber-700 hover:text-amber-800">Tandai semua dibaca</button></form>
            @endif
        </div>
        <div class="max-h-96 divide-y divide-slate-100 overflow-y-auto">
            @forelse($headerNotifications as $notification)
                <a href="{{ route('notifications.open', $notification->id) }}" class="block px-4 py-3 transition hover:bg-slate-50 {{ $notification->read_at ? '' : 'bg-amber-50/60' }}">
                    <div class="flex gap-3">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full {{ $notification->read_at ? 'bg-slate-300' : 'bg-amber-500' }}"></span>
                        <div class="min-w-0"><p class="text-sm font-semibold text-slate-900">{{ $notification->data['title'] ?? 'Pembaruan Daiku' }}</p><p class="mt-1 text-xs leading-5 text-slate-600">{{ $notification->data['message'] ?? '' }}</p><p class="mt-1 text-[11px] text-slate-400">{{ $notification->created_at->diffForHumans() }}</p></div>
                    </div>
                </a>
            @empty
                <div class="px-6 py-10 text-center"><svg class="mx-auto h-7 w-7 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M9.143 17.082a24.26 24.26 0 0 0 5.714 0M6.17 6.17A6 6 0 0 1 18 9v.75a8.97 8.97 0 0 0 2.312 6.022c-.663.215-1.34.411-2.031.586M6 9.75a8.97 8.97 0 0 1-2.312 6.022c1.143.37 2.326.69 3.546.947M9.143 17.082a3 3 0 0 0 5.714 0"/></svg><p class="mt-3 text-sm font-medium text-slate-600">Belum ada notifikasi</p></div>
            @endforelse
        </div>
    </div>
</div>
@endauth
