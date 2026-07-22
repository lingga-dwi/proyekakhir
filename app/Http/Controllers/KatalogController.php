<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Katalog;
use App\Services\CatalogImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class KatalogController extends Controller
{
    public function __construct(private readonly CatalogImageService $images) {}

    public function index(Request $request)
    {
        $query = Katalog::with('category')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim();
                $query->where(fn ($nested) => $nested
                    ->where('nama_desain', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%"));
            })
            ->when($request->filled('category'), fn ($query) => $query->where('category_id', $request->category))
            ->when($request->status === 'published', fn ($query) => $query->published())
            ->when($request->status === 'draft', fn ($query) => $query->where('status', 'draft'))
            ->when($request->status === 'archived', fn ($query) => $query->archived())
            ->when($request->status === 'incomplete', fn ($query) => $query->incomplete())
            ->when(
                ! in_array($request->status, ['published', 'draft', 'archived', 'incomplete'], true),
                fn ($query) => $query->where('status', '!=', 'archived')
            );

        match ($request->get('sort', 'latest')) {
            'oldest' => $query->oldest('updated_at'),
            'name_asc' => $query->orderBy('nama_desain'),
            'name_desc' => $query->orderByDesc('nama_desain'),
            default => $query->latest('updated_at'),
        };

        $katalogs = $query
            ->paginate(15)
            ->withQueryString();

        $categories = Category::orderBy('name')->get(['id', 'name']);
        $stats = [
            'all' => Katalog::where('status', '!=', 'archived')->count(),
            'published' => Katalog::published()->count(),
            'draft' => Katalog::where('status', 'draft')->count(),
            'archived' => Katalog::archived()->count(),
            'incomplete' => Katalog::incomplete()->count(),
        ];

        return view('admin.katalog.index', compact('katalogs', 'categories', 'stats'));
    }

    public function create()
    {
        $categoryGroups = Category::with('children')->parents()->active()->orderBy('sort_order')->get();

        return view('admin.katalog.create', compact('categoryGroups'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        if ($request->status === 'published' && (! $request->filled('category_id') || ! $request->hasFile('gambar_utama'))) {
            throw ValidationException::withMessages([
                'status' => 'Katalog harus memiliki kategori dan gambar utama sebelum dipublikasikan.',
            ]);
        }

        $data['kategori'] = $this->legacyCategoryName($data['category_id'] ?? null);
        // Harga ditentukan per proyek, bukan pada item portofolio katalog.
        $data['harga_estimasi'] = 0;

        // Handle main image upload
        if ($request->hasFile('gambar_utama')) {
            $data['gambar_utama'] = $this->images->store($request->file('gambar_utama'), 'katalog/main');
        }

        // Handle gallery images upload
        $galeriGambar = [];
        if ($request->hasFile('galeri_gambar')) {
            foreach ($request->file('galeri_gambar') as $file) {
                $galeriGambar[] = $this->images->store($file, 'katalog/gallery');
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
        $categoryGroups = Category::with('children')->parents()->active()->orderBy('sort_order')->get();

        return view('admin.katalog.edit', compact('katalog', 'categoryGroups'));
    }

    public function update(Request $request, Katalog $katalog)
    {
        $data = $this->validatedData($request, true);

        if ($request->status === 'published'
            && (! $request->filled('category_id') || (! $request->hasFile('gambar_utama') && ! $katalog->gambar_utama))) {
            throw ValidationException::withMessages([
                'status' => 'Katalog harus memiliki kategori dan gambar utama sebelum dipublikasikan.',
            ]);
        }

        $data['kategori'] = $this->legacyCategoryName($data['category_id'] ?? null);

        // Handle main image upload
        if ($request->hasFile('gambar_utama')) {
            // Delete old image
            if ($katalog->gambar_utama) {
                Storage::disk('public')->delete($katalog->gambar_utama);
            }
            $data['gambar_utama'] = $this->images->store($request->file('gambar_utama'), 'katalog/main');
        }

        $existingGallery = array_values($katalog->galeri_gambar ?? []);
        $requestedRemovals = array_values(array_intersect(
            $existingGallery,
            $data['remove_gallery'] ?? []
        ));
        $newGalleryFiles = $request->file('galeri_gambar', []);

        if ((count($existingGallery) - count($requestedRemovals) + count($newGalleryFiles)) > 12) {
            throw ValidationException::withMessages([
                'galeri_gambar' => 'Galeri maksimal berisi 12 gambar.',
            ]);
        }

        foreach ($requestedRemovals as $path) {
            Storage::disk('public')->delete($path);
        }

        $gallery = array_values(array_diff($existingGallery, $requestedRemovals));
        foreach ($newGalleryFiles as $file) {
            $gallery[] = $this->images->store($file, 'katalog/gallery');
        }
        $data['galeri_gambar'] = $gallery;
        unset($data['remove_gallery']);

        $katalog->update($data);

        return redirect()->route('admin.katalog.index')
            ->with('success', 'Katalog berhasil diperbarui.');
    }

    public function destroy(Katalog $katalog)
    {
        $katalog->update(['status' => 'archived']);

        return redirect()->route('admin.katalog.index')
            ->with('success', 'Katalog berhasil diarsipkan tanpa menghapus riwayatnya.');
    }

    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:katalog,id'],
            'action' => ['required', Rule::in(['publish', 'draft', 'archive', 'change_category'])],
            'category_id' => [
                Rule::requiredIf($request->action === 'change_category'),
                'nullable',
                'exists:categories,id',
            ],
        ]);

        $catalogs = Katalog::whereIn('id', $validated['ids'])->get();

        if ($validated['action'] === 'publish') {
            $incomplete = $catalogs->reject->isCompleteForPublication();

            if ($incomplete->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'ids' => 'Lengkapi kategori, deskripsi, dan gambar utama pada: '.$incomplete->pluck('nama_desain')->join(', ').'.',
                ]);
            }
        }

        DB::transaction(function () use ($validated, $catalogs) {
            switch ($validated['action']) {
                case 'publish':
                    Katalog::whereIn('id', $validated['ids'])->update(['status' => 'published']);
                    break;
                case 'draft':
                    Katalog::whereIn('id', $validated['ids'])->update(['status' => 'draft']);
                    break;
                case 'change_category':
                    $this->changeCategory($catalogs, (int) $validated['category_id']);
                    break;
                case 'archive':
                    Katalog::whereIn('id', $validated['ids'])->update(['status' => 'archived']);
                    break;
            }
        });

        $messages = [
            'publish' => 'Katalog terpilih berhasil dipublikasikan.',
            'draft' => 'Katalog terpilih berhasil dijadikan draft.',
            'change_category' => 'Kategori katalog terpilih berhasil diperbarui.',
            'archive' => 'Katalog terpilih berhasil diarsipkan.',
        ];

        return back()->with('success', $messages[$validated['action']]);
    }

    private function changeCategory($catalogs, int $categoryId): void
    {
        $category = Category::findOrFail($categoryId);

        foreach ($catalogs as $catalog) {
            $catalog->update([
                'category_id' => $category->id,
                'kategori' => $category->name,
            ]);
        }
    }

    private function validatedData(Request $request, bool $editing = false): array
    {
        return $request->validate([
            'nama_desain' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'deskripsi' => ['required', 'string', 'max:5000'],
            'room_size' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'style_tags' => ['nullable', 'string', 'max:500'],
            'inspiration_story' => ['nullable', 'string', 'max:5000'],
            'gambar_utama' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'galeri_gambar' => ['nullable', 'array', 'max:12'],
            'galeri_gambar.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'remove_gallery' => [$editing ? 'nullable' : 'prohibited', 'array'],
            'remove_gallery.*' => ['string'],
            'status' => [
                'required',
                Rule::in($editing ? ['draft', 'published', 'archived'] : ['draft', 'published']),
            ],
        ]);
    }

    private function legacyCategoryName(?int $categoryId): string
    {
        return $categoryId
            ? Category::findOrFail($categoryId)->name
            : 'Belum dikategorikan';
    }
}
