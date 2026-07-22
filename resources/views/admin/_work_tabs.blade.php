<nav class="mb-6 flex w-fit max-w-full gap-1 overflow-x-auto rounded-xl border border-slate-200 bg-white p-1 shadow-sm" aria-label="Navigasi pekerjaan">
    <a href="{{ route('admin.pemesanan.index') }}"
       class="whitespace-nowrap rounded-lg px-4 py-2 text-sm font-semibold transition {{ request()->routeIs('admin.pemesanan.*') ? 'bg-slate-950 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
        Permintaan &amp; Pesanan
    </a>
    <a href="{{ route('admin.proyek.index') }}"
       class="whitespace-nowrap rounded-lg px-4 py-2 text-sm font-semibold transition {{ request()->routeIs('admin.proyek.*') ? 'bg-slate-950 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
        Status Proyek
    </a>
</nav>
