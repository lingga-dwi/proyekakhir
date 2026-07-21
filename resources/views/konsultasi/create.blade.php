@extends('layouts.main')

@section('title', 'Book Konsultasi')

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="container mx-auto px-4">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Book Konsultasi Interior</h1>
            <p class="text-gray-600">Isi form di bawah untuk menjadwalkan konsultasi dengan designer kami</p>
        </div>

        <!-- Form -->
        <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg p-8">
            <form action="{{ route('konsultasi.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Personal Info -->
                <div class="border-b pb-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Informasi Personal</h3>
                    <div class="grid md:grid-cols-3 gap-6">
                        <div>
                            <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                            <input type="text" id="nama" name="nama" value="{{ auth()->user()->nama ?? old('nama') }}" required readonly
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <p class="mt-1 text-xs text-gray-500">Mengikuti data akun yang sedang login.</p>
                            @error('nama')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" id="email" name="email" value="{{ auth()->user()->email ?? old('email') }}" required readonly
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <p class="mt-1 text-xs text-gray-500">Email konsultasi selalu mengikuti identitas akun.</p>
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="no_telp" class="block text-sm font-medium text-gray-700 mb-2">No. Telepon</label>
                            <input type="tel" id="no_telp" name="no_telp" value="{{ old('no_telp', auth()->user()->no_telp) }}" required
                                @readonly(filled(auth()->user()->no_telp))
                                placeholder="08xxxxxxxxxx"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg {{ filled(auth()->user()->no_telp) ? 'bg-gray-100 text-gray-700' : 'bg-white text-gray-900' }} focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <p class="mt-1 text-xs text-gray-500">
                                {{ filled(auth()->user()->no_telp) ? 'Mengikuti nomor telepon pada profil akun.' : 'Isi sekali agar tim Daiku dapat menghubungi Anda.' }}
                            </p>
                            @error('no_telp')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Consultation Type -->
                <div class="border-b pb-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Jenis Konsultasi</h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-300 transition duration-200">
                            <input type="radio" name="jenis_konsultasi" value="free_consultation" class="text-blue-600" required>
                            <div class="ml-3">
                                <div class="font-medium text-gray-800">Konsultasi Awal</div>
                                <div class="text-sm text-gray-600">Pembahasan kebutuhan dan arah proyek</div>
                            </div>
                        </label>

                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-300 transition duration-200">
                            <input type="radio" name="jenis_konsultasi" value="virtual_design" class="text-blue-600">
                            <div class="ml-3">
                                <div class="font-medium text-gray-800">Diskusi Desain Daring</div>
                                <div class="text-sm text-gray-600">Pembahasan desain dilakukan secara daring</div>
                            </div>
                        </label>

                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-300 transition duration-200">
                            <input type="radio" name="jenis_konsultasi" value="in_home_visit" class="text-blue-600">
                            <div class="ml-3">
                                <div class="font-medium text-gray-800">Survei Lokasi</div>
                                <div class="text-sm text-gray-600">Jadwal kunjungan dikonfirmasi setelah peninjauan awal</div>
                            </div>
                        </label>

                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-300 transition duration-200">
                            <input type="radio" name="jenis_konsultasi" value="chat_support" class="text-blue-600">
                            <div class="ml-3">
                                <div class="font-medium text-gray-800">Konsultasi Tertulis</div>
                                <div class="text-sm text-gray-600">Sampaikan kebutuhan proyek secara terstruktur</div>
                            </div>
                        </label>
                    </div>
                    @error('jenis_konsultasi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Project Details -->
                <div class="border-b pb-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Detail Proyek</h3>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label for="jenis_ruangan" class="block text-sm font-medium text-gray-700 mb-2">Jenis Ruangan</label>
                            <select id="jenis_ruangan" name="jenis_ruangan" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Pilih Ruangan</option>
                                <option value="living_room" {{ old('jenis_ruangan') == 'living_room' ? 'selected' : '' }}>Living Room</option>
                                <option value="bedroom" {{ old('jenis_ruangan') == 'bedroom' ? 'selected' : '' }}>Bedroom</option>
                                <option value="kitchen" {{ old('jenis_ruangan') == 'kitchen' ? 'selected' : '' }}>Kitchen</option>
                                <option value="bathroom" {{ old('jenis_ruangan') == 'bathroom' ? 'selected' : '' }}>Bathroom</option>
                                <option value="office" {{ old('jenis_ruangan') == 'office' ? 'selected' : '' }}>Home Office</option>
                                <option value="whole_house" {{ old('jenis_ruangan') == 'whole_house' ? 'selected' : '' }}>Seluruh Rumah</option>
                            </select>
                            @error('jenis_ruangan')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="luas_ruangan" class="block text-sm font-medium text-gray-700 mb-2">Luas Ruangan (m2)</label>
                            <input type="number" id="luas_ruangan" name="luas_ruangan" value="{{ old('luas_ruangan') }}" step="0.1" min="1"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('luas_ruangan')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="budget_range" class="block text-sm font-medium text-gray-700 mb-2">Budget Range</label>
                            <select id="budget_range" name="budget_range" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Pilih Budget</option>
                                <option value="under_10m" {{ old('budget_range') == 'under_10m' ? 'selected' : '' }}>Di bawah Rp 10 Juta</option>
                                <option value="10m_25m" {{ old('budget_range') == '10m_25m' ? 'selected' : '' }}>Rp 10 - 25 Juta</option>
                                <option value="25m_50m" {{ old('budget_range') == '25m_50m' ? 'selected' : '' }}>Rp 25 - 50 Juta</option>
                                <option value="50m_100m" {{ old('budget_range') == '50m_100m' ? 'selected' : '' }}>Rp 50 - 100 Juta</option>
                                <option value="above_100m" {{ old('budget_range') == 'above_100m' ? 'selected' : '' }}>Di atas Rp 100 Juta</option>
                            </select>
                            @error('budget_range')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="timeline" class="block text-sm font-medium text-gray-700 mb-2">Timeline Proyek</label>
                            <select id="timeline" name="timeline" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Pilih Timeline</option>
                                <option value="immediate" {{ old('timeline') == 'immediate' ? 'selected' : '' }}>Segera (1-2 minggu)</option>
                                <option value="1_month" {{ old('timeline') == '1_month' ? 'selected' : '' }}>1 Bulan</option>
                                <option value="3_months" {{ old('timeline') == '3_months' ? 'selected' : '' }}>3 Bulan</option>
                                <option value="6_months" {{ old('timeline') == '6_months' ? 'selected' : '' }}>6 Bulan</option>
                                <option value="flexible" {{ old('timeline') == 'flexible' ? 'selected' : '' }}>Fleksibel</option>
                            </select>
                            @error('timeline')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6">
                        <label for="gaya_preferensi" class="block text-sm font-medium text-gray-700 mb-2">Gaya Preferensi (Opsional)</label>
                        <input type="text" id="gaya_preferensi" name="gaya_preferensi" value="{{ old('gaya_preferensi') }}"
                            placeholder="Contoh: Modern minimalis, Industrial, Skandinavia, dll."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('gaya_preferensi')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Description & Photos -->
                <div class="border-b pb-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Deskripsi Kebutuhan</h3>
                    
                    <div class="mb-6">
                        <label for="deskripsi_kebutuhan" class="block text-sm font-medium text-gray-700 mb-2">Ceritakan kebutuhan dan keinginan Anda</label>
                        <textarea id="deskripsi_kebutuhan" name="deskripsi_kebutuhan" rows="5" required
                            placeholder="Jelaskan secara detail tentang keinginan desain, masalah yang ingin diselesaikan, inspirasi, atau hal khusus lainnya..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('deskripsi_kebutuhan') }}</textarea>
                        @error('deskripsi_kebutuhan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="upload_foto" class="block text-sm font-medium text-gray-700 mb-2">Upload Foto Ruangan (Opsional)</label>
                        <input type="file" id="upload_foto" name="upload_foto[]" multiple accept="image/*"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="mt-1 text-sm text-gray-500">Upload foto kondisi existing atau foto inspirasi (maksimal 2MB per file)</p>
                        @error('upload_foto.*')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Schedule -->
                <div class="border-b pb-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Jadwal Konsultasi</h3>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label for="tanggal_konsultasi" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Konsultasi</label>
                            <input type="date" id="tanggal_konsultasi" name="tanggal_konsultasi" value="{{ old('tanggal_konsultasi') }}" 
                                min="{{ date('Y-m-d', strtotime('+1 day')) }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('tanggal_konsultasi')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="waktu_konsultasi" class="block text-sm font-medium text-gray-700 mb-2">Waktu Konsultasi</label>
                            <select id="waktu_konsultasi" name="waktu_konsultasi" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Pilih Waktu</option>
                                <option value="09:00" {{ old('waktu_konsultasi') == '09:00' ? 'selected' : '' }}>09:00</option>
                                <option value="10:00" {{ old('waktu_konsultasi') == '10:00' ? 'selected' : '' }}>10:00</option>
                                <option value="11:00" {{ old('waktu_konsultasi') == '11:00' ? 'selected' : '' }}>11:00</option>
                                <option value="13:00" {{ old('waktu_konsultasi') == '13:00' ? 'selected' : '' }}>13:00</option>
                                <option value="14:00" {{ old('waktu_konsultasi') == '14:00' ? 'selected' : '' }}>14:00</option>
                                <option value="15:00" {{ old('waktu_konsultasi') == '15:00' ? 'selected' : '' }}>15:00</option>
                                <option value="16:00" {{ old('waktu_konsultasi') == '16:00' ? 'selected' : '' }}>16:00</option>
                                <option value="19:00" {{ old('waktu_konsultasi') == '19:00' ? 'selected' : '' }}>19:00</option>
                                <option value="20:00" {{ old('waktu_konsultasi') == '20:00' ? 'selected' : '' }}>20:00</option>
                            </select>
                            @error('waktu_konsultasi')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="text-center pt-6">
                    <button type="submit" 
                        class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold text-lg hover:bg-blue-700 transition duration-300">
                        Book Konsultasi Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Auto-fill user data if logged in
document.addEventListener('DOMContentLoaded', function() {
    // Update border color when radio is selected
    const radioInputs = document.querySelectorAll('input[type="radio"][name="jenis_konsultasi"]');
    radioInputs.forEach(radio => {
        radio.addEventListener('change', function() {
            // Reset all borders
            radioInputs.forEach(r => {
                r.closest('label').classList.remove('border-blue-500', 'bg-blue-50');
                r.closest('label').classList.add('border-gray-200');
            });
            
            // Highlight selected
            if (this.checked) {
                this.closest('label').classList.remove('border-gray-200');
                this.closest('label').classList.add('border-blue-500', 'bg-blue-50');
            }
        });
    });
});
</script>
@endsection

