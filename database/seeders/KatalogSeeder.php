<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class KatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoryMap = Category::pluck('id', 'name')->toArray();

        $katalogs = [
            // KAMAR TIDUR
            [
                'nama_desain' => 'Apartemen 2 Kamar Modern',
                'kategori' => 'Kamar Tidur',
                'deskripsi' => 'Apartemen 2 kamar tidur seluas 38,45 m² dengan konsep minimalis modern. Privasi dan batasan yang tepat untuk hidup bersama teman sekamar.',
                'harga_estimasi' => 85000000,
                'gambar_utama' => 'katalog/apartemen-2-kamar.jpg',
                'galeri_gambar' => ['katalog/apartemen-2-kamar-1.jpg', 'katalog/apartemen-2-kamar-2.jpg'],
                'product_spots' => [
                    ['x' => 25, 'y' => 15, 'product' => 'HEMNES - Tempat tidur, putih, 160x200 cm', 'price' => 'Rp 3.499.000'],
                    ['x' => 60, 'y' => 30, 'product' => 'BILLY - Rak buku, putih, 80x28x202 cm', 'price' => 'Rp 899.000'],
                    ['x' => 80, 'y' => 65, 'product' => 'MICKE - Meja kerja, putih, 105x50 cm', 'price' => 'Rp 1.299.000'],
                    ['x' => 40, 'y' => 80, 'product' => 'KALLAX - Unit rak, putih, 77x77 cm', 'price' => 'Rp 799.000']
                ],
                'style_tags' => 'Modern, Minimalis, Scandinavian',
                'room_size' => 38,
                'inspiration_story' => 'Waktu menyenangkan dengan teman sekamar di apartemen 2 kamar yang nyaman dan fungsional.'
            ],
            [
                'nama_desain' => 'Kamar Tidur Mahasiswa',
                'kategori' => 'Kamar Tidur',
                'deskripsi' => 'Kamar tidur multifungsi untuk belajar dan beristirahat. Dilengkapi solusi penyimpanan yang efisien dan area belajar yang nyaman.',
                'harga_estimasi' => 25000000,
                'gambar_utama' => 'katalog/kamar-mahasiswa.jpg',
                'galeri_gambar' => ['katalog/kamar-mahasiswa-1.jpg', 'katalog/kamar-mahasiswa-2.jpg'],
                'product_spots' => [
                    ['x' => 30, 'y' => 20, 'product' => 'MALM - Tempat tidur tinggi, putih, 90x200 cm', 'price' => 'Rp 1.799.000'],
                    ['x' => 70, 'y' => 40, 'product' => 'MICKE - Meja kerja, putih, 73x50 cm', 'price' => 'Rp 999.000'],
                    ['x' => 85, 'y' => 25, 'product' => 'IVAR - Unit rak, kayu pinus, 80x30x179 cm', 'price' => 'Rp 1.199.000'],
                    ['x' => 15, 'y' => 70, 'product' => 'SAMLA - Kotak dengan penutup, transparan, 57x39x28 cm', 'price' => 'Rp 149.000']
                ],
                'style_tags' => 'Fungsional, Minimalis, Student Life',
                'room_size' => 12,
                'inspiration_story' => 'Belajar dengan nyaman di rumah dengan solusi penyimpanan multifungsi yang mudah dibersihkan.'
            ],
            [
                'nama_desain' => 'Kamar Tidur Skandinavia',
                'kategori' => 'Kamar Tidur',
                'deskripsi' => 'Desain kamar tidur dengan gaya Skandinavia yang hangat dan nyaman. Menggunakan material kayu birch dan warna-warna terang.',
                'harga_estimasi' => 35000000,
                'gambar_utama' => 'katalog/kamar-skandinavia.jpg',
                'galeri_gambar' => ['katalog/kamar-skandinavia-1.jpg', 'katalog/kamar-skandinavia-2.jpg']
            ],
            [
                'nama_desain' => 'Kamar Tidur Industrial',
                'kategori' => 'Kamar Tidur',
                'deskripsi' => 'Kamar tidur dengan konsep industrial yang masculine. Perpaduan metal, kayu, dan concrete untuk kesan raw dan autentik.',
                'harga_estimasi' => 40000000,
                'gambar_utama' => 'katalog/kamar-industrial.jpg',
                'galeri_gambar' => ['katalog/kamar-industrial-1.jpg', 'katalog/kamar-industrial-2.jpg']
            ],

            // RUANG TAMU
            [
                'nama_desain' => 'Ruang Tamu Hangout',
                'kategori' => 'Ruang Tamu',
                'deskripsi' => 'Ruang tamu yang perfect untuk hangout dengan teman. Tempat untuk maraton film, membaca novel favorit, atau mengundang teman berkumpul.',
                'harga_estimasi' => 45000000,
                'gambar_utama' => 'katalog/ruang-tamu-hangout.jpg',
                'galeri_gambar' => ['katalog/ruang-tamu-hangout-1.jpg', 'katalog/ruang-tamu-hangout-2.jpg'],
                'product_spots' => [
                    ['x' => 35, 'y' => 45, 'product' => 'KIVIK - Sofa 3 dudukan, Hillared antrasit', 'price' => 'Rp 6.499.000'],
                    ['x' => 65, 'y' => 35, 'product' => 'HEMNES - Unit TV, cokelat muda, 148x47x57 cm', 'price' => 'Rp 2.299.000'],
                    ['x' => 20, 'y' => 25, 'product' => 'LACK - Meja samping, putih, 55x55 cm', 'price' => 'Rp 199.000'],
                    ['x' => 80, 'y' => 75, 'product' => 'BILLY - Rak buku, putih, 80x28x202 cm', 'price' => 'Rp 899.000']
                ],
                'style_tags' => 'Cozy, Entertainment, Social',
                'room_size' => 25,
                'inspiration_story' => 'Ruang untuk menikmati waktu yang menyenangkan dengan sentuhan warna primer yang memberikan kepribadian.'
            ],
            [
                'nama_desain' => 'Living Room Minimalis',
                'kategori' => 'Ruang Tamu',
                'deskripsi' => 'Ruang tamu minimalis dengan penyimpanan terbuka dan tertutup. Menampilkan koleksi favorit sambil menjaga privasi barang pribadi.',
                'harga_estimasi' => 50000000,
                'gambar_utama' => 'katalog/living-room-minimalis.jpg',
                'galeri_gambar' => ['katalog/living-room-minimalis-1.jpg', 'katalog/living-room-minimalis-2.jpg']
            ],
            [
                'nama_desain' => 'Ruang Keluarga Cozy',
                'kategori' => 'Ruang Tamu',
                'deskripsi' => 'Ruang keluarga yang hangat dan nyaman untuk berkumpul. Sentuhan warna primer memberikan kepribadian pada ruang.',
                'harga_estimasi' => 55000000,
                'gambar_utama' => 'katalog/ruang-keluarga-cozy.jpg',
                'galeri_gambar' => ['katalog/ruang-keluarga-cozy-1.jpg', 'katalog/ruang-keluarga-cozy-2.jpg']
            ],

            // DAPUR
            [
                'nama_desain' => 'Dapur Mini Apartment',
                'kategori' => 'Dapur',
                'deskripsi' => 'Dapur kecil dengan kombinasi warna birch dan putih. Menggunakan penyimpanan dinding untuk memaksimalkan ruang yang terbatas.',
                'harga_estimasi' => 30000000,
                'gambar_utama' => 'katalog/dapur-mini.jpg',
                'galeri_gambar' => ['katalog/dapur-mini-1.jpg', 'katalog/dapur-mini-2.jpg'],
                'product_spots' => [
                    ['x' => 15, 'y' => 35, 'product' => 'ENHET - Kabinet dinding dg 2 rak, putih, 60x32x75 cm', 'price' => 'Rp 1.425.000'],
                    ['x' => 45, 'y' => 50, 'product' => 'SUNNERSTA - Rak dinding dengan hook, putih', 'price' => 'Rp 149.000'],
                    ['x' => 70, 'y' => 60, 'product' => 'GODMORGON - Wastafel, putih, 60x32x10 cm', 'price' => 'Rp 699.000'],
                    ['x' => 85, 'y' => 30, 'product' => 'LERHYTTAN - Pintu, cokelat muda, 60x80 cm', 'price' => 'Rp 899.000']
                ],
                'style_tags' => 'Compact, Efficient, Scandinavian',
                'room_size' => 6,
                'inspiration_story' => 'Suasana hangat di dapur kecil dengan kombinasi warna birch dan putih yang selalu terlihat bersih.'
            ],
            [
                'nama_desain' => 'Dapur Modern Open Kitchen',
                'kategori' => 'Dapur',
                'deskripsi' => 'Dapur modern dengan konsep open kitchen yang terintegrasi dengan ruang makan. Perfect untuk memasak dan makan bersama.',
                'harga_estimasi' => 65000000,
                'gambar_utama' => 'katalog/dapur-modern.jpg',
                'galeri_gambar' => ['katalog/dapur-modern-1.jpg', 'katalog/dapur-modern-2.jpg']
            ],
            [
                'nama_desain' => 'Kitchen Set Kompak',
                'kategori' => 'Dapur',
                'deskripsi' => 'Kitchen set kompak dengan storage yang optimal. Semua peralatan tertata rapi dengan akses yang mudah untuk memasak sehari-hari.',
                'harga_estimasi' => 45000000,
                'gambar_utama' => 'katalog/kitchen-set.jpg',
                'galeri_gambar' => ['katalog/kitchen-set-1.jpg', 'katalog/kitchen-set-2.jpg']
            ],

            // KAMAR MANDI
            [
                'nama_desain' => 'Kamar Mandi Cerah',
                'kategori' => 'Kamar Mandi',
                'deskripsi' => 'Kamar mandi dengan warna terang yang mencerahkan suasana di pagi hari. Memberikan energi positif untuk memulai aktivitas.',
                'harga_estimasi' => 25000000,
                'gambar_utama' => 'katalog/kamar-mandi-cerah.jpg',
                'galeri_gambar' => ['katalog/kamar-mandi-cerah-1.jpg', 'katalog/kamar-mandi-cerah-2.jpg']
            ],
            [
                'nama_desain' => 'Bathroom Modern Luxury',
                'kategori' => 'Kamar Mandi',
                'deskripsi' => 'Kamar mandi mewah dengan konsep modern luxury. Dilengkapi dengan bathtub dan shower glass yang elegant.',
                'harga_estimasi' => 60000000,
                'gambar_utama' => 'katalog/bathroom-luxury.jpg',
                'galeri_gambar' => ['katalog/bathroom-luxury-1.jpg', 'katalog/bathroom-luxury-2.jpg']
            ],
            [
                'nama_desain' => 'Toilet Minimalis Compact',
                'kategori' => 'Kamar Mandi',
                'deskripsi' => 'Toilet minimalis untuk ruang terbatas dengan design yang compact dan fungsional. Setiap inch dimanfaatkan dengan optimal.',
                'harga_estimasi' => 18000000,
                'gambar_utama' => 'katalog/toilet-compact.jpg',
                'galeri_gambar' => ['katalog/toilet-compact-1.jpg', 'katalog/toilet-compact-2.jpg']
            ],

            // RUANG KERJA
            [
                'nama_desain' => 'Home Office Modern',
                'kategori' => 'Ruang Kerja',
                'deskripsi' => 'Ruang kerja di rumah yang produktif dan inspiratif. Dilengkapi dengan storage yang cukup dan pencahayaan yang optimal untuk bekerja.',
                'harga_estimasi' => 35000000,
                'gambar_utama' => 'katalog/home-office.jpg',
                'galeri_gambar' => ['katalog/home-office-1.jpg', 'katalog/home-office-2.jpg']
            ],
            [
                'nama_desain' => 'Study Corner',
                'kategori' => 'Ruang Kerja',
                'deskripsi' => 'Sudut belajar yang nyaman di kamar tidur. Belajar di rumah dengan solusi penyimpanan multifungsi yang mudah dibersihkan.',
                'harga_estimasi' => 15000000,
                'gambar_utama' => 'katalog/study-corner.jpg',
                'galeri_gambar' => ['katalog/study-corner-1.jpg', 'katalog/study-corner-2.jpg']
            ],

            // RUANG MAKAN
            [
                'nama_desain' => 'Ruang Makan Intimate',
                'kategori' => 'Ruang Makan',
                'deskripsi' => 'Ruang makan dengan suasana intimate untuk 2-4 orang. Perfect untuk makan bersama dalam suasana yang hangat dan cozy.',
                'harga_estimasi' => 28000000,
                'gambar_utama' => 'katalog/ruang-makan-intimate.jpg',
                'galeri_gambar' => ['katalog/ruang-makan-intimate-1.jpg', 'katalog/ruang-makan-intimate-2.jpg']
            ],
            [
                'nama_desain' => 'Dining Area Open Space',
                'kategori' => 'Ruang Makan',
                'deskripsi' => 'Area makan yang terintegrasi dengan ruang tamu. Konsep open space yang memaksimalkan interaksi dan komunikasi.',
                'harga_estimasi' => 40000000,
                'gambar_utama' => 'katalog/dining-open.jpg',
                'galeri_gambar' => ['katalog/dining-open-1.jpg', 'katalog/dining-open-2.jpg']
            ],

            // LUAR RUANG
            [
                'nama_desain' => 'Balkon Mini Garden',
                'kategori' => 'Luar Ruang',
                'deskripsi' => 'Balkon apartment yang disulap menjadi mini garden. Tempat yang perfect untuk refreshing dan menikmati udara segar.',
                'harga_estimasi' => 12000000,
                'gambar_utama' => 'katalog/balkon-garden.jpg',
                'galeri_gambar' => ['katalog/balkon-garden-1.jpg', 'katalog/balkon-garden-2.jpg']
            ],
            [
                'nama_desain' => 'Teras Outdoor Relax',
                'kategori' => 'Luar Ruang',
                'deskripsi' => 'Teras outdoor untuk bersantai dan mengadakan gathering kecil. Dilengkapi dengan furniture tahan cuaca.',
                'harga_estimasi' => 25000000,
                'gambar_utama' => 'katalog/teras-relax.jpg',
                'galeri_gambar' => ['katalog/teras-relax-1.jpg', 'katalog/teras-relax-2.jpg']
            ]
        ];

        foreach ($katalogs as $katalog) {
            $katalog['category_id'] = $categoryMap[$katalog['kategori']] ?? null;
            \App\Models\Katalog::create($katalog);
        }
    }
}
