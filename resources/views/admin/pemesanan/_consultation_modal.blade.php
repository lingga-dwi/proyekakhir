<div
    x-data="{
        open: false,
        reference: '',
        customer: '',
        email: '',
        phone: '',
        address: '',
        requirement: '',
        statusLabel: '',
        statusClass: '',
        status: '',
        convertAction: '',
        deleteAction: '',
        submitting: false,
        show(detail) {
            this.reference = detail.reference;
            this.customer = detail.customer;
            this.email = detail.email;
            this.phone = detail.phone;
            this.address = detail.address;
            this.requirement = detail.requirement;
            this.statusLabel = detail.statusLabel;
            this.statusClass = detail.statusClass;
            this.status = detail.status;
            this.convertAction = detail.convertAction;
            this.deleteAction = detail.deleteAction;
            this.submitting = false;
            this.open = true;
        },
        close() {
            if (this.submitting) return;
            this.open = false;
        }
    }"
    x-cloak
    x-show="open"
    @open-consultation-modal.window="show($event.detail)"
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
        aria-labelledby="consultation-modal-title"
        @click.stop
    >
        <div class="flex items-start justify-between gap-4 px-6 pb-5 pt-6 sm:px-7 sm:pt-7">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-700" x-text="reference"></p>
                <h2 id="consultation-modal-title" class="mt-2 text-xl font-bold text-slate-950">Kelola Pesanan</h2>
            </div>
            <span class="w-fit shrink-0 rounded-full px-3 py-1.5 text-xs font-semibold" :class="statusClass" x-text="statusLabel"></span>
        </div>

        <div class="px-6 pb-6 sm:px-7">
            <dl class="grid gap-3 rounded-xl bg-slate-50 p-4 text-sm sm:grid-cols-2">
                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Pelanggan</dt><dd class="mt-1 font-semibold text-slate-800" x-text="customer"></dd></div>
                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Email</dt><dd class="mt-1 font-semibold text-slate-800" x-text="email || 'Belum diisi'"></dd></div>
                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-400">No. WhatsApp</dt><dd class="mt-1 font-semibold text-slate-800" x-text="phone || 'Belum diisi'"></dd></div>
                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Alamat</dt><dd class="mt-1 font-semibold text-slate-800" x-text="address || 'Belum diisi'"></dd></div>
                <div class="sm:col-span-2"><dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Kebutuhan</dt><dd class="mt-1 font-semibold text-slate-800" x-text="requirement"></dd></div>
            </dl>

            <p class="mt-4 text-sm leading-6 text-slate-600" x-show="status === 'completed'">
                Konsultasi ini telah selesai. Lanjutkan menjadi pesanan proyek agar dapat dikelola, atau hapus jika tidak dilanjutkan.
            </p>
            <p class="mt-4 text-sm leading-6 text-slate-600" x-show="status === 'cancelled'">
                Permintaan ini telah ditolak dan tidak dapat dilanjutkan. Anda dapat menghapusnya dari daftar bila sudah tidak diperlukan.
            </p>
        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-7">
            <form method="POST" :action="deleteAction" @submit.prevent="$dispatch('open-confirmation', {
                form: $el,
                title: 'Hapus permintaan konsultasi?',
                message: 'Data permintaan ini akan dihapus permanen dan tidak dapat dikembalikan.',
                confirmLabel: 'Ya, hapus',
                tone: 'danger'
            })">
                @csrf @method('DELETE')
                <button type="submit" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-red-200 bg-white px-5 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                    <i class="fas fa-trash-can" aria-hidden="true"></i> Hapus
                </button>
            </form>

            <div class="flex flex-col-reverse gap-3 sm:flex-row">
                <button type="button" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 text-sm font-semibold text-slate-700 hover:bg-slate-100" @click="close()" :disabled="submitting">Tutup</button>
                <form method="POST" :action="convertAction" x-show="status === 'completed'" @submit="submitting = true">
                    @csrf
                    <button type="submit" class="inline-flex h-11 min-w-44 items-center justify-center gap-2 rounded-xl bg-slate-950 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">Lanjutkan ke Pesanan</button>
                </form>
            </div>
        </div>
    </section>
</div>
