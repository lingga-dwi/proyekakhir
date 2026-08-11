@php($faq = $faq ?? null)

<div class="grid gap-6">
    <div>
        <label for="question" class="text-sm font-semibold text-slate-700">Pertanyaan</label>
        <input id="question" name="question" type="text" required maxlength="255" value="{{ old('question', $faq?->question) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-900 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200" placeholder="Contoh: Bagaimana proses konsultasi dengan Daiku?">
    </div>

    <div>
        <label for="answer" class="text-sm font-semibold text-slate-700">Jawaban</label>
        <textarea id="answer" name="answer" rows="7" required maxlength="5000" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 leading-relaxed text-slate-900 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200" placeholder="Tulis jawaban yang akan tampil di Beranda.">{{ old('answer', $faq?->answer) }}</textarea>
    </div>

    <div class="grid gap-5 sm:grid-cols-[180px_1fr] sm:items-end">
        <div>
            <label for="sort_order" class="text-sm font-semibold text-slate-700">Urutan tampil</label>
            <input id="sort_order" name="sort_order" type="number" min="0" max="9999" required value="{{ old('sort_order', $faq?->sort_order ?? $nextSortOrder ?? 0) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-900 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200">
        </div>
        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $faq?->is_active ?? true)) class="h-4 w-4 rounded border-slate-300 text-amber-500 focus:ring-amber-400">
            Tampilkan di Beranda
        </label>
    </div>
</div>
