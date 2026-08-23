<div
    x-data="{
        open: false,
        action: '',
        reference: '',
        customer: '',
        requirement: '',
        submitting: false,
        show(detail) {
            this.action = detail.action;
            this.reference = detail.reference;
            this.customer = detail.customer;
            this.requirement = detail.requirement;
            this.submitting = false;
            this.open = true;
            this.$nextTick(() => this.$refs.designer.focus());
        },
        close() {
            if (this.submitting) return;
            this.open = false;
        }
    }"
    x-cloak
    x-show="open"
    @open-accept-consultation.window="show($event.detail)"
    @keydown.escape.window="close()"
    class="fixed inset-0 z-110 flex items-center justify-center p-4 sm:p-6"
    role="presentation"
>
    <div class="absolute inset-0 bg-slate-950/55 backdrop-blur-[2px]" @click="close()"></div>

    <section
        x-show="open"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="translate-y-3 scale-95 opacity-0"
        x-transition:enter-end="translate-y-0 scale-100 opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="translate-y-0 scale-100 opacity-100"
        x-transition:leave-end="translate-y-2 scale-95 opacity-0"
        class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl"
        role="dialog"
        aria-modal="true"
        aria-labelledby="accept-consultation-title"
        @click.stop
    >
        <form method="POST" :action="action" @submit="submitting = true">
            @csrf
            <div class="px-6 pb-6 pt-6 sm:px-7 sm:pt-7">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-700" x-text="reference"></p>
                <h2 id="accept-consultation-title" class="mt-2 text-xl font-bold text-slate-950">Terima dan tugaskan desainer</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Permintaan akan langsung diterima dan desainer terpilih akan diberi tugas konsultasi melalui WhatsApp.
                </p>

                <dl class="mt-5 grid gap-3 rounded-xl bg-slate-50 p-4 text-sm sm:grid-cols-2">
                    <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Pelanggan</dt><dd class="mt-1 font-semibold text-slate-800" x-text="customer"></dd></div>
                    <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Kebutuhan</dt><dd class="mt-1 font-semibold text-slate-800" x-text="requirement"></dd></div>
                </dl>

                <label class="mt-5 block" for="accept-consultation-designer">
                    <span class="mb-2 block text-sm font-semibold text-slate-700">Desainer konsultasi</span>
                    <select
                        x-ref="designer"
                        id="accept-consultation-designer"
                        name="designer_id"
                        required
                        class="w-full rounded-xl border-slate-300 py-2.5 text-sm focus:border-amber-500 focus:ring-amber-500"
                    >
                        <option value="">Pilih desainer</option>
                        @foreach($designers as $designer)
                            <option value="{{ $designer->id }}">{{ $designer->nama }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4 sm:flex-row sm:justify-end sm:px-7">
                <button type="button" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 text-sm font-semibold text-slate-700 hover:bg-slate-100" @click="close()" :disabled="submitting">Batal</button>
                <button class="inline-flex h-11 min-w-44 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-wait disabled:opacity-70" :disabled="submitting">
                    <i x-show="submitting" class="fas fa-circle-notch fa-spin" aria-hidden="true"></i>
                    <span x-text="submitting ? 'Memproses...' : 'Terima & Tugaskan'"></span>
                </button>
            </div>
        </form>
    </section>
</div>
