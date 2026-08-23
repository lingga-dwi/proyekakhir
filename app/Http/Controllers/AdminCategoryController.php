<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminCategoryController extends Controller
{
    public function index()
    {
        $categories = Category::query()
            ->parents()
            ->withCount('katalogs')
            ->with(['allChildren' => fn ($query) => $query
                ->withCount('katalogs')
                ->orderBy('sort_order')
                ->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $stats = [
            'total' => Category::count(),
            'active' => Category::active()->count(),
            'parents' => Category::parents()->count(),
            'used' => Category::has('katalogs')->count(),
        ];

        return view('admin.categories.index', compact('categories', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['is_active'] = $request->boolean('is_active');

        Category::create($data);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validatedData($request, $category);

        if ($category->allChildren()->exists() && ! empty($data['parent_id'])) {
            throw ValidationException::withMessages([
                'parent_id' => 'Kategori yang memiliki subkategori tidak dapat dijadikan subkategori.',
            ]);
        }

        if ($category->name !== $data['name']) {
            $data['slug'] = $this->uniqueSlug($data['name'], $category);
        }

        $data['is_active'] = $request->boolean('is_active');
        $category->update($data);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function toggle(Category $category)
    {
        $category->update(['is_active' => ! $category->is_active]);

        $message = $category->is_active
            ? 'Kategori berhasil ditampilkan kembali.'
            : 'Kategori berhasil disembunyikan dari pilihan katalog baru.';

        return back()->with('success', $message);
    }

    public function destroy(Category $category)
    {
        if ($category->katalogs()->exists()) {
            throw ValidationException::withMessages([
                'category' => 'Kategori masih digunakan katalog. Nonaktifkan kategori atau pindahkan katalog terlebih dahulu.',
            ]);
        }

        if ($category->allChildren()->exists()) {
            throw ValidationException::withMessages([
                'category' => 'Kategori masih memiliki subkategori. Hapus atau pindahkan subkategori terlebih dahulu.',
            ]);
        }

        $category->delete();

        return back()->with('success', 'Kategori yang tidak digunakan berhasil dihapus.');
    }

    private function validatedData(Request $request, ?Category $category = null): array
    {
        $parentRule = Rule::exists('categories', 'id')->whereNull('parent_id');

        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'parent_id' => [
                'nullable',
                'integer',
                $parentRule,
                Rule::notIn(array_filter([$category?->id])),
            ],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'parent_id.exists' => 'Kategori induk tidak valid atau bukan kategori tingkat utama.',
            'parent_id.not_in' => 'Kategori tidak dapat menjadi induk bagi dirinya sendiri.',
        ]);
    }

    private function uniqueSlug(string $name, ?Category $except = null): string
    {
        $base = Str::slug($name) ?: 'kategori';
        $slug = $base;
        $counter = 2;

        while (Category::where('slug', $slug)
            ->when($except, fn ($query) => $query->whereKeyNot($except->getKey()))
            ->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
