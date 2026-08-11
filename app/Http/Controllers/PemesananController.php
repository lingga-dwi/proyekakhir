<?php

namespace App\Http\Controllers;

use App\Models\Katalog;
use App\Models\Pemesanan;
use App\Models\ProjectDocument;
use App\Models\ProjectInvoice;
use App\Models\User;
use App\Services\AccountActivationService;
use App\Services\AdminWorkItemService;
use App\Services\ManualOrderService;
use App\Services\PaymentEvidenceService;
use App\Services\ProjectWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PemesananController extends Controller
{
    public function __construct(
        private readonly ProjectWorkflowService $workflow,
        private readonly AdminWorkItemService $workItems,
        private readonly ManualOrderService $manualOrders,
        private readonly AccountActivationService $activation,
        private readonly PaymentEvidenceService $paymentEvidence
    ) {}

    public function create(Request $request)
    {
        return redirect()->route('konsultasi.create')
            ->with('success', 'Mulai dari formulir konsultasi agar kebutuhan, jadwal, desain, dan RAB dapat ditangani dalam satu alur.');
    }

    public function store(Request $request)
    {
        return redirect()->route('konsultasi.create')
            ->with('error', 'Pesanan langsung tidak digunakan lagi. Kirim permintaan melalui formulir konsultasi agar proses desain dapat ditelusuri dari awal.');
    }

    public function show(Request $request, $id)
    {
        $pemesanan = Pemesanan::with(['user', 'katalog', 'documents.uploader', 'dpInvoice'])->findOrFail($id);
        $this->authorizeOrderAccess($request, $pemesanan);

        $adminView = $request->routeIs('admin.pemesanan.show');

        return view('pemesanan.show', compact('pemesanan', 'adminView'));
    }

    public function uploadDocument(Request $request, Pemesanan $pemesanan)
    {
        abort_unless($pemesanan->designer_id === $request->user()->id, 403);

        $data = $request->validate([
            'document_type' => ['required', Rule::in(['design', 'rab', 'survey'])],
            'document' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ]);

        $isDraft = in_array($pemesanan->workflow_stage, ['draft_design', 'revision_requested'], true);
        $isFinal = $pemesanan->workflow_stage === 'final_design';
        abort_unless($isDraft || $isFinal, 422, 'Dokumen belum dapat diunggah pada tahap ini.');

        $stage = $isDraft ? 'draft' : 'final';
        $nextVersion = (int) $pemesanan->documents()->where('stage', $stage)->max('version') + 1;
        $file = $data['document'];
        $path = $file->store('project-documents/'.$pemesanan->id.'/'.$stage, 'local');

        $pemesanan->documents()->create([
            'uploaded_by' => $request->user()->id,
            'stage' => $stage,
            'document_type' => $data['document_type'],
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'version' => $nextVersion,
        ]);

        $pemesanan->update([
            'workflow_stage' => $isDraft ? 'awaiting_draft_approval' : 'awaiting_final_approval',
            'progress' => $isDraft ? 20 : 45,
            'catatan_progres' => $isDraft ? 'Desain awal/RAB dikirim untuk ditinjau pelanggan.' : 'Desain dan RAB final dikirim untuk persetujuan pelanggan.',
        ]);

        return back()->with('success', 'Dokumen berhasil dikirim ke pelanggan untuk ditinjau.');
    }

    public function downloadDocument(Request $request, Pemesanan $pemesanan, ProjectDocument $document)
    {
        abort_unless($document->pemesanan_id === $pemesanan->id, 404);
        $this->authorizeOrderAccess($request, $pemesanan);

        return Storage::disk('local')->download($document->path, $document->original_name);
    }

    public function decideDocument(Request $request, Pemesanan $pemesanan)
    {
        abort_unless($pemesanan->id_user === $request->user()->id, 403);

        $data = $request->validate([
            'stage' => ['required', Rule::in(['draft', 'final'])],
            'decision' => ['required', Rule::in(['approved', 'revision_requested'])],
            'feedback' => ['nullable', 'string', 'max:2000'],
        ]);

        $expectedStage = $data['stage'] === 'draft' ? 'awaiting_draft_approval' : 'awaiting_final_approval';
        abort_unless($pemesanan->workflow_stage === $expectedStage, 422, 'Tidak ada dokumen yang menunggu keputusan pada tahap ini.');

        if ($data['decision'] === 'revision_requested') {
            $pemesanan->update([
                'workflow_stage' => $data['stage'] === 'draft' ? 'revision_requested' : 'final_design',
                'catatan_progres' => 'Pelanggan meminta revisi: '.($data['feedback'] ?: 'Tidak ada catatan tambahan.'),
            ]);

            return back()->with('success', 'Permintaan revisi telah dikirim kepada desainer.');
        }

        if ($data['stage'] === 'draft') {
            if ((float) $pemesanan->total_harga <= 0) {
                return back()->withErrors(['decision' => 'Admin perlu menetapkan nilai proyek sebelum invoice DP dapat dibuat.']);
            }

            $invoice = ProjectInvoice::firstOrCreate(
                ['pemesanan_id' => $pemesanan->id, 'type' => 'dp_20'],
                [
                    'number' => 'DP-'.now()->format('Y').'-'.str_pad((string) $pemesanan->id, 5, '0', STR_PAD_LEFT),
                    'amount' => round((float) $pemesanan->total_harga * 0.20, 2),
                    'status' => 'pending',
                    'due_date' => now()->addDays(7)->toDateString(),
                ]
            );

            $pemesanan->update([
                'workflow_stage' => $invoice->status === 'paid' ? 'survey_scheduled' : 'awaiting_dp',
                'progress' => 25,
                'catatan_progres' => 'Desain awal disetujui. Invoice DP 20% '.$invoice->number.' telah dibuat.',
            ]);

            return back()->with('success', 'Desain awal disetujui. Invoice DP 20% telah dibuat.');
        }

        $pemesanan->update([
            'workflow_stage' => 'approved',
            'status_pemesanan' => Pemesanan::STATUS_IN_PROGRESS,
            'progress' => max(50, (int) $pemesanan->progress),
            'catatan_progres' => 'Desain dan RAB final disetujui pelanggan. Pengerjaan dapat dimulai.',
        ]);

        return back()->with('success', 'Desain final disetujui. Proyek masuk ke tahap pengerjaan.');
    }

    public function uploadDpEvidence(Request $request, Pemesanan $pemesanan)
    {
        abort_unless($pemesanan->id_user === $request->user()->id, 403);
        $invoice = $pemesanan->dpInvoice;
        abort_unless($invoice && $pemesanan->workflow_stage === 'awaiting_dp', 422, 'Tidak ada invoice DP yang menunggu pembayaran.');

        $data = $request->validate(['bukti_pembayaran' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120']]);
        $path = $data['bukti_pembayaran']->store('dp-proofs/order-'.$pemesanan->id, 'payment_evidence');

        $invoice->update(['proof_path' => $path, 'status' => 'submitted']);
        $pemesanan->update(['workflow_stage' => 'dp_verification', 'catatan_progres' => 'Bukti pembayaran DP telah diunggah dan menunggu verifikasi admin.']);

        return back()->with('success', 'Bukti pembayaran DP berhasil dikirim.');
    }

    public function verifyDp(Request $request, Pemesanan $pemesanan)
    {
        $invoice = $pemesanan->dpInvoice;
        abort_unless($invoice && $invoice->status === 'submitted', 422, 'Tidak ada pembayaran DP yang menunggu verifikasi.');

        $invoice->update(['status' => 'paid', 'verified_by' => $request->user()->id, 'verified_at' => now()]);
        $pemesanan->update(['workflow_stage' => 'survey_scheduled', 'progress' => 30, 'catatan_progres' => 'DP telah diverifikasi. Jadwalkan survei lokasi.']);

        return back()->with('success', 'DP berhasil diverifikasi. Proyek siap dijadwalkan untuk survei.');
    }

    public function scheduleSurvey(Request $request, Pemesanan $pemesanan)
    {
        abort_unless($pemesanan->workflow_stage === 'survey_scheduled', 422, 'Survei hanya dapat dijadwalkan setelah DP diverifikasi.');
        $data = $request->validate([
            'survey_scheduled_at' => ['required', 'date', 'after_or_equal:now'],
            'survey_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $pemesanan->update([
            ...$data,
            'workflow_stage' => 'final_design',
            'progress' => 35,
            'catatan_progres' => 'Survei lokasi dijadwalkan. Desainer dapat menyiapkan desain dan RAB final.',
        ]);

        return back()->with('success', 'Survei lokasi dijadwalkan dan tahap desain final dimulai.');
    }

    public function uploadPaymentEvidence(Request $request, Pemesanan $pemesanan)
    {
        abort_unless($pemesanan->id_user === $request->user()->id, 403);

        $data = $request->validate([
            'bukti_pembayaran' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        $this->paymentEvidence->upload($pemesanan, $data['bukti_pembayaran'], $request->user());

        return back()->with('success', 'Bukti pembayaran berhasil diunggah dan menunggu verifikasi admin.');
    }

    public function downloadPaymentEvidence(Request $request, Pemesanan $pemesanan)
    {
        $this->authorizeOrderAccess($request, $pemesanan);
        $summary = $this->paymentEvidence->summary($pemesanan);

        abort_unless($summary['proof_path'], 404);

        return Storage::disk('payment_evidence')->download($summary['proof_path']);
    }

    public function verifyPaymentEvidence(Request $request, Pemesanan $pemesanan)
    {
        abort_unless($this->paymentEvidence->verify($pemesanan, $request->user()), 422, 'Tidak ada bukti pembayaran yang menunggu verifikasi.');

        return back()->with('success', 'Pembayaran berhasil diverifikasi.');
    }

    public function index(Request $request)
    {
        $stats = $this->workItems->stats();
        $workItems = $this->workItems->paginate($request);

        $designers = User::where('role', 'designer')->orderBy('nama')->get(['id', 'nama']);
        $customers = User::where('role', 'pelanggan')->orderBy('nama')->get(['id', 'nama', 'email', 'no_telp']);

        return view('admin.pemesanan.index', compact('workItems', 'stats', 'designers', 'customers'));
    }

    public function storeAdmin(Request $request)
    {
        $data = $request->validateWithBag('manualOrder', [
            'customer_mode' => ['required', Rule::in(['existing', 'new'])],
            'id_user' => [
                'nullable',
                'required_if:customer_mode,existing',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'pelanggan')),
            ],
            'nama' => ['nullable', 'required_if:customer_mode,new', 'string', 'max:255'],
            'email' => ['nullable', 'required_if:customer_mode,new', 'email', 'max:255', Rule::unique('users', 'email')],
            'no_telp' => ['nullable', 'required_if:customer_mode,new', 'string', 'max:20'],
            'alamat' => ['nullable', 'string', 'max:1000'],
            'sumber_masuk' => ['required', Rule::in(['kantor', 'whatsapp', 'telepon', 'instagram', 'website'])],
            'tanggal_pesan' => ['required', 'date', 'before_or_equal:today'],
            'jenis_proyek' => ['required', 'string', 'max:255'],
            'jenis_bangunan' => ['nullable', 'string', 'max:255'],
            'deskripsi_keinginan_desain' => ['nullable', 'string', 'max:2000'],
        ]);

        $pemesanan = $this->manualOrders->create($data, $request->user());

        $message = 'Pesanan DI-'.str_pad((string) $pemesanan->id, 3, '0', STR_PAD_LEFT).' berhasil dicatat.';

        if ($data['customer_mode'] === 'new') {
            $customer = $pemesanan->user()->firstOrFail();

            if (! $this->activation->isDeliveryConfigured()) {
                $message .= ' Akun pelanggan dibuat, tetapi email aktivasi belum dikirim karena layanan email belum dikonfigurasi.';
            } else {
                $message .= $this->activation->send($customer)
                    ? ' Tautan aktivasi akun telah dikirim ke email pelanggan.'
                    : ' Akun pelanggan dibuat, tetapi email aktivasi gagal dikirim. Periksa layanan email.';
            }
        }

        return redirect()->route('admin.pemesanan.index')
            ->with('success', $message);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => ['required', Rule::in(Pemesanan::STATUSES)],
        ]);

        $pemesanan = Pemesanan::findOrFail($id);
        $changed = $this->workflow->update(
            $pemesanan,
            ['status_pemesanan' => $request->status],
            $request->user(),
            'Status pesanan diperbarui oleh admin.'
        );

        return back()->with('success', $changed ? 'Status pesanan berhasil diperbarui.' : 'Tidak ada perubahan status.');
    }

    public function updateProject(Request $request, Pemesanan $pemesanan)
    {
        $data = $request->validate([
            'status_pemesanan' => ['required', Rule::in(Pemesanan::STATUSES)],
            'progress' => ['required', 'integer', 'between:0,100'],
            'target_selesai' => ['nullable', 'date'],
            'designer_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'designer')),
            ],
            'total_harga' => ['nullable', 'numeric', 'min:0'],
            'catatan_progres' => ['nullable', 'string', 'max:2000'],
        ]);

        if (in_array($data['status_pemesanan'], [Pemesanan::STATUS_IN_PROGRESS, Pemesanan::STATUS_COMPLETED], true)
            && $pemesanan->workflow_stage !== 'approved') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'status_pemesanan' => 'Pelanggan harus menyetujui desain dan RAB final sebelum proyek dapat dikerjakan atau diselesaikan.',
            ]);
        }

        $changed = $this->workflow->update($pemesanan, $data, $request->user());

        return back()->with('success', $changed ? 'Progres proyek berhasil diperbarui.' : 'Tidak ada perubahan proyek.');
    }

    public function updateAssignedProject(Request $request, Pemesanan $pemesanan)
    {
        abort_unless($pemesanan->designer_id === $request->user()->id, 403);

        $data = $request->validate([
            'status_pemesanan' => [
                'required',
                Rule::in([
                    Pemesanan::STATUS_CONFIRMED,
                    Pemesanan::STATUS_IN_PROGRESS,
                    Pemesanan::STATUS_COMPLETED,
                ]),
            ],
            'progress' => ['required', 'integer', 'between:0,100'],
            'target_selesai' => ['nullable', 'date'],
            'catatan_progres' => ['required', 'string', 'max:2000'],
        ]);

        if (in_array($data['status_pemesanan'], [Pemesanan::STATUS_IN_PROGRESS, Pemesanan::STATUS_COMPLETED], true)
            && $pemesanan->workflow_stage !== 'approved') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'status_pemesanan' => 'Desain dan RAB final harus disetujui pelanggan sebelum pengerjaan dimulai.',
            ]);
        }

        $changed = $this->workflow->update(
            $pemesanan,
            $data,
            $request->user(),
            'Progres proyek diperbarui oleh desainer.'
        );

        return back()->with('success', $changed ? 'Progres proyek berhasil diperbarui.' : 'Tidak ada perubahan proyek.');
    }

    private function authorizeOrderAccess(Request $request, Pemesanan $pemesanan): void
    {
        $user = $request->user();
        $canAccess = $user->isAdmin()
            || $pemesanan->id_user === $user->id
            || ($user->isDesigner() && $pemesanan->designer_id === $user->id);

        abort_unless($canAccess, 403);
    }
}
