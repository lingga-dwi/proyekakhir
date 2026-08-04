@extends('layouts.auth')

@section('title', 'Verifikasi Email - Daiku Interior')

@section('content')
<div class="rounded-2xl border border-slate-100 bg-white p-8 shadow-sm sm:p-10">
    <img src="{{ asset('images/logo/image.png') }}" alt="Daiku Interior" class="mb-8 h-7">

    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-xl text-amber-700">
        <i class="fas fa-envelope" aria-hidden="true"></i>
    </div>

    <h1 class="mt-6 text-2xl font-bold text-slate-900">Verifikasi email Anda</h1>
    <p class="mt-3 leading-relaxed text-slate-600">
        Kami telah mengirim tautan verifikasi ke <strong class="text-slate-800">{{ auth()->user()->email }}</strong>.
        Buka email tersebut dan klik tautannya sebelum membuat permintaan desain.
    </p>

    @if (session('status'))
        <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}" class="mt-7">
        @csrf
        <button type="submit" class="w-full rounded-xl bg-amber-400 px-5 py-3.5 font-semibold text-slate-950 transition hover:bg-amber-300">
            Kirim ulang email verifikasi
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-4 text-center">
        @csrf
        <button type="submit" class="text-sm font-semibold text-slate-600 underline underline-offset-4 hover:text-slate-900">
            Gunakan akun lain
        </button>
    </form>
</div>
@endsection
