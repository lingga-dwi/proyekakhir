<?php

namespace App\Http\Controllers;

use App\Models\Katalog;
use App\Models\Konsultasi;
use App\Models\Pemesanan;
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
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'no_telp' => 'required|string|max:20',
            'alamat' => 'required|string|max:2000',
            'jenis_konsultasi' => 'required|in:free_consultation,virtual_design,in_home_visit,chat_support',
            'jenis_ruangan' => 'required|in:living_room,bedroom,kitchen,bathroom,office,whole_house',
            'budget_range' => 'required|in:under_10m,10m_25m,25m_50m,50m_100m,above_100m',
            'luas_ruangan' => 'required|numeric|min:0',
            'deskripsi_kebutuhan' => 'nullable|string|max:10000',
        ]);

        $user = $request->user();
        $email = $data['email'] ?: $user->email;
        $user->update([
            'nama' => $data['nama'],
            'no_telp' => $data['no_telp'],
            'alamat' => $data['alamat'],
        ]);

        $konsultasi = Konsultasi::create([
                'user_id' => $user->id,
                'nama' => $data['nama'],
                'email' => $email,
                'no_telp' => $data['no_telp'],
                'jenis_konsultasi' => $data['jenis_konsultasi'],
                'jenis_ruangan' => $data['jenis_ruangan'],
                'budget_range' => $data['budget_range'],
                'timeline' => 'flexible',
                'luas_ruangan' => $data['luas_ruangan'],
                'deskripsi_kebutuhan' => $data['deskripsi_kebutuhan'] ?? '',
                'tanggal_konsultasi' => now()->toDateString(),
                'waktu_konsultasi' => now()->format('H:i:s'),
                'status' => Konsultasi::STATUS_PENDING,
            ]);

        return redirect()->route('konsultasi.show', $konsultasi->id)
            ->with('success', 'Permintaan berhasil dikirim. Tim Daiku akan menghubungi Anda segera.');
    }

    public function show($id)
    {
        $konsultasi = Konsultasi::with('user')->findOrFail($id);

        // Ensure user can only see their own consultation
        if (! auth()->user()->isAdmin() && $konsultasi->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Status konsultasi dapat diubah oleh admin di sesi/perangkat lain.
        // Hindari browser menampilkan snapshot halaman lama saat pelanggan kembali ke sini.
        return response()
            ->view('konsultasi.show', compact('konsultasi'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
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

        if ($data['status'] === Konsultasi::STATUS_CONFIRMED) {
            $project = $this->createProjectFromConsultation($request, $konsultasi, $data['catatan_admin'] ?? null);

            return redirect()->route('admin.pemesanan.index', ['search' => 'DI-'.$project->id])
                ->with('success', 'Permintaan dikonfirmasi dan langsung dibuat sebagai proyek.');
        }

        $konsultasi->update($data);

        return back()->with('success', 'Status konsultasi berhasil diperbarui.');
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

        $project = $this->createProjectFromConsultation($request, $konsultasi);

        return redirect()->route('admin.pemesanan.index', ['search' => 'DI-'.$project->id])
            ->with('success', 'Konsultasi berhasil diteruskan menjadi proyek.');
    }

    private function createProjectFromConsultation(Request $request, Konsultasi $konsultasi, ?string $adminNote = null): Pemesanan
    {
        return DB::transaction(function () use ($request, $konsultasi, $adminNote): Pemesanan {
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
                'deskripsi_keinginan_desain' => $konsultasi->deskripsi_kebutuhan,
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
                'catatan_admin' => $adminNote ?: 'Permintaan diteruskan menjadi proyek DI-'.str_pad((string) $project->id, 3, '0', STR_PAD_LEFT).'.',
            ]);

            return $project;
        });
    }
}
