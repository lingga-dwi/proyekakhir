@extends('layouts.dashboard')

@section('title', 'Manajemen User - Admin Dashboard')
@section('page-title', 'Manajemen User')
@section('page-description', 'Kelola akun pengguna dan hak akses')

@section('content')
@php
    $cards = [
        ['label' => 'Total Pengguna', 'value' => $userStats['total'], 'tone' => 'bg-blue-100 text-blue-700', 'icon' => 'fa-users'],
        ['label' => 'Admin', 'value' => $userStats['admin'], 'tone' => 'bg-red-100 text-red-700', 'icon' => 'fa-user-shield'],
        ['label' => 'Desainer', 'value' => $userStats['designer'], 'tone' => 'bg-purple-100 text-purple-700', 'icon' => 'fa-pencil-ruler'],
        ['label' => 'Pelanggan', 'value' => $userStats['pelanggan'], 'tone' => 'bg-green-100 text-green-700', 'icon' => 'fa-user'],
    ];
    $roleLabels = ['admin' => 'Admin', 'designer' => 'Desainer', 'pelanggan' => 'Pelanggan'];
@endphp

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach($cards as $card)
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="flex items-center gap-4">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl {{ $card['tone'] }}"><i class="fas {{ $card['icon'] }}"></i></span>
            <div><p class="text-sm text-slate-500">{{ $card['label'] }}</p><p class="text-2xl font-bold text-slate-950">{{ $card['value'] }}</p></div>
        </div></article>
    @endforeach
</div>

<div class="mt-6 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm lg:flex-row lg:items-center">
    <form method="GET" class="grid flex-1 gap-3 md:grid-cols-[minmax(0,1fr)_220px_auto_auto]">
        <label class="relative"><span class="sr-only">Cari pengguna</span><i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i><input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..." class="w-full rounded-xl border-slate-300 py-2.5 pl-10 pr-4 focus:border-amber-500 focus:ring-amber-500"></label>
        <select name="role" class="rounded-xl border-slate-300 px-4 py-2.5 focus:border-amber-500 focus:ring-amber-500"><option value="">Semua role</option>@foreach($roleLabels as $value => $label)<option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>@endforeach</select>
        <button class="rounded-xl bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Terapkan</button>
        @if(request()->hasAny(['search', 'role']))<a href="{{ route('admin.users.index') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100">Reset</a>@endif
    </form>
    <button type="button" onclick="openUserModal()" class="inline-flex items-center justify-center rounded-xl bg-amber-400 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-amber-300"><i class="fas fa-plus mr-2"></i>Tambah Pengguna</button>
</div>

<section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-5 py-4"><h2 class="font-semibold text-slate-950">Daftar Pengguna</h2><p class="text-xs text-slate-500">Menampilkan {{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} pengguna</p></div>
    <div class="overflow-x-auto"><table class="w-full min-w-[820px]">
        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-5 py-3 font-medium">Nama Pengguna</th><th class="px-5 py-3 font-medium">Email</th><th class="px-5 py-3 font-medium">Role</th><th class="px-5 py-3 font-medium">Tanggal Daftar</th><th class="px-5 py-3 font-medium">Aksi</th></tr></thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($users as $user)
                @php $roleClass = match($user->role) {'admin' => 'bg-red-100 text-red-800', 'designer' => 'bg-purple-100 text-purple-800', default => 'bg-green-100 text-green-800'}; @endphp
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-4"><div class="flex items-center gap-3"><span class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-sm font-bold text-slate-600">{{ strtoupper(substr($user->nama, 0, 1)) }}</span><div><p class="text-sm font-semibold text-slate-900">{{ $user->nama }}</p>@if($user->is(auth()->user()))<p class="text-xs text-amber-700">Akun Anda</p>@endif</div></div></td>
                    <td class="px-5 py-4 text-sm text-slate-600">{{ $user->email }}</td>
                    <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $roleClass }}">{{ $roleLabels[$user->role] }}</span></td>
                    <td class="px-5 py-4 text-sm text-slate-600">{{ $user->created_at->translatedFormat('d M Y') }}</td>
                    <td class="px-5 py-4"><div class="flex items-center gap-3"><button type="button" class="text-sm font-semibold text-blue-700 hover:text-blue-800" onclick='openUserModal({{ $user->id }}, @js($user->nama), @js($user->email), @js($user->role))'>Edit</button>
                        @unless($user->is(auth()->user()))<form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Hapus pengguna ini?')">@csrf @method('DELETE')<button class="text-sm font-semibold text-red-600 hover:text-red-700">Hapus</button></form>@endunless
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-5 py-14 text-center text-sm text-slate-500">Tidak ada pengguna yang sesuai filter.</td></tr>
            @endforelse
        </tbody>
    </table></div>
    @if($users->hasPages())<div class="border-t border-slate-100 px-5 py-4">{{ $users->links() }}</div>@endif
</section>

<div id="userModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 p-4" role="dialog" aria-modal="true" aria-labelledby="user-modal-title">
    <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
        <div class="flex items-center justify-between"><h2 id="user-modal-title" class="text-lg font-semibold text-slate-950">Tambah Pengguna</h2><button type="button" onclick="closeUserModal()" class="h-9 w-9 rounded-full text-slate-500 hover:bg-slate-100"><i class="fas fa-times"></i></button></div>
        <form id="userForm" method="POST" action="{{ route('admin.users.store') }}" class="mt-5 space-y-4">@csrf <input id="userMethod" type="hidden" name="_method" value="POST" disabled>
            <label class="block"><span class="text-sm font-medium text-slate-700">Nama</span><input id="userName" name="nama" required maxlength="255" class="mt-2 w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500"></label>
            <label class="block"><span class="text-sm font-medium text-slate-700">Email</span><input id="userEmail" name="email" type="email" required maxlength="255" class="mt-2 w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500"></label>
            <label class="block"><span class="text-sm font-medium text-slate-700">Role</span><select id="userRole" name="role" required class="mt-2 w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500">@foreach($roleLabels as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label>
            <label class="block"><span class="text-sm font-medium text-slate-700">Password <span id="passwordHint" class="font-normal text-slate-400"></span></span><input id="userPassword" name="password" type="password" minlength="8" autocomplete="new-password" class="mt-2 w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500"></label>
            <label class="block"><span class="text-sm font-medium text-slate-700">Konfirmasi password</span><input id="userPasswordConfirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" class="mt-2 w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500"></label>
            <div class="flex justify-end gap-3"><button type="button" onclick="closeUserModal()" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Batal</button><button class="rounded-xl bg-amber-400 px-4 py-2.5 text-sm font-semibold text-slate-950 hover:bg-amber-300">Simpan</button></div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openUserModal(id = null, name = '', email = '', role = 'pelanggan') {
    const editing = id !== null;
    document.getElementById('user-modal-title').textContent = editing ? 'Edit Pengguna' : 'Tambah Pengguna';
    document.getElementById('userForm').action = editing ? `{{ url('/admin/users') }}/${id}` : @js(route('admin.users.store'));
    document.getElementById('userMethod').disabled = !editing;
    document.getElementById('userMethod').value = 'PUT';
    document.getElementById('userName').value = name;
    document.getElementById('userEmail').value = email;
    document.getElementById('userRole').value = role;
    document.getElementById('userPassword').required = !editing;
    document.getElementById('userPasswordConfirmation').required = !editing;
    document.getElementById('userPassword').value = '';
    document.getElementById('userPasswordConfirmation').value = '';
    document.getElementById('passwordHint').textContent = editing ? '(kosongkan jika tidak diubah)' : '';
    const modal = document.getElementById('userModal'); modal.classList.remove('hidden'); modal.classList.add('flex');
}
function closeUserModal() { const modal = document.getElementById('userModal'); modal.classList.add('hidden'); modal.classList.remove('flex'); }
document.getElementById('userModal').addEventListener('click', event => { if (event.target.id === 'userModal') closeUserModal(); });
</script>
@endpush
