<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Katalog;
use App\Models\Category;
use Illuminate\Support\Facades\File;

class KatalogWithImagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing katalog data safely
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Katalog::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Get categories
        $categories = [
            'rumah' => [
                'ruang-keluarga' => 'Ruang Keluarga',
                'ruang-tamu' => 'Ruang Tamu', 
                'kamar-tidur-utama' => 'Kamar Tidur Utama',
                'kamar-tidur-anak' => 'Kamar Tidur Anak',
                'dapur' => 'Dapur',
                'ruang-makan' => 'Ruang Makan',
                'kamar-mandi' => 'Kamar Mandi',
                'ruang-kerja' => 'Ruang Kerja'
            ],
            'kantor' => [
                'kantor-modern' => 'Kantor Modern'
            ],
            'usaha' => [
                'retail-toko' => 'Retail & Toko',
                'restoran-kafe' => 'Restoran & Kafe'
            ]
        ];

        // Get category IDs from database
        $categoryMap = [];
        foreach ($categories as $folder => $cats) {
            foreach ($cats as $slug => $name) {
                $category = Category::where('slug', $slug)->first();
                if ($category) {
                    $categoryMap[$folder][] = $category->id;
                }
            }
        }

        // Process each folder
        foreach (['rumah', 'kantor', 'usaha'] as $folder) {
            $imagePath = public_path("images/katalog/{$folder}");
            
            if (!File::exists($imagePath)) {
                continue;
            }

            $images = File::files($imagePath);
            $imageCount = 0;
            
            foreach ($images as $image) {
                if ($imageCount >= 50) break; // Limit to 50 images per folder
                
                $filename = $image->getFilename();
                $extension = $image->getExtension();
                
                // Skip if not image
                if (!in_array(strtolower($extension), ['jpg', 'jpeg', 'png'])) {
                    continue;
                }

                // Generate catalog data based on folder
                $catalogData = $this->generateCatalogData($folder, $filename, $categoryMap);
                
                if ($catalogData) {
                    Katalog::create([
                        'category_id' => $catalogData['category_id'],
                        'nama_desain' => $catalogData['nama_desain'],
                        'kategori' => $catalogData['kategori'], // Keep for backward compatibility
                        'deskripsi' => $catalogData['deskripsi'],
                        'harga_estimasi' => $catalogData['harga_estimasi'],
                        'gambar_utama' => "images/katalog/{$folder}/{$filename}",
                        'galeri_gambar' => $catalogData['galeri_gambar'],
                        'product_spots' => $catalogData['product_spots'],
                        'style_tags' => $catalogData['style_tags'],
                        'room_size' => $catalogData['room_size'],
                        'inspiration_story' => $catalogData['inspiration_story'],
                    ]);
                    
                    $imageCount++;
                }
            }
        }
    }

    private function generateCatalogData($folder, $filename, $categoryMap)
    {
        $baseNames = [
            'rumah' => [
                'Modern Minimalis Living Room',
                'Scandinavian Bedroom Design',
                'Industrial Kitchen Concept',
                'Bohemian Master Bedroom',
                'Contemporary Dining Room',
                'Luxury Bathroom Design',
                'Cozy Family Room',
                'Elegant Guest Room',
                'Functional Home Office',
                'Stylish Kids Bedroom',
                'Open Kitchen Layout',
                'Spa-like Bathroom',
                'Warm Living Space',
                'Chic Powder Room',
                'Rustic Dining Area'
            ],
            'kantor' => [
                'Modern Office Space',
                'Executive Office Design',
                'Open Plan Workspace',
                'Creative Studio Layout',
                'Professional Meeting Room',
                'Contemporary Office Interior',
                'Minimalist Workspace',
                'Corporate Office Design',
                'Flexible Work Environment',
                'Innovative Office Concept'
            ],
            'usaha' => [
                'Modern Cafe Interior',
                'Retail Store Design',
                'Restaurant Layout',
                'Boutique Shop Concept',
                'Coffee Shop Design',
                'Commercial Space Interior',
                'Trendy Bar Design',
                'Elegant Restaurant',
                'Contemporary Retail',
                'Stylish Cafe Concept'
            ]
        ];

        $descriptions = [
            'rumah' => [
                'Desain interior rumah yang menggabungkan kenyamanan dan estetika modern untuk menciptakan ruang hidup yang sempurna.',
                'Konsep desain yang mengutamakan fungsi dan keindahan dengan pemilihan warna dan material yang harmonis.',
                'Ruang yang dirancang khusus untuk memberikan kenyamanan maksimal bagi keluarga dengan sentuhan desain kontemporer.',
                'Desain interior yang mencerminkan gaya hidup modern dengan perpaduan elemen klasik dan contemporary.',
                'Konsep ruang yang mengoptimalkan pencahayaan alami dan sirkulasi udara untuk kenyamanan penghuni.'
            ],
            'kantor' => [
                'Desain kantor modern yang mendukung produktivitas dengan lingkungan kerja yang nyaman dan inspiratif.',
                'Konsep workspace yang menggabungkan efisiensi dan estetika untuk menciptakan suasana kerja yang optimal.',
                'Ruang kerja yang dirancang untuk meningkatkan kolaborasi tim dengan desain yang fungsional dan menarik.',
                'Desain office interior yang mencerminkan profesionalisme dengan sentuhan modern dan teknologi terkini.'
            ],
            'usaha' => [
                'Desain interior komersial yang menarik pelanggan dengan konsep yang unik dan memorable.',
                'Konsep ruang usaha yang mengoptimalkan customer experience dengan desain yang fungsional dan estetis.',
                'Interior bisnis yang dirancang untuk meningkatkan brand image dan customer engagement.',
                'Desain komersial yang menggabungkan aspek bisnis dengan estetika untuk menciptakan ruang yang profitable.'
            ]
        ];

        $styleTags = [
            'rumah' => ['Modern', 'Minimalis', 'Scandinavian', 'Industrial', 'Bohemian', 'Contemporary', 'Rustic', 'Luxury'],
            'kantor' => ['Modern', 'Professional', 'Minimalis', 'Corporate', 'Industrial', 'Contemporary'],
            'usaha' => ['Commercial', 'Modern', 'Trendy', 'Contemporary', 'Industrial', 'Chic']
        ];

        // Get random category for this folder
        if (!isset($categoryMap[$folder]) || empty($categoryMap[$folder])) {
            return null;
        }

        $categoryId = $categoryMap[$folder][array_rand($categoryMap[$folder])];
        $category = Category::find($categoryId);

        return [
            'category_id' => $categoryId,
            'nama_desain' => $baseNames[$folder][array_rand($baseNames[$folder])] . ' #' . rand(1, 999),
            'kategori' => $category ? $category->name : ucfirst($folder),
            'deskripsi' => $descriptions[$folder][array_rand($descriptions[$folder])],
            'harga_estimasi' => $this->generatePrice($folder),
            'galeri_gambar' => null, // Will be populated later if needed
            'product_spots' => $this->generateProductSpots(),
            'style_tags' => implode(', ', array_slice($styleTags[$folder], 0, rand(2, 4))),
            'room_size' => rand(20, 150),
            'inspiration_story' => $this->generateInspirationStory($folder)
        ];
    }

    private function generatePrice($folder)
    {
        $priceRanges = [
            'rumah' => [15000000, 75000000], // 15jt - 75jt
            'kantor' => [25000000, 100000000], // 25jt - 100jt  
            'usaha' => [30000000, 150000000] // 30jt - 150jt
        ];

        $range = $priceRanges[$folder];
        return rand($range[0], $range[1]);
    }

    private function generateProductSpots()
    {
        $spots = [];
        $numSpots = rand(3, 8);
        
        $products = [
            'Sofa Modern 3 Seater',
            'Coffee Table Minimalis', 
            'Floor Lamp Industrial',
            'Wall Art Contemporary',
            'Rug Scandinavian',
            'Side Table Wooden',
            'Pendant Light Modern',
            'Bookshelf Industrial',
            'Accent Chair',
            'Wall Mirror Decorative'
        ];

        for ($i = 0; $i < $numSpots; $i++) {
            $spots[] = [
                'x' => rand(10, 80),
                'y' => rand(10, 80),
                'product' => $products[array_rand($products)],
                'price' => 'Rp ' . number_format(rand(500000, 15000000), 0, ',', '.')
            ];
        }

        return $spots;
    }

    private function generateInspirationStory($folder)
    {
        $stories = [
            'rumah' => [
                'Terinspirasi dari gaya hidup modern yang mengutamakan kenyamanan dan kemudahan, desain ini menggabungkan elemen-elemen kontemporer dengan sentuhan hangat untuk menciptakan rumah yang truly livable.',
                'Konsep ini lahir dari keinginan untuk menciptakan ruang yang tidak hanya indah dipandang, tetapi juga fungsional untuk kehidupan sehari-hari keluarga modern.',
                'Desain ini mengambil inspirasi dari filosofi less is more, dimana setiap elemen dipilih dengan cermat untuk menciptakan harmoni dan keseimbangan dalam ruang.'
            ],
            'kantor' => [
                'Terinspirasi dari workspace terbaik di dunia, desain ini menggabungkan produktivitas dengan kenyamanan untuk menciptakan lingkungan kerja yang optimal.',
                'Konsep ini dirancang untuk mendukung berbagai gaya kerja modern, dari focused work hingga collaborative sessions, dalam satu ruang yang cohesive.'
            ],
            'usaha' => [
                'Terinspirasi dari tren retail global, desain ini menciptakan customer experience yang memorable melalui perpaduan estetika dan fungsionalitas.',
                'Konsep ini dirancang untuk meningkatkan brand presence dan customer engagement melalui desain interior yang strategic dan appealing.'
            ]
        ];

        return $stories[$folder][array_rand($stories[$folder])];
    }
}