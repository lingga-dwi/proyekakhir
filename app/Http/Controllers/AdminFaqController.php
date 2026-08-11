<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AdminFaqController extends Controller
{
    public function index(Request $request)
    {
        $query = Faq::query()->orderBy('sort_order')->orderBy('id');
        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(fn ($builder) => $builder->where('question', 'like', "%{$search}%")->orWhere('answer', 'like', "%{$search}%"));
        }
        if ($request->has('status') && in_array($request->status, ['active', 'hidden'], true)) {
            $query->where('is_active', $request->status === 'active');
        }
        $faqs = $query->paginate(15)->withQueryString();
        $stats = [
            'total' => Faq::count(),
            'active' => Faq::active()->count(),
            'hidden' => Faq::where('is_active', false)->count(),
        ];

        return view('admin.faq.index', compact('faqs', 'stats'));
    }

    public function create()
    {
        $nextSortOrder = (int) Faq::max('sort_order') + 1;

        return view('admin.faq.create', compact('nextSortOrder'));
    }

    public function store(Request $request)
    {
        Faq::create($this->validatedData($request));

        return redirect()->route('admin.faq.index')->with('success', 'Pertanyaan umum berhasil ditambahkan.');
    }

    public function edit(Faq $faq)
    {
        return view('admin.faq.edit', compact('faq'));
    }

    public function update(Request $request, Faq $faq)
    {
        $faq->update($this->validatedData($request));

        return redirect()->route('admin.faq.index')->with('success', 'Pertanyaan umum berhasil diperbarui.');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();

        return redirect()->route('admin.faq.index')->with('success', 'Pertanyaan umum berhasil dihapus.');
    }

    public function toggle(Faq $faq)
    {
        $faq->update(['is_active' => ! $faq->is_active]);

        return back()->with('success', $faq->is_active ? 'FAQ ditampilkan di Beranda.' : 'FAQ disembunyikan dari Beranda.');
    }

    public function move(Request $request, Faq $faq)
    {
        $direction = $request->validate(['direction' => ['required', 'in:up,down']])['direction'];
        $neighbor = Faq::query()
            ->when($direction === 'up', fn ($q) => $q->where('sort_order', '<', $faq->sort_order)->orderByDesc('sort_order'))
            ->when($direction === 'down', fn ($q) => $q->where('sort_order', '>', $faq->sort_order)->orderBy('sort_order'))
            ->first();

        if ($neighbor) {
            DB::transaction(function () use ($faq, $neighbor): void {
                $current = $faq->sort_order;
                $faq->update(['sort_order' => $neighbor->sort_order]);
                $neighbor->update(['sort_order' => $current]);
            });
        }

        return back()->with('success', 'Urutan FAQ diperbarui.');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string', 'max:5000'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
