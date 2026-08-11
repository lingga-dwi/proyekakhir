@extends('layouts.dashboard')

@section('title', 'Ubah FAQ - Daiku Interior')
@section('page-title', 'Ubah FAQ')
@section('page-description', 'Perbarui pertanyaan umum yang tampil di Beranda')

@section('content')
<div class="mx-auto max-w-3xl">
    <form method="POST" action="{{ route('admin.faq.update', $faq) }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
        @csrf
        @method('PUT')
        @include('admin.faq._form')
        <div class="mt-7 flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.faq.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</a>
            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-amber-400 px-5 py-3 text-sm font-semibold text-slate-950 hover:bg-amber-300">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
