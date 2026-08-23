@extends('layouts.dashboard')

@section('title', 'Edit Katalog - Admin Dashboard')
@section('page-title', 'Edit Katalog')
@section('page-description', 'Perbarui informasi, media, dan status publikasi desain')

@section('content')
<form action="{{ route('admin.katalog.update', $katalog) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @include('admin.katalog._form', ['submitLabel' => 'Simpan Perubahan'])
</form>
@endsection
