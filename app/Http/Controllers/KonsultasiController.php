<?php

namespace App\Http\Controllers;

use App\Models\Katalog;
use App\Models\Konsultasi;
use App\Models\Pemesanan;
use App\Models\User;
use App\Services\DaikuNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class KonsultasiController extends Controller
{
    public function __construct(private readonly DaikuNotificationService $notifications) {}

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
            'jenis_konsultasi' => 'required|in:free_consultation,virtual_design,in_home_visit',
            'jenis_ruangan' => 'required|in:living_room,bedroom,kitchen,bathroom,office,whole_house',
            'budget_range' => 'required|in:under_10m,10m_25m,25m_50m,50m_100m,above_100m',
            'luas_ruangan' => 'required|numeric|min:1',
            'deskripsi_kebutuhan' => 'nullable|string|max:10000',
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
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
                'alamat' => $data['alamat'],
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

        if ($request->hasFile('attachments')) {
            $attachments = [];
            foreach ($request->file('attachments') as $file) {
                $attachments[] = [
                    'path' => $file->store('consultation-attachments/'.$konsultasi->id, 'local'),
                    'name' => $file->getClientOriginalName(),
                ];
            }
            $konsultasi->update(['attachments' => $attachments]);
        }

        $this->notifications->admins(
            'Permintaan konsultasi baru',
            $user->nama.' mengirim kebutuhan desain '.$konsultasi->getJenisRuanganLabel().'.',
            route('admin.pemesanan.index', ['search' => $user->email], false),
            'Tinjau permintaan'
        );

        return redirect()->route('konsultasi.show', $konsultasi->id)
            ->with('success', 'Permintaan berhasil dikirim. Tim Daiku akan menghubungi Anda segera.');
    }

    public function show($id)
    {
        $konsultasi = Konsultasi::with(['user', 'pemesanan'])->findOrFail($id);

        $canAccess = auth()->user()->isAdmin()
            || $konsultasi->user_id === auth()->id()
            || $konsultasi->designer_id === auth()->id();
        if (! $canAccess) {
            abort(403, 'Unauthorized action.');
        }

        // Status konsultasi dapat diubah oleh admin di sesi/perangkat lain.
        // Hindari browser menampilkan snapshot halaman lama saat pelanggan kembali ke sini.
        return response()
            ->view('konsultasi.show', compact('konsultasi'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function downloadAttachment(Request $request, Konsultasi $konsultasi, int $index)
    {
        $user = $request->user();
        $canAccess = $user->isAdmin()
            || $konsultasi->user_id === $user->id
            || $konsultasi->designer_id === $user->id;

        abort_unless($canAccess, 403, 'Unauthorized action.');

        $attachment = $konsultasi->attachments[$index] ?? null;
        $path = is_array($attachment) ? ($attachment['path'] ?? null) : $attachment;
        $name = is_array($attachment) ? ($attachment['name'] ?? basename((string) $path)) : basename((string) $path);
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, $name);
    }

    public function updateStatus(Request $request, Konsultasi $konsultasi)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(Konsultasi::STATUSES)],
            'catatan_admin' => ['nullable', 'string', 'max:2000'],
            'consultation_result' => ['nullable', 'string', 'max:5000'],
        ]);

        if ($konsultasi->pemesanan_id && $data['status'] === Konsultasi::STATUS_CANCELLED) {
            throw ValidationException::withMessages([
                'status' => 'Konsultasi yang sudah menjadi proyek tidak dapat dibatalkan dari antrean ini.',
            ]);
        }

        if (
            $konsultasi->status === Konsultasi::STATUS_PENDING
            && $konsultasi->accepted_at
            && $data['status'] === Konsultasi::STATUS_CANCELLED
        ) {
            throw ValidationException::withMessages([
                'status' => 'Permintaan yang sudah diterima tidak dapat ditolak. Lanjutkan dengan menugaskan desainer.',
            ]);
        }

        if (! in_array($data['status'], self::STATUS_TRANSITIONS[$konsultasi->status] ?? [], true)) {
            throw ValidationException::withMessages([
                'status' => "Status {$konsultasi->status} tidak dapat langsung diubah menjadi {$data['status']}.",
            ]);
        }

        if ($data['status'] === Konsultasi::STATUS_COMPLETED) {
            if (! $konsultasi->designer_id) {
                throw ValidationException::withMessages([
                    'status' => 'Tugaskan desainer sebelum konsultasi diteruskan ke tahap desain awal.',
                ]);
            }

            $result = trim((string) ($data['consultation_result'] ?? $konsultasi->consultation_result));
            if ($result === '') {
                throw ValidationException::withMessages([
                    'consultation_result' => 'Catatan hasil konsultasi wajib diisi sebelum masuk ke desain awal.',
                ]);
            }

            $konsultasi->update([
                'consultation_result' => $result,
                'consulted_at' => $konsultasi->consulted_at ?: now(),
            ]);

            $project = $this->createProjectFromConsultation($request, $konsultasi, $data['catatan_admin'] ?? null);

            return redirect()->route('admin.pemesanan.index', ['search' => 'DI-'.$project->id])
                ->with('success', 'Konsultasi selesai dan proyek masuk ke tahap desain awal.');
        }

        if (in_array($data['status'], [Konsultasi::STATUS_CANCELLED, Konsultasi::STATUS_COMPLETED], true)) {
            $data['active_slot'] = null;
        }

        $konsultasi->update($data);

        return back()->with('success', 'Status konsultasi berhasil diperbarui.');
    }

    public function assignDesigner(Request $request, Konsultasi $konsultasi)
    {
        if (
            ($konsultasi->pemesanan_id && $konsultasi->pemesanan?->workflow_stage !== 'konsultasi')
            || ! in_array($konsultasi->status, [Konsultasi::STATUS_PENDING, Konsultasi::STATUS_CONFIRMED], true)
            || ! $konsultasi->accepted_at
        ) {
            abort(422, 'Desainer tidak dapat ditugaskan pada konsultasi ini.');
        }

        $data = $request->validate([
            'designer_id' => ['required', Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'designer'))],
            'catatan_admin' => ['nullable', 'string', 'max:2000'],
        ]);

        $konsultasi->update([
            'designer_id' => $data['designer_id'],
            'catatan_admin' => $data['catatan_admin'] ?? $konsultasi->catatan_admin,
            'scheduled_by' => $request->user()->id,
            'active_slot' => null,
            'status' => Konsultasi::STATUS_CONFIRMED,
        ]);

        if ($konsultasi->pemesanan_id) {
            $konsultasi->pemesanan()->update(['designer_id' => $data['designer_id']]);
        }

        $this->notifications->send(
            $konsultasi->user,
            'Desainer konsultasi telah ditugaskan',
            'Desainer Daiku telah ditugaskan dan akan menghubungi Anda melalui WhatsApp untuk konsultasi.',
            route('konsultasi.show', $konsultasi, false),
            'Lihat konsultasi'
        );
        $designer = User::find($data['designer_id']);
        if ($designer) {
            $this->notifications->send(
                $designer,
                'Konsultasi baru ditugaskan',
                'Anda ditugaskan untuk berkonsultasi dengan '.$konsultasi->nama.' melalui WhatsApp.',
                route('dashboard.designer', [], false),
                'Buka dashboard'
            );
        }

        return back()->with('success', 'Desainer konsultasi berhasil ditugaskan.');
    }

    public function accept(Request $request, Konsultasi $konsultasi)
    {
        abort_unless(
            ! $konsultasi->pemesanan_id
                && $konsultasi->status === Konsultasi::STATUS_PENDING
                && ! $konsultasi->accepted_at,
            422,
            'Permintaan konsultasi ini tidak dapat diterima.'
        );

        $data = $request->validate([
            'designer_id' => ['required', Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'designer'))],
        ]);

        $project = DB::transaction(function () use ($request, $konsultasi, $data) {
            $konsultasi->update([
                'accepted_by' => $request->user()->id,
                'accepted_at' => now(),
                'designer_id' => $data['designer_id'],
                'scheduled_by' => $request->user()->id,
                'active_slot' => null,
                'status' => Konsultasi::STATUS_CONFIRMED,
            ]);

            return $this->createProjectAtKonsultasiStage($request, $konsultasi);
        });

        $this->notifications->send(
            $konsultasi->user,
            'Permintaan diterima dan desainer ditugaskan',
            'Permintaan desain Anda telah diterima. Desainer Daiku akan menghubungi Anda melalui WhatsApp untuk konsultasi.',
            route('konsultasi.show', $konsultasi, false),
            'Lihat permintaan'
        );

        $designer = User::find($data['designer_id']);
        if ($designer) {
            $this->notifications->send(
                $designer,
                'Konsultasi baru ditugaskan',
                'Anda ditugaskan untuk berkonsultasi dengan '.$konsultasi->nama.' melalui WhatsApp.',
                route('dashboard.designer', [], false),
                'Buka dashboard'
            );
        }

        return back()->with('success', 'Permintaan diterima dan desainer konsultasi berhasil ditugaskan.');
    }

    public function completeByDesigner(Request $request, Konsultasi $konsultasi)
    {
        abort_unless($konsultasi->designer_id === $request->user()->id, 403);
        abort_unless($konsultasi->pemesanan_id, 422, 'Konsultasi ini belum memiliki proyek terkait.');

        $project = $konsultasi->pemesanan;
        abort_unless(
            $konsultasi->status === Konsultasi::STATUS_CONFIRMED && $project && $project->workflow_stage === 'konsultasi',
            422,
            'Konsultasi ini tidak dapat diselesaikan pada tahap sekarang.'
        );

        $data = $request->validate([
            'consultation_result' => ['required', 'string', 'max:5000'],
        ]);

        $konsultasi->update([
            'consultation_result' => $data['consultation_result'],
            'consulted_at' => now(),
            'status' => Konsultasi::STATUS_COMPLETED,
        ]);

        $project->update([
            'workflow_stage' => 'draft_design',
            'progress' => 10,
            'catatan_progres' => 'Konsultasi selesai. Proyek masuk ke tahap desain awal.',
        ]);
        $project->statusTrackings()->create([
            'actor_id' => $request->user()->id,
            'previous_status' => $project->status_pemesanan,
            'status' => $project->status_pemesanan,
            'progress' => 10,
            'tanggal_update' => now()->toDateString(),
            'catatan' => 'Konsultasi selesai. Proyek masuk ke tahap desain awal.',
        ]);

        $this->notifications->send(
            $project->user,
            'Proyek desain dimulai',
            'Konsultasi selesai dan proyek DI-'.$project->id.' masuk ke tahap desain awal.',
            route('pemesanan.show', $project, false),
            'Lihat proyek'
        );
        $this->notifications->admins(
            'Konsultasi selesai',
            'Desainer menyelesaikan konsultasi '.$konsultasi->nama.' dan proyek DI-'.$project->id.' masuk ke tahap desain awal.',
            route('admin.pemesanan.show', $project, false),
            'Tinjau proyek'
        );

        return redirect()->route('pemesanan.show', $project)
            ->with('success', 'Hasil konsultasi tersimpan dan proyek masuk ke tahap desain awal.');
    }

    public function convertToProject(Request $request, Konsultasi $konsultasi)
    {
        if ($konsultasi->pemesanan_id) {
            return redirect()->route('pemesanan.show', $konsultasi->pemesanan_id)
                ->with('success', 'Konsultasi ini sudah terhubung ke proyek.');
        }

        if ($konsultasi->status !== Konsultasi::STATUS_COMPLETED) {
            throw ValidationException::withMessages(['status' => 'Selesaikan konsultasi sebelum meneruskannya menjadi proyek.']);
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
                'workflow_stage' => 'draft_design',
                'progress' => 10,
                'designer_id' => $konsultasi->designer_id,
                'total_harga' => 0,
                'jenis_proyek' => $konsultasi->getJenisKonsultasiLabel(),
                'jenis_bangunan' => $konsultasi->getJenisRuanganLabel(),
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
                'active_slot' => null,
                'catatan_admin' => $adminNote ?: 'Permintaan diteruskan menjadi proyek DI-'.str_pad((string) $project->id, 3, '0', STR_PAD_LEFT).'.',
            ]);

            $this->notifications->send(
                $project->user,
                'Proyek desain dimulai',
                'Konsultasi selesai dan proyek DI-'.$project->id.' masuk ke tahap desain awal.',
                route('pemesanan.show', $project, false),
                'Lihat proyek'
            );
            if ($project->designer) {
                $this->notifications->send(
                    $project->designer,
                    'Proyek baru ditugaskan',
                    'Proyek DI-'.$project->id.' siap dikerjakan pada tahap desain awal dan RAB.',
                    route('pemesanan.show', $project, false),
                    'Buka proyek'
                );
            }

            return $project;
        });
    }

    /**
     * Creates the project record as soon as a consultation is accepted, so admin/designer
     * can already prepare desain & RAB while the WhatsApp consultation is still ongoing.
     * The project starts at the 'konsultasi' workflow stage and only advances to
     * 'draft_design' once the designer records the consultation result.
     */
    private function createProjectAtKonsultasiStage(Request $request, Konsultasi $konsultasi): Pemesanan
    {
        $project = Pemesanan::create([
            'id_user' => $konsultasi->user_id,
            'tanggal_pesan' => now()->toDateString(),
            'sumber_masuk' => 'website',
            'status_pemesanan' => Pemesanan::STATUS_CONFIRMED,
            'workflow_stage' => 'konsultasi',
            'progress' => 5,
            'designer_id' => $konsultasi->designer_id,
            'total_harga' => 0,
            'jenis_proyek' => $konsultasi->getJenisKonsultasiLabel(),
            'jenis_bangunan' => $konsultasi->getJenisRuanganLabel(),
            'luas_area' => $konsultasi->luas_ruangan,
            'deskripsi_keinginan_desain' => $konsultasi->deskripsi_kebutuhan,
        ]);

        $project->statusTrackings()->create([
            'actor_id' => $request->user()->id,
            'previous_status' => null,
            'status' => Pemesanan::STATUS_CONFIRMED,
            'progress' => 5,
            'tanggal_update' => now()->toDateString(),
            'catatan' => 'Proyek dibuat saat permintaan konsultasi diterima.',
        ]);

        $konsultasi->update(['pemesanan_id' => $project->id]);

        return $project;
    }
}
