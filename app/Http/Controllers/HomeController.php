<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Katalog;
use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;

class HomeController extends Controller
{
    public function index()
    {
        if (Config::get('app.db_offline')) {
            $featuredKatalogs = collect();
        } else {
            try {
                $featuredKatalogs = Katalog::with('category')->latest()->take(3)->get();
            } catch (\Throwable $e) {
                // Saat DB mati, tampilkan tanpa data agar halaman tetap hidup
                $featuredKatalogs = collect();
                logger()->warning('DB unavailable when loading home featured katalogs', ['error' => $e->getMessage()]);
            }
        }
        return view('home.index', compact('featuredKatalogs'));
    }

    public function katalog(Request $request)
    {
        $parentCategories = collect();

        if (!Config::get('app.db_offline')) {
            try {
                $parentCategories = Category::parents()->active()->with('children')->orderBy('sort_order')->get();
            } catch (\Exception $e) {
                // Fallback jika table categories belum ada
                $parentCategories = collect();
            }
        }
        
        $query = Config::get('app.db_offline') ? null : Katalog::with('category');
        
        // Filter by category
        if ($query && $request->has('category') && $request->category != '') {
            $category = Category::where('slug', $request->category)->first();
            if ($category) {
                if ($category->hasChildren()) {
                    // If parent category, include all children
                    $childrenIds = $category->children->pluck('id')->push($category->id);
                    $query->whereIn('category_id', $childrenIds);
                } else {
                    $query->where('category_id', $category->id);
                }
            }
        }
        
        // Search functionality
        if ($query && $request->has('search') && $request->search != '') {
            $query->where('nama_desain', 'like', '%' . $request->search . '%');
        }
        
        // Sorting
        if ($query) {
            switch ($request->get('sort', 'latest')) {
                case 'name':
                    $query->orderBy('nama_desain', 'asc');
                    break;
                case 'category':
                    $query->join('categories', 'katalog.category_id', '=', 'categories.id')
                          ->orderBy('categories.name', 'asc')
                          ->select('katalog.*');
                    break;
                default:
                    $query->latest();
                    break;
            }
        }
        
        if ($query) {
            try {
                $katalogs = $query->paginate(12);
            } catch (\Throwable $e) {
                // Saat DB tidak bisa diakses, kembalikan paginator kosong supaya view tetap jalan
                $katalogs = new LengthAwarePaginator([], 0, 12);
                $selectedCategory = null;
                logger()->warning('DB unavailable when listing katalog', ['error' => $e->getMessage()]);
                return view('home.katalog', compact('katalogs', 'parentCategories', 'selectedCategory'));
            }
        } else {
            $katalogs = new LengthAwarePaginator([], 0, 12);
        }
        $selectedCategory = $request->category;

        return view('home.katalog', compact('katalogs', 'parentCategories', 'selectedCategory'));
    }

    public function katalogFilter(Request $request)
    {
        $query = Katalog::query();

        if ($request->has('kategori') && $request->kategori != '') {
            $query->where('kategori', $request->kategori);
        }

        if ($request->has('search') && $request->search != '') {
            $query->where('nama_desain', 'like', '%' . $request->search . '%');
        }

        $katalogs = $query->paginate(8);
        $categories = Katalog::distinct('kategori')->pluck('kategori');

        return view('home.katalog', compact('katalogs', 'categories'));
    }

    public function katalogDetail($id)
    {
        if (Config::get('app.db_offline')) {
            abort(503, 'Database offline');
        }

        $katalog = Katalog::with('category')->findOrFail($id);
        
        $relatedKatalogs = Katalog::with('category')
                                 ->where('category_id', $katalog->category_id)
                                 ->where('id', '!=', $id)
                                 ->take(3)
                                 ->get();
        
        return view('home.katalog-detail', compact('katalog', 'relatedKatalogs'));
    }

    public function katalogApi($id)
    {
        if (Config::get('app.db_offline')) {
            return response()->json(['message' => 'Database offline'], 503);
        }

        $katalog = Katalog::with('category')->findOrFail($id);
        
        return response()->json([
            'id' => $katalog->id,
            'nama_desain' => $katalog->nama_desain,
            'deskripsi' => $katalog->deskripsi,
            'harga_estimasi' => $katalog->harga_estimasi,
            'gambar_utama' => $katalog->gambar_utama,
            'gambar_utama_url' => $katalog->gambar_utama_url,
            'category' => $katalog->category ? $katalog->category->name : null,
            'style_tags' => $katalog->style_tags,
            'room_size' => $katalog->room_size,
            'inspiration_story' => $katalog->inspiration_story,
            'product_spots' => $katalog->product_spots,
            'formatted_price' => 'Rp ' . number_format($katalog->harga_estimasi, 0, ',', '.')
        ]);
    }
}
