@extends('layouts.main')

@section('title', 'Buat Pesanan - Daiku Interior')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-purple-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo/image.png') }}" alt="Daiku Interior" class="h-6 mx-auto mb-4">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Buat Pesanan Interior</h1>
            <p class="text-gray-600">Isi formulir di bawah untuk memulai proyek desain interior Anda</p>
        </div>
        
        <!-- Single Form -->
        <div class="bg-white rounded-xl shadow-lg p-8">
            <form method="POST" action="{{ route('pemesanan.store') }}" enctype="multipart/form-data" class="space-y-8">
                @csrf
                @if($katalog ?? null)
                    <input type="hidden" name="katalog_id" value="{{ $katalog->id }}">
                    
                    <!-- Katalog Reference -->
                    <div class="bg-blue-50 rounded-lg p-6 border border-blue-200">
                        <h3 class="text-lg font-semibold text-blue-800 mb-2">Desain yang Dipilih</h3>
                        <div class="flex items-center space-x-4">
                            @if($katalog->gambar_utama_url)
                                <img src="{{ $katalog->gambar_utama_url }}" alt="{{ $katalog->nama_desain }}" class="w-20 h-20 object-cover rounded-lg">
                            @endif
                            <div>
                                <p class="font-medium text-gray-800">{{ $katalog->nama_desain }}</p>
                                <p class="text-sm text-gray-600">{{ $katalog->category->name ?? $katalog->kategori }}</p>
                                <p class="text-blue-600 font-semibold">{{ $katalog->getFormattedHargaAttribute() }}</p>
                            </div>
                        </div>
                    </div>
                @endif
                
                <!-- Informasi Pelanggan -->
                <div class="space-y-6">
                    <div class="border-b border-gray-200 pb-4">
                        <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-user text-blue-600 mr-3"></i>
                            Informasi Pelanggan
                        </h2>
                        <p class="text-gray-600 mt-1">Data kontak dan informasi dasar</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="nama" 
                                   value="{{ old('nama', auth()->user()->nama ?? '') }}"
                                   placeholder="Masukkan nama lengkap" 
                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-gray-100 text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition duration-200 @error('nama') border-red-500 @enderror"
                                   readonly
                                   required>
                            <p class="text-xs text-gray-500 mt-1">Mengikuti data akun yang sedang login.</p>
                            @error('nama')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                No. HP <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" 
                                   name="no_hp" 
                                   value="{{ old('no_hp', auth()->user()->no_telp ?? '') }}"
                                   placeholder="08xxxxxxxxxx" 
                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 {{ filled(auth()->user()->no_telp) ? 'bg-gray-100 text-gray-700' : 'bg-white text-gray-900' }} focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition duration-200 @error('no_hp') border-red-500 @enderror"
                                   @readonly(filled(auth()->user()->no_telp))
                                   required>
                            <p class="text-xs text-gray-500 mt-1">{{ filled(auth()->user()->no_telp) ? 'Nomor telepon mengikuti profil akun.' : 'Isi sekali untuk melengkapi kontak proyek.' }}</p>
                            @error('no_hp')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Alamat Lengkap <span class="text-red-500">*</span>
                            </label>
                            <textarea name="alamat" 
                                      rows="3"
                                      placeholder="Masukkan alamat lengkap proyek"
                                      class="w-full px-4 py-3 rounded-lg border border-gray-300 {{ filled(auth()->user()->alamat) ? 'bg-gray-100 text-gray-700' : 'bg-white text-gray-900' }} focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition duration-200 @error('alamat') border-red-500 @enderror"
                                      @readonly(filled(auth()->user()->alamat))
                                      required>{{ old('alamat', auth()->user()->alamat ?? '') }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">{{ filled(auth()->user()->alamat) ? 'Alamat mengikuti data akun agar histori proyek konsisten.' : 'Isi alamat lokasi proyek untuk melengkapi profil.' }}</p>
                            @error('alamat')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" 
                                   name="email" 
                                   value="{{ old('email', auth()->user()->email ?? '') }}"
                                   placeholder="nama@email.com" 
                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-gray-100 text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition duration-200 @error('email') border-red-500 @enderror"
                                   readonly
                                   required>
                            <p class="text-xs text-gray-500 mt-1">Email login tidak diubah saat membuat pesanan.</p>
                            @error('email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Detail Proyek -->
                <div class="space-y-6">
                    <div class="border-b border-gray-200 pb-4">
                        <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-building text-blue-600 mr-3"></i>
                            Detail Proyek
                        </h2>
                        <p class="text-gray-600 mt-1">Informasi mengenai proyek interior yang diinginkan</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Jenis Proyek <span class="text-red-500">*</span>
                            </label>
                            <select name="jenis_proyek" 
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition duration-200 @error('jenis_proyek') border-red-500 @enderror"
                                    required>
                                <option value="">Pilih Jenis Proyek</option>
                                <option value="renovasi_total" {{ old('jenis_proyek') == 'renovasi_total' ? 'selected' : '' }}>Renovasi Total</option>
                                <option value="renovasi_sebagian" {{ old('jenis_proyek') == 'renovasi_sebagian' ? 'selected' : '' }}>Renovasi Sebagian</option>
                                <option value="desain_baru" {{ old('jenis_proyek') == 'desain_baru' ? 'selected' : '' }}>Desain Baru</option>
                                <option value="furnishing" {{ old('jenis_proyek') == 'furnishing' ? 'selected' : '' }}>Furnishing Only</option>
                            </select>
                            @error('jenis_proyek')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Jenis Bangunan <span class="text-red-500">*</span>
                            </label>
                            <select name="jenis_bangunan" 
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition duration-200 @error('jenis_bangunan') border-red-500 @enderror"
                                    required>
                                <option value="">Pilih Jenis Bangunan</option>
                                <option value="rumah_tinggal" {{ old('jenis_bangunan') == 'rumah_tinggal' ? 'selected' : '' }}>Rumah Tinggal</option>
                                <option value="apartemen" {{ old('jenis_bangunan') == 'apartemen' ? 'selected' : '' }}>Apartemen</option>
                                <option value="ruko" {{ old('jenis_bangunan') == 'ruko' ? 'selected' : '' }}>Ruko</option>
                                <option value="kantor" {{ old('jenis_bangunan') == 'kantor' ? 'selected' : '' }}>Kantor</option>
                                <option value="cafe_restoran" {{ old('jenis_bangunan') == 'cafe_restoran' ? 'selected' : '' }}>Cafe/Restoran</option>
                                <option value="hotel" {{ old('jenis_bangunan') == 'hotel' ? 'selected' : '' }}>Hotel</option>
                            </select>
                            @error('jenis_bangunan')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Luas Area (m2) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   name="luas_area" 
                                   value="{{ old('luas_area') }}"
                                   placeholder="Contoh: 50" 
                                   min="1"
                                   step="0.1"
                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition duration-200 @error('luas_area') border-red-500 @enderror"
                                   required>
                            @error('luas_area')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Jumlah Ruangan <span class="text-red-500">*</span>
                            </label>
                            <select name="jumlah_ruangan" 
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition duration-200 @error('jumlah_ruangan') border-red-500 @enderror"
                                    required>
                                <option value="">Pilih Jumlah Ruangan</option>
                                <option value="1" {{ old('jumlah_ruangan') == '1' ? 'selected' : '' }}>1 Ruangan</option>
                                <option value="2" {{ old('jumlah_ruangan') == '2' ? 'selected' : '' }}>2 Ruangan</option>
                                <option value="3" {{ old('jumlah_ruangan') == '3' ? 'selected' : '' }}>3 Ruangan</option>
                                <option value="4" {{ old('jumlah_ruangan') == '4' ? 'selected' : '' }}>4 Ruangan</option>
                                <option value="5" {{ old('jumlah_ruangan') == '5' ? 'selected' : '' }}>5 Ruangan</option>
                                <option value="6" {{ old('jumlah_ruangan') == '6' ? 'selected' : '' }}>6+ Ruangan</option>
                            </select>
                            @error('jumlah_ruangan')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Preferensi Desain -->
                <div class="space-y-6">
                    <div class="border-b border-gray-200 pb-4">
                        <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-palette text-blue-600 mr-3"></i>
                            Preferensi Desain
                        </h2>
                        <p class="text-gray-600 mt-1">Gaya desain dan keinginan khusus</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Gaya Desain Preferensi
                            </label>
                            <select name="gaya_desain_preferensi" 
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition duration-200 @error('gaya_desain_preferensi') border-red-500 @enderror">
                                <option value="">Pilih Gaya Desain</option>
                                <option value="modern_minimalis" {{ old('gaya_desain_preferensi') == 'modern_minimalis' ? 'selected' : '' }}>Modern Minimalis</option>
                                <option value="skandinavia" {{ old('gaya_desain_preferensi') == 'skandinavia' ? 'selected' : '' }}>Skandinavia</option>
                                <option value="industrial" {{ old('gaya_desain_preferensi') == 'industrial' ? 'selected' : '' }}>Industrial</option>
                                <option value="klasik" {{ old('gaya_desain_preferensi') == 'klasik' ? 'selected' : '' }}>Klasik</option>
                                <option value="kontemporer" {{ old('gaya_desain_preferensi') == 'kontemporer' ? 'selected' : '' }}>Kontemporer</option>
                                <option value="tradisional" {{ old('gaya_desain_preferensi') == 'tradisional' ? 'selected' : '' }}>Tradisional</option>
                                <option value="bohemian" {{ old('gaya_desain_preferensi') == 'bohemian' ? 'selected' : '' }}>Bohemian</option>
                                <option value="art_deco" {{ old('gaya_desain_preferensi') == 'art_deco' ? 'selected' : '' }}>Art Deco</option>
                            </select>
                            @error('gaya_desain_preferensi')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Warna Dominan
                            </label>
                            <select name="warna_dominan" 
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition duration-200 @error('warna_dominan') border-red-500 @enderror">
                                <option value="">Pilih Warna Dominan</option>
                                <option value="putih" {{ old('warna_dominan') == 'putih' ? 'selected' : '' }}>Putih</option>
                                <option value="abu_abu" {{ old('warna_dominan') == 'abu_abu' ? 'selected' : '' }}>Abu-abu</option>
                                <option value="hitam" {{ old('warna_dominan') == 'hitam' ? 'selected' : '' }}>Hitam</option>
                                <option value="coklat" {{ old('warna_dominan') == 'coklat' ? 'selected' : '' }}>Coklat</option>
                                <option value="krem" {{ old('warna_dominan') == 'krem' ? 'selected' : '' }}>Krem</option>
                                <option value="biru" {{ old('warna_dominan') == 'biru' ? 'selected' : '' }}>Biru</option>
                                <option value="hijau" {{ old('warna_dominan') == 'hijau' ? 'selected' : '' }}>Hijau</option>
                                <option value="netral" {{ old('warna_dominan') == 'netral' ? 'selected' : '' }}>Netral/Earth Tone</option>
                            </select>
                            @error('warna_dominan')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Deskripsi Keinginan Desain
                            </label>
                            <textarea name="deskripsi_keinginan_desain" 
                                      rows="4"
                                      placeholder="Jelaskan keinginan, kebutuhan khusus, atau referensi desain yang Anda inginkan..."
                                      class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition duration-200 @error('deskripsi_keinginan_desain') border-red-500 @enderror">{{ old('deskripsi_keinginan_desain') }}</textarea>
                            @error('deskripsi_keinginan_desain')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Upload Denah/Foto Ruangan
                                <span class="text-sm text-gray-500">(Opsional - Maksimal 5 file, masing-masing max 5MB)</span>
                            </label>
                            <input type="file" 
                                   name="upload_denah_foto[]" 
                                   multiple
                                   accept="image/*,.pdf"
                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition duration-200 @error('upload_denah_foto') border-red-500 @enderror">
                            <p class="text-sm text-gray-500 mt-1">Format yang diterima: JPG, PNG, PDF. Ini akan membantu kami memahami kondisi eksisting ruangan.</p>
                            @error('upload_denah_foto')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Konfirmasi -->
                <div class="space-y-6">
                    <div class="border-b border-gray-200 pb-4">
                        <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-check-circle text-blue-600 mr-3"></i>
                            Konfirmasi
                        </h2>
                        <p class="text-gray-600 mt-1">Pastikan semua informasi sudah benar</p>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-6">
                        <div class="flex items-start space-x-3">
                            <input type="checkbox" 
                                   id="terms" 
                                   name="terms" 
                                   class="mt-1 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                                   required>
                            <label for="terms" class="text-sm text-gray-700">
                                Saya setuju dengan <a href="#" class="text-blue-600 hover:text-blue-700 underline">syarat dan ketentuan</a> serta <a href="#" class="text-blue-600 hover:text-blue-700 underline">kebijakan privasi</a> yang berlaku.
                            </label>
                        </div>
                        @error('terms')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="bg-blue-50 rounded-lg p-6 border border-blue-200">
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-info-circle text-blue-600 mt-1"></i>
                            <div class="text-sm text-blue-800">
                                <p class="font-medium mb-2">Informasi Penting:</p>
                                <ul class="space-y-1 text-blue-700">
                                    <li>• Tim kami akan menghubungi Anda dalam 1x24 jam untuk konsultasi awal</li>
                                    <li>• Survei lokasi akan dijadwalkan setelah konfirmasi awal</li>
                                    <li>• Estimasi waktu pengerjaan akan diberikan setelah survei</li>
                                    <li>• Semua data Anda aman dan terlindungi</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-6 border-t border-gray-200">
                    <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                        <a href="{{ route('katalog') }}" 
                           class="inline-flex items-center px-6 py-3 border border-gray-300 text-gray-700 bg-white rounded-lg hover:bg-gray-50 transition duration-200">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Kembali ke Katalog
                        </a>
                        
                        <button type="submit" 
                                class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-purple-700 transform hover:scale-105 transition duration-200 shadow-lg">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Kirim Permintaan Pemesanan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Auto-format phone number
document.querySelector('input[name="no_hp"]').addEventListener('input', function(e) {
    if (e.target.hasAttribute('readonly')) {
        return;
    }

    let value = e.target.value.replace(/\D/g, '');
    if (value.startsWith('62')) {
        value = '0' + value.substring(2);
    }
    e.target.value = value;
});

// File upload validation
document.querySelector('input[name="upload_denah_foto[]"]').addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    const maxFiles = 5;
    const maxSize = 5 * 1024 * 1024; // 5MB
    
    if (files.length > maxFiles) {
        alert(`Maksimal ${maxFiles} file yang dapat diupload`);
        e.target.value = '';
        return;
    }
    
    for (let file of files) {
        if (file.size > maxSize) {
            alert(`File ${file.name} terlalu besar. Maksimal 5MB per file.`);
            e.target.value = '';
            return;
        }
    }
});
</script>
@endsection
