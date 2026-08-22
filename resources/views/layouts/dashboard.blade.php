<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('partials.favicon')
    <title>@yield('title', 'Dashboard - Daiku Interior')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>[x-cloak] { display: none !important; }</style>
    @stack('styles')
</head>
<body class="h-dvh overflow-hidden bg-slate-100 text-slate-900" x-data="{ sidebarOpen: false }">
    <div class="flex h-full min-h-0 overflow-hidden">
        <div x-cloak x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-950/50 lg:hidden" @click="sidebarOpen = false"></div>

        <aside class="fixed inset-y-0 left-0 z-50 flex h-dvh w-72 shrink-0 -translate-x-full flex-col overflow-hidden border-r border-slate-200 bg-white transition-transform duration-200 lg:relative lg:inset-auto lg:h-full lg:w-64 lg:translate-x-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            <div class="flex h-20 items-center justify-between border-b border-slate-100 px-6">
                <a href="{{ route('home') }}" aria-label="Kembali ke beranda Daiku">
                    <img src="{{ asset('images/logo/image.png') }}" alt="Daiku Interior" class="h-6 w-auto">
                </a>
                <button type="button" class="text-slate-500 lg:hidden" @click="sidebarOpen = false" aria-label="Tutup menu">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            @php
                $baseNav = 'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition';
                $activeNav = 'bg-amber-400 text-slate-950 shadow-sm';
                $inactiveNav = 'text-slate-600 hover:bg-amber-50 hover:text-slate-950';
            @endphp
            <nav class="min-h-0 flex-1 space-y-1 overflow-y-auto overscroll-contain px-4 py-6">
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('dashboard.admin') }}" class="{{ $baseNav }} {{ request()->routeIs('dashboard.admin') ? $activeNav : $inactiveNav }}">
                        <i class="fas fa-chart-pie w-5 text-center"></i><span>Dashboard</span>
                    </a>
                    <a href="{{ route('admin.pemesanan.index') }}" class="{{ $baseNav }} {{ request()->routeIs('admin.pemesanan.*', 'admin.proyek.*') ? $activeNav : $inactiveNav }}">
                        <i class="fas fa-clipboard-list w-5 text-center"></i><span>Kelola Pesanan</span>
                    </a>
                    <a href="{{ route('admin.katalog.index') }}" class="{{ $baseNav }} {{ request()->routeIs('admin.katalog.*', 'admin.categories.*') ? $activeNav : $inactiveNav }}">
                        <i class="fas fa-images w-5 text-center"></i><span>Kelola Katalog</span>
                    </a>
                    <a href="{{ route('admin.faq.index') }}" class="{{ $baseNav }} {{ request()->routeIs('admin.faq.*') ? $activeNav : $inactiveNav }}">
                        <i class="fas fa-circle-question w-5 text-center"></i><span>Kelola FAQ</span>
                    </a>
                    <a href="{{ route('admin.pelanggan.index') }}" class="{{ $baseNav }} {{ request()->routeIs('admin.pelanggan.*') ? $activeNav : $inactiveNav }}">
                        <i class="fas fa-address-book w-5 text-center"></i><span>Kelola Pelanggan</span>
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="{{ $baseNav }} {{ request()->routeIs('admin.users.*') ? $activeNav : $inactiveNav }}">
                        <i class="fas fa-user-shield w-5 text-center"></i><span>Manajemen User</span>
                    </a>
                @else
                    <a href="{{ route('dashboard.designer') }}" class="{{ $baseNav }} {{ request()->routeIs('dashboard.designer') ? $activeNav : $inactiveNav }}">
                        <i class="fas fa-chart-pie w-5 text-center"></i><span>Dashboard</span>
                    </a>
                    <a href="{{ route('designer.projects.index') }}" class="{{ $baseNav }} {{ request()->routeIs('designer.projects.*') ? $activeNav : $inactiveNav }}">
                        <i class="fas fa-clipboard-list w-5 text-center"></i><span>Proyek Saya</span>
                    </a>
                    <a href="{{ route('admin.katalog.index') }}" class="{{ $baseNav }} {{ request()->routeIs('admin.katalog.*', 'admin.categories.*') ? $activeNav : $inactiveNav }}">
                        <i class="fas fa-images w-5 text-center"></i><span>Kelola Katalog</span>
                    </a>
                @endif
            </nav>

            <div class="border-t border-slate-100 p-4">
                <div class="flex items-center gap-3 rounded-xl bg-slate-50 p-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-400 font-bold text-slate-950">
                        {{ Str::upper(Str::substr(auth()->user()->nama, 0, 1)) }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold">{{ auth()->user()->nama }}</p>
                        <p class="text-xs capitalize text-slate-500">{{ auth()->user()->role }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-red-600 transition hover:bg-red-50" aria-label="Keluar" title="Keluar">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="flex h-full min-w-0 flex-1 flex-col overflow-hidden">
            <header class="z-30 shrink-0 border-b border-slate-200 bg-white/95 px-4 py-4 backdrop-blur sm:px-6">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex min-w-0 items-center gap-3">
                        <button type="button" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-slate-200 text-slate-600 lg:hidden" @click="sidebarOpen = true" aria-label="Buka menu">
                            <i class="fas fa-bars"></i>
                        </button>
                        <div class="min-w-0">
                            <h1 class="truncate text-xl font-semibold text-slate-950 sm:text-2xl">@yield('page-title')</h1>
                            <p class="hidden truncate text-sm text-slate-500 sm:block">@yield('page-description')</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        @include('partials.notifications')
                    </div>
                </div>
            </header>

            <main class="min-h-0 flex-1 overflow-y-auto overscroll-contain p-4 sm:p-6 lg:p-8">
                @if($errors->any())
                    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                        <p class="font-semibold">Periksa kembali data berikut:</p>
                        <ul class="mt-2 list-disc pl-5"><li>{{ $errors->first() }}</li></ul>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    <div
        x-data="{
            open: false,
            pendingForm: null,
            onConfirm: null,
            title: 'Konfirmasi tindakan',
            message: 'Apakah Anda yakin ingin melanjutkan?',
            confirmLabel: 'Konfirmasi',
            tone: 'primary',
            submitting: false,
            show(detail) {
                this.pendingForm = detail.form || null;
                this.onConfirm = detail.onConfirm || null;
                this.title = detail.title || 'Konfirmasi tindakan';
                this.message = detail.message || 'Apakah Anda yakin ingin melanjutkan?';
                this.confirmLabel = detail.confirmLabel || 'Konfirmasi';
                this.tone = detail.tone || 'primary';
                this.submitting = false;
                this.open = true;
                this.$nextTick(() => this.$refs.cancelButton.focus());
            },
            close() {
                if (this.submitting) return;
                this.open = false;
                this.pendingForm = null;
                this.onConfirm = null;
            },
            confirm() {
                if (this.submitting) return;
                if (this.onConfirm) {
                    this.submitting = true;
                    Promise.resolve(this.onConfirm()).finally(() => {
                        this.submitting = false;
                        this.open = false;
                        this.onConfirm = null;
                    });
                    return;
                }
                if (!this.pendingForm) return;
                this.submitting = true;
                this.pendingForm.submit();
            }
        }"
        x-cloak
        x-show="open"
        @open-confirmation.window="show($event.detail)"
        @keydown.escape.window="close()"
        class="fixed inset-0 z-120 flex items-center justify-center p-4 sm:p-6"
        role="presentation"
    >
        <div
            x-show="open"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="absolute inset-0 bg-slate-950/55 backdrop-blur-[2px]"
            @click="close()"
        ></div>

        <section
            x-show="open"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="translate-y-3 scale-95 opacity-0"
            x-transition:enter-end="translate-y-0 scale-100 opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="translate-y-0 scale-100 opacity-100"
            x-transition:leave-end="translate-y-2 scale-95 opacity-0"
            class="relative w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl"
            role="alertdialog"
            aria-modal="true"
            aria-labelledby="confirmation-dialog-title"
            aria-describedby="confirmation-dialog-message"
            @click.stop
        >
            <div class="px-6 pb-5 pt-6 sm:px-7 sm:pt-7">
                <h2 id="confirmation-dialog-title" class="text-xl font-bold text-slate-950" x-text="title"></h2>
                <p id="confirmation-dialog-message" class="mt-2 text-sm leading-6 text-slate-600" x-text="message"></p>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4 sm:flex-row sm:justify-end sm:px-7">
                <button
                    x-ref="cancelButton"
                    type="button"
                    class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-2"
                    @click="close()"
                    :disabled="submitting"
                >
                    Batal
                </button>
                <button
                    type="button"
                    class="inline-flex h-11 min-w-32 items-center justify-center gap-2 rounded-xl px-5 text-sm font-semibold text-white shadow-sm transition focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:cursor-wait disabled:opacity-70"
                    :class="tone === 'danger' ? 'bg-red-600 hover:bg-red-700 focus:ring-red-500' : (tone === 'success' ? 'bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500' : 'bg-slate-950 hover:bg-slate-800 focus:ring-slate-700')"
                    @click="confirm()"
                    :disabled="submitting"
                >
                    <i x-show="submitting" class="fas fa-circle-notch fa-spin" aria-hidden="true"></i>
                    <span x-text="submitting ? 'Memproses...' : confirmLabel"></span>
                </button>
            </div>
        </section>
    </div>

    @include('partials.notification-card')

    @stack('scripts')
</body>
</html>
