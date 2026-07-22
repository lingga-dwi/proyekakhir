<?php

namespace App\Http\Controllers;

use App\Models\Katalog;
use App\Models\Konsultasi;
use App\Models\Pemesanan;
use App\Services\CustomerUploadService;
use App\Services\ProjectWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PemesananController extends Controller
{
    public function __construct(
        private readonly ProjectWorkflowService $workflow,
        private readonly CustomerUploadService $uploads
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
            'status_pemesanan' => 'pending',
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
            'status' => 'pending',
            'progress' => 0,
            'tanggal_update' => now()->toDateString(),
            'catatan' => 'Pesanan baru diajukan oleh pelanggan.',
        ]);

        return redirect()->route('pemesanan.show', $pemesanan->id)
            ->with('success', 'Pesanan berhasil dibuat! Tim kami akan segera menghubungi Anda.');
    }

    public function show($id)
    {
        $pemesanan = Pemesanan::with(['user', 'katalog', 'rfq.katalog'])->findOrFail($id);

        // Ensure user can only see their own orders (unless admin)
        if (! auth()->user()->isAdmin() && $pemesanan->id_user !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

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

    public function myOrders()
    {
        $pemesanans = auth()->user()->pemesanans()
            ->with(['katalog', 'rfq.katalog'])
            ->latest()
            ->paginate(10);

        return view('pemesanan.my-orders', compact('pemesanans'));
    }

    public function index(Request $request)
    {
        $stats = [
            'total' => Pemesanan::count(),
            'pending' => Pemesanan::where('status_pemesanan', 'pending')->count(),
            'confirmed' => Pemesanan::where('status_pemesanan', 'dikonfirmasi')->count(),
            'active' => Pemesanan::where('status_pemesanan', 'sedang_dikerjakan')->count(),
            'completed' => Pemesanan::where('status_pemesanan', 'selesai')->count(),
            'consultations_pending' => Konsultasi::where('status', 'pending')->count(),
        ];

        $consultations = Konsultasi::with(['user', 'pemesanan'])
            ->when($request->filled('consultation_status'), fn ($query) => $query->where('status', $request->consultation_status))
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 WHEN status = 'confirmed' THEN 1 ELSE 2 END")
            ->orderBy('tanggal_konsultasi')
            ->orderBy('waktu_konsultasi')
            ->paginate(5, ['*'], 'consultations_page')
            ->withQueryString();

        $pemesanans = Pemesanan::with(['user', 'katalog', 'invoice'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim();
                $numericId = (int) preg_replace('/\D/', '', (string) $search);
                $query->where(fn ($nested) => $nested
                    ->when($numericId > 0, fn ($idQuery) => $idQuery->orWhere('id', $numericId))
                    ->orWhere('jenis_proyek', 'like', "%{$search}%")
                    ->orWhere('jenis_bangunan', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($userQuery) => $userQuery
                        ->where('nama', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")));
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status_pemesanan', $request->status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.pemesanan.index', compact('pemesanans', 'consultations', 'stats'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,dikonfirmasi,sedang_dikerjakan,selesai,dibatalkan',
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
            'status_pemesanan' => ['required', Rule::in(['pending', 'dikonfirmasi', 'sedang_dikerjakan', 'selesai', 'dibatalkan'])],
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
