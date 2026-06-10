<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Katalog;
use App\Models\Category;
use App\Models\Pemesanan;
use App\Models\Rfq;
use Illuminate\Support\Facades\Storage;

class KatalogController extends Controller
{
    public function index()
    {
        $katalogs = Katalog::with('category')->latest()->paginate(12);
        $stats = [
            'total_designs' => Katalog::count(),
            'total_style' => Category::count(),
            'view_results' => Rfq::count(),
            'sold_generated' => Pemesanan::count(),
        ];

        return view('admin.katalog.index', compact('katalogs', 'stats'));
    }

    public function create()
    {
        return view('admin.katalog.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_desain' => 'required|string|max:255',
            'kategori' => 'nullable|string|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'deskripsi' => 'required|string',
            'harga_estimasi' => 'required|numeric|min:0',
            'gambar_utama' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'galeri_gambar.*' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        $data = $request->all();

        if (empty($data['kategori']) && !empty($data['category_id'])) {
            $category = Category::find($data['category_id']);
            if ($category) {
                $data['kategori'] = $category->name;
            }
        }

        // Handle main image upload
        if ($request->hasFile('gambar_utama')) {
            $data['gambar_utama'] = $request->file('gambar_utama')->store('katalog/main', 'public');
        }

        // Handle gallery images upload
        $galeriGambar = [];
        if ($request->hasFile('galeri_gambar')) {
            foreach ($request->file('galeri_gambar') as $file) {
                $galeriGambar[] = $file->store('katalog/gallery', 'public');
            }
        }
        $data['galeri_gambar'] = $galeriGambar;

        Katalog::create($data);

        return redirect()->route('admin.katalog.index')
                        ->with('success', 'Katalog berhasil ditambahkan.');
    }

    public function show(Katalog $katalog)
    {
        return view('admin.katalog.show', compact('katalog'));
    }

    public function edit(Katalog $katalog)
    {
        return view('admin.katalog.edit', compact('katalog'));
    }

    public function update(Request $request, Katalog $katalog)
    {
        $request->validate([
            'nama_desain' => 'required|string|max:255',
            'kategori' => 'nullable|string|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'deskripsi' => 'required|string',
            'harga_estimasi' => 'required|numeric|min:0',
            'gambar_utama' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'galeri_gambar.*' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        $data = $request->all();

        if (empty($data['kategori']) && !empty($data['category_id'])) {
            $category = Category::find($data['category_id']);
            if ($category) {
                $data['kategori'] = $category->name;
            }
        }

        // Handle main image upload
        if ($request->hasFile('gambar_utama')) {
            // Delete old image
            if ($katalog->gambar_utama) {
                Storage::disk('public')->delete($katalog->gambar_utama);
            }
            $data['gambar_utama'] = $request->file('gambar_utama')->store('katalog/main', 'public');
        }

        // Handle gallery images upload
        if ($request->hasFile('galeri_gambar')) {
            // Delete old gallery images
            if ($katalog->galeri_gambar) {
                foreach ($katalog->galeri_gambar as $oldImage) {
                    Storage::disk('public')->delete($oldImage);
                }
            }
            
            $galeriGambar = [];
            foreach ($request->file('galeri_gambar') as $file) {
                $galeriGambar[] = $file->store('katalog/gallery', 'public');
            }
            $data['galeri_gambar'] = $galeriGambar;
        }

        $katalog->update($data);

        return redirect()->route('admin.katalog.index')
                        ->with('success', 'Katalog berhasil diperbarui.');
    }

    public function destroy(Katalog $katalog)
    {
        // Delete associated images
        if ($katalog->gambar_utama) {
            Storage::disk('public')->delete($katalog->gambar_utama);
        }
        
        if ($katalog->galeri_gambar) {
            foreach ($katalog->galeri_gambar as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $katalog->delete();

        return redirect()->route('admin.katalog.index')
                        ->with('success', 'Katalog berhasil dihapus.');
    }
}
