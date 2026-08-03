<?php

namespace App\Http\Controllers;

use App\Models\Katalog;
use App\Models\Konsultasi;
use App\Models\Pemesanan;
use App\Services\CustomerUploadService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class KonsultasiController extends Controller
{
    private const STATUS_TRANSITIONS = [
        Konsultasi::STATUS_PENDING => [Konsultasi::STATUS_PENDING, Konsultasi::STATUS_CONFIRMED, Konsultasi::STATUS_CANCELLED],
        Konsultasi::STATUS_CONFIRMED => [Konsultasi::STATUS_CONFIRMED, Konsultasi::STATUS_COMPLETED, Konsultasi::STATUS_CANCELLED],
        Konsultasi::STATUS_COMPLETED => [Konsultasi::STATUS_COMPLETED],
        Konsultasi::STATUS_CANCELLED => [Konsultasi::STATUS_CANCELLED],
    ];

    public function __construct(private readonly CustomerUploadService $uploads) {}

    public function index()
    {
        $featuredKatalogs = collect();

        if (! Config::get('app.db_offline')) {
            try {
                $featuredKatalogs = Katalog::with('category')->published()->latest()->take(3)->get();
            } catch (\Throwable $e) {
                logger()->warning('DB unavailable when loading consultation portfolio', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return view('konsultasi.index', compact('featuredKatalogs'));
    }

    public function create()
    {
        return view('konsultasi.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_telp' => 'required|string|max:20',
            'jenis_konsultasi' => 'required|in:free_consultation,virtual_design,in_home_visit,chat_support',
            'jenis_ruangan' => 'required|in:living_room,bedroom,kitchen,bathroom,office,whole_house',
            'budget_range' => 'required|in:under_10m,10m_25m,25m_50m,50m_100m,above_100m',
            'timeline' => 'required|in:immediate,1_month,3_months,6_months,flexible',
            'luas_ruangan' => 'nullable|numeric|min:1',
            'gaya_preferensi' => 'nullable|string',
            'deskripsi_kebutuhan' => 'required|string',
            'tanggal_konsultasi' => 'required|date|after:today',
            'waktu_konsultasi' => 'required|date_format:H:i',
            'upload_foto.*' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        $slotTaken = Konsultasi::query()
            ->whereDate('tanggal_konsultasi', $request->tanggal_konsultasi)
            ->whereTime('waktu_konsultasi', $request->waktu_konsultasi)
            ->whereIn('status', [Konsultasi::STATUS_PENDING, Konsultasi::STATUS_CONFIRMED])
            ->exists();

        if ($slotTaken) {
            throw ValidationException::withMessages([
                'waktu_konsultasi' => 'Waktu tersebut sudah dipesan. Silakan pilih jadwal lain.',
            ]);
        }

        $user = $request->user();
        $contactNumber = $user->no_telp ?: trim((string) $request->no_telp);

        if (! $user->no_telp) {
            $user->update(['no_telp' => $contactNumber]);
        }

        // Handle file uploads
        $uploadedFiles = $this->uploads->storeMany(
            $request->file('upload_foto', []),
            "customer-uploads/consultations/{$user->id}"
        );

        try {
            $konsultasi = Konsultasi::create([
                'user_id' => $user->id,
                'nama' => $user->nama,
                'email' => $user->email,
                'no_telp' => $contactNumber,
                'jenis_konsultasi' => $request->jenis_konsultasi,
                'jenis_ruangan' => $request->jenis_ruangan,
                'budget_range' => $request->budget_range,
                'timeline' => $request->timeline,
                'luas_ruangan' => $request->luas_ruangan,
                'gaya_preferensi' => $request->gaya_preferensi,
                'deskripsi_kebutuhan' => $request->deskripsi_kebutuhan,
                'upload_foto' => $uploadedFiles,
                'tanggal_konsultasi' => $request->tanggal_konsultasi,
                'waktu_konsultasi' => $request->waktu_konsultasi,
                'status' => Konsultasi::STATUS_PENDING,
            ]);
        } catch (QueryException $exception) {
            $this->uploads->deleteMany($uploadedFiles);
            $this->throwIfSlotConflict($exception);
            throw $exception;
        }

        return redirect()->route('konsultasi.show', $konsultasi->id)
            ->with('success', 'Konsultasi berhasil dijadwalkan! Tim kami akan menghubungi Anda segera.');
    }

    public function show($id)
    {
        $konsultasi = Konsultasi::with('user')->findOrFail($id);

        // Ensure user can only see their own consultation
        if (! auth()->user()->isAdmin() && $konsultasi->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('konsultasi.show', compact('konsultasi'));
    }

    public function attachment(Konsultasi $konsultasi, int $index)
    {
        $user = auth()->user();
        abort_unless($user->isAdmin() || $konsultasi->user_id === $user->id, 403);

        return $this->uploads->response($konsultasi->upload_foto ?? [], $index);
    }

    public function updateStatus(Request $request, Konsultasi $konsultasi)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(Konsultasi::STATUSES)],
            'catatan_admin' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($konsultasi->pemesanan_id && $data['status'] === Konsultasi::STATUS_CANCELLED) {
            throw ValidationException::withMessages([
                'status' => 'Konsultasi yang sudah menjadi proyek tidak dapat dibatalkan dari antrean ini.',
            ]);
        }

        if (! in_array($data['status'], self::STATUS_TRANSITIONS[$konsultasi->status] ?? [], true)) {
            throw ValidationException::withMessages([
                'status' => "Status {$konsultasi->status} tidak dapat langsung diubah menjadi {$data['status']}.",
            ]);
        }

        try {
            $konsultasi->update($data);
        } catch (QueryException $exception) {
            $this->throwIfSlotConflict($exception);
            throw $exception;
        }

        return back()->with('success', 'Status konsultasi berhasil diperbarui.');
    }

    private function throwIfSlotConflict(QueryException $exception): void
    {
        if (str_contains(strtolower($exception->getMessage()), 'active_slot')) {
            throw ValidationException::withMessages([
                'waktu_konsultasi' => 'Waktu tersebut sudah dipesan. Silakan pilih jadwal lain.',
            ]);
        }
    }

    public function convertToProject(Request $request, Konsultasi $konsultasi)
    {
        if ($konsultasi->pemesanan_id) {
            return redirect()->route('pemesanan.show', $konsultasi->pemesanan_id)
                ->with('success', 'Konsultasi ini sudah terhubung ke proyek.');
        }

        if ($konsultasi->status !== Konsultasi::STATUS_CONFIRMED) {
            throw ValidationException::withMessages([
                'status' => 'Konfirmasi konsultasi sebelum meneruskannya menjadi proyek.',
            ]);
        }

        $project = DB::transaction(function () use ($request, $konsultasi): Pemesanan {
            $project = Pemesanan::create([
                'id_user' => $konsultasi->user_id,
                'tanggal_pesan' => now()->toDateString(),
                'sumber_masuk' => 'website',
                'status_pemesanan' => Pemesanan::STATUS_CONFIRMED,
                'progress' => 10,
                'total_harga' => 0,
                'jenis_proyek' => 'Konsultasi '.$konsultasi->getJenisRuanganLabel(),
                'jenis_bangunan' => 'Belum ditentukan',
                'luas_area' => $konsultasi->luas_ruangan,
                'jumlah_ruangan' => 1,
                'gaya_desain_preferensi' => $konsultasi->gaya_preferensi,
                'deskripsi_keinginan_desain' => $konsultasi->deskripsi_kebutuhan,
                'upload_denah_foto' => $konsultasi->upload_foto,
            ]);

            $project->statusTrackings()->create([
                'actor_id' => $request->user()->id,
                'previous_status' => null,
                'status' => Pemesanan::STATUS_CONFIRMED,
                'progress' => 10,
                'tanggal_update' => now()->toDateString(),
                'catatan' => 'Proyek dibuat dari permintaan konsultasi.',
            ]);

            $konsultasi->update([
                'pemesanan_id' => $project->id,
                'status' => Konsultasi::STATUS_COMPLETED,
                'catatan_admin' => 'Permintaan diteruskan menjadi proyek DI-'.str_pad((string) $project->id, 3, '0', STR_PAD_LEFT).'.',
            ]);

            return $project;
        });

        return redirect()->route('pemesanan.show', $project)
            ->with('success', 'Konsultasi berhasil diteruskan menjadi proyek.');
    }
}
