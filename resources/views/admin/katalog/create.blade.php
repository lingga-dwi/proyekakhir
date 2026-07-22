@extends('layouts.dashboard')

@section('title', 'Tambah Katalog - Admin Dashboard')
@section('page-title', 'Tambah Katalog')
@section('page-description', 'Tambahkan portofolio desain baru dan atur publikasinya')

@section('content')
<form action="{{ route('admin.katalog.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @include('admin.katalog._form', ['submitLabel' => 'Simpan Katalog'])
</form>
@endsection
