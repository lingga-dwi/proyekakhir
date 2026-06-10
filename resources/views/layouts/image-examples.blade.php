{{-- CONTOH PENGGUNAAN GAMBAR DI LARAVEL --}}

{{-- 1. GAMBAR STATIC (Logo, Icon, Background) --}}
{{-- Letakkan di: public/images/ --}}

<!-- Logo -->
<img src="{{ asset('images/logo/daiku-logo.png') }}" alt="Daiku Logo" class="h-8">

<!-- Background Hero -->
<div style="background-image: url('{{ asset('images/backgrounds/hero-bg.jpg') }}')">
    <!-- Content -->
</div>

<!-- Icon -->
<img src="{{ asset('images/icons/phone-icon.svg') }}" alt="Phone" class="w-6 h-6">


{{-- 2. GAMBAR UPLOAD (Katalog, User Upload) --}}
{{-- Letakkan di: storage/app/public/ --}}

<!-- Gambar Katalog (dari database) -->
@if($katalog->gambar_utama)
    <img src="{{ Storage::url($katalog->gambar_utama) }}" alt="{{ $katalog->nama_desain }}">
@else
    <img src="{{ asset('images/placeholder/no-image.jpg') }}" alt="No Image">
@endif

<!-- Upload Denah Customer -->
@if($pemesanan->upload_denah_foto)
    @foreach($pemesanan->upload_denah_foto as $file)
        <img src="{{ Storage::url($file) }}" alt="Denah">
    @endforeach
@endif


{{-- 3. FALLBACK/PLACEHOLDER --}}
{{-- Untuk gambar yang mungkin tidak ada --}}

<img src="{{ $katalog->gambar_utama ? Storage::url($katalog->gambar_utama) : asset('images/placeholder/katalog-placeholder.jpg') }}" 
     alt="{{ $katalog->nama_desain }}"
     class="w-full h-48 object-cover">


{{-- 4. FORM UPLOAD --}}
{{-- Input untuk upload gambar --}}

<form action="{{ route('admin.katalog.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    
    <!-- Single Image Upload -->
    <input type="file" name="gambar_utama" accept="image/*" class="form-control">
    
    <!-- Multiple Images Upload -->
    <input type="file" name="galeri_gambar[]" multiple accept="image/*" class="form-control">
</form>


{{-- 5. OPTIMASI GAMBAR --}}
{{-- Berbagai ukuran untuk responsive --}}

<picture>
    <!-- Mobile -->
    <source media="(max-width: 768px)" 
            srcset="{{ asset('images/backgrounds/hero-mobile.jpg') }}">
    
    <!-- Desktop -->
    <source media="(min-width: 769px)" 
            srcset="{{ asset('images/backgrounds/hero-desktop.jpg') }}">
    
    <!-- Fallback -->
    <img src="{{ asset('images/backgrounds/hero-desktop.jpg') }}" 
         alt="Hero Background" 
         class="w-full h-screen object-cover">
</picture>
