<?php

namespace App\Http\Controllers;

use App\Models\Katalog;
use App\Models\Pemesanan;
use App\Models\User;
use App\Services\AdminWorkItemService;
use App\Services\CustomerUploadService;
use App\Services\ManualOrderService;
use App\Services\ProjectWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;

class PemesananController extends Controller
{
    public function __construct(
        private readonly ProjectWorkflowService $workflow,
        private readonly CustomerUploadService $uploads,
        private readonly AdminWorkItemService $workItems,
        private readonly ManualOrderService $manualOrders
    ) {}

    public function create(Request $request)
    {
        $katalog_id = $request->get('katalog_id');
        $katalog = null;

        if ($katalog_id) {
            $katalog = Katalog::with('category')->published()->findOrFail($katalog_id);
        }

        return view('pemesanan.create', compact('katalog'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'alamat' => 'required|string',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore(auth()->id())],
            'jenis_proyek' => 'required|string',
            'jenis_bangunan' => 'required|string',
            'luas_area' => 'required|numeric|min:1',
            'jumlah_ruangan' => 'required|integer|min:1',
            'gaya_desain_preferensi' => 'required|string',
            'warna_dominan' => 'required|string',
            'deskripsi_keinginan_desain' => 'required|string',
            'upload_denah_foto.*' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
            'katalog_id' => 'nullable|exists:katalog,id',
            'terms' => 'accepted',
        ]);

        $katalog = null;
        if ($request->filled('katalog_id')) {
            $katalog = Katalog::published()->findOrFail($request->katalog_id);
        }

        $user = $request->user();
        $missingProfileData = [];

        if (! $user->no_telp) {
            $missingProfileData['no_telp'] = trim((string) $request->no_hp);
        }

        if (! $user->alamat) {
            $missingProfileData['alamat'] = trim((string) $request->alamat);
        }

        if ($missingProfileData !== []) {
            $user->update($missingProfileData);
        }

        // Handle file uploads
        $uploadedFiles = $this->uploads->storeMany(
            $request->file('upload_denah_foto', []),
            "customer-uploads/orders/{$user->id}"
        );

        // Create Pemesanan
        $pemesanan = Pemesanan::create([
            'id_user' => auth()->id(),
            'katalog_id' => $katalog?->id,
            'tanggal_pesan' => now()->toDateString(),
            'sumber_masuk' => 'website',
            'status_pemesanan' => Pemesanan::STATUS_PENDING,
            // Nilai proyek ditentukan setelah kebutuhan pelanggan ditinjau.
            'total_harga' => 0,
            'jenis_proyek' => $request->jenis_proyek,
            'jenis_bangunan' => $request->jenis_bangunan,
            'luas_area' => $request->luas_area,
            'jumlah_ruangan' => $request->jumlah_ruangan,
            'gaya_desain_preferensi' => $request->gaya_desain_preferensi,
            'warna_dominan' => $request->warna_dominan,
            'deskripsi_keinginan_desain' => $request->deskripsi_keinginan_desain,
            'upload_denah_foto' => $uploadedFiles,
        ]);

        $pemesanan->statusTrackings()->create([
            'actor_id' => $user->id,
            'previous_status' => null,
            'status' => Pemesanan::STATUS_PENDING,
            'progress' => 0,
            'tanggal_update' => now()->toDateString(),
            'catatan' => 'Pesanan baru diajukan oleh pelanggan.',
        ]);

        return redirect()->route('pemesanan.show', $pemesanan->id)
            ->with('success', 'Pesanan berhasil dibuat! Tim kami akan segera menghubungi Anda.');
    }

    public function show($id)
    {
        $pemesanan = Pemesanan::with(['user', 'katalog'])->findOrFail($id);

        $user = auth()->user();
        $canAccess = $user->isAdmin()
            || $pemesanan->id_user === $user->id
            || ($user->isDesigner() && $pemesanan->designer_id === $user->id);

        abort_unless($canAccess, 403);

        return view('pemesanan.show', compact('pemesanan'));
    }

    public function attachment(Pemesanan $pemesanan, int $index)
    {
        $user = auth()->user();
        $canAccess = $user->isAdmin()
            || $pemesanan->id_user === $user->id
            || ($user->isDesigner() && $pemesanan->designer_id === $user->id);

        abort_unless($canAccess, 403);

        return $this->uploads->response($pemesanan->upload_denah_foto ?? [], $index);
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
            $resetStatus = Password::sendResetLink(['email' => $data['email']]);
            $message .= $resetStatus === Password::RESET_LINK_SENT
                ? ' Tautan aktivasi akun telah dikirim ke email pelanggan.'
                : ' Akun pelanggan dibuat, tetapi tautan aktivasi belum terkirim. Periksa konfigurasi email.';
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

        $changed = $this->workflow->update($pemesanan, $data, $request->user());

        return back()->with('success', $changed ? 'Progres proyek berhasil diperbarui.' : 'Tidak ada perubahan proyek.');
    }
}
