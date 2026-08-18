<?php

namespace App\Http\Controllers;

use App\Models\Pemesanan;
use App\Models\Konsultasi;
use App\Models\StatusTracking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function admin()
    {
        $today = today();
        $deadlineLimit = $today->copy()->addDays(7);

        $stats = [
            'active_projects' => Pemesanan::whereIn('status_pemesanan', ['dikonfirmasi', 'sedang_dikerjakan'])->count(),
            // Permintaan pelanggan dimulai sebagai konsultasi dan baru menjadi
            // pesanan setelah ditinjau admin. Keduanya perlu terlihat di dashboard.
            'new_requests' => Konsultasi::where('status', Konsultasi::STATUS_PENDING)->count()
                + Pemesanan::where('status_pemesanan', Pemesanan::STATUS_PENDING)->count(),
            'deadlines_soon' => Pemesanan::whereNotIn('status_pemesanan', ['selesai', 'dibatalkan'])
                ->whereNotNull('target_selesai')
                ->whereDate('target_selesai', '<=', $deadlineLimit)
                ->count(),
            'new_customers' => User::where('role', 'pelanggan')->where('created_at', '>=', Carbon::now()->subDays(30))->count(),
        ];

        $orderActivities = StatusTracking::with(['pemesanan.user'])
            ->whereHas('pemesanan')
            ->latest('created_at')
            ->take(6)
            ->get()
            ->map(fn (StatusTracking $tracking) => [
                'icon' => match ($tracking->status) {
                    'pending' => 'fa-inbox',
                    'dikonfirmasi' => 'fa-circle-check',
                    'sedang_dikerjakan' => 'fa-drafting-compass',
                    'selesai' => 'fa-flag-checkered',
                    'dibatalkan' => 'fa-ban',
                    default => 'fa-clock-rotate-left',
                },
                'title' => $tracking->catatan === 'Proyek dibuat dari permintaan konsultasi.'
                    ? 'Proyek baru dibuat'
                    : match ($tracking->status) {
                    'pending' => 'Pesanan baru diterima',
                    'dikonfirmasi' => 'Pesanan dikonfirmasi',
                    'sedang_dikerjakan' => 'Proyek sedang dikerjakan',
                    'selesai' => 'Proyek diselesaikan',
                    'dibatalkan' => 'Proyek dibatalkan',
                    default => 'Progres diperbarui',
                },
                'description' => ($tracking->pemesanan->user?->nama ?? 'Pelanggan').' · '.($tracking->pemesanan->jenis_proyek ?: 'Proyek interior'),
                'occurred_at' => $tracking->created_at,
                'url' => route('admin.pemesanan.index', [
                    'search' => 'DI-'.$tracking->pemesanan->id,
                ]),
            ]);

        $consultationActivities = Konsultasi::with('user')
            ->whereNull('pemesanan_id')
            ->latest('created_at')
            ->take(6)
            ->get()
            ->map(fn (Konsultasi $konsultasi) => [
                'icon' => match ($konsultasi->status) {
                    Konsultasi::STATUS_PENDING => 'fa-comments',
                    Konsultasi::STATUS_CONFIRMED => 'fa-circle-check',
                    Konsultasi::STATUS_COMPLETED => 'fa-check-double',
                    default => 'fa-ban',
                },
                'title' => match ($konsultasi->status) {
                    Konsultasi::STATUS_PENDING => 'Permintaan desain baru',
                    Konsultasi::STATUS_CONFIRMED => 'Permintaan desain dikonfirmasi',
                    Konsultasi::STATUS_COMPLETED => 'Permintaan desain selesai ditinjau',
                    default => 'Permintaan desain dibatalkan',
                },
                'description' => ($konsultasi->nama ?: ($konsultasi->user?->nama ?? 'Pelanggan')).' · '.$konsultasi->getJenisKonsultasiLabel(),
                'occurred_at' => $konsultasi->updated_at,
                'url' => route('admin.pemesanan.index', [
                    'search' => 'KS-'.$konsultasi->id,
                ]),
            ]);

        $recentActivities = $orderActivities
            ->sortByDesc('occurred_at')
            ->take(6)
            ->values();

        return view('dashboard.admin', compact('stats', 'recentActivities'));
    }

    public function designer()
    {
        $designer = auth()->user();
        $assignedProjects = Pemesanan::where('designer_id', $designer->id);
        $assignedConsultations = Konsultasi::with('user')
            ->where('designer_id', $designer->id)
            ->where('status', Konsultasi::STATUS_CONFIRMED)
            ->whereNull('pemesanan_id')
            ->latest('updated_at')
            ->get();

        $stats = [
            'assigned_projects' => (clone $assignedProjects)->count(),
            'in_progress' => (clone $assignedProjects)->where('status_pemesanan', 'sedang_dikerjakan')->count(),
            'completed_this_month' => (clone $assignedProjects)->where('status_pemesanan', 'selesai')
                ->whereYear('updated_at', now()->year)
                ->whereMonth('updated_at', now()->month)
                ->count(),
            'pending_reviews' => (clone $assignedProjects)->where('status_pemesanan', 'dikonfirmasi')->count(),
        ];

        $my_projects = Pemesanan::with(['user', 'katalog'])
            ->where('designer_id', $designer->id)
            ->latest('updated_at')
            ->take(8)
            ->get();

        return view('dashboard.designer', compact('stats', 'my_projects', 'assignedConsultations'));
    }

    public function designerProjects(Request $request)
    {
        $designer = $request->user();
        $allowedStatuses = Pemesanan::STATUSES;

        $projects = Pemesanan::with(['user', 'katalog'])
            ->where('designer_id', $designer->id)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->string('search'));
                $numericId = preg_match('/^(?:DI-?)?(\d+)$/i', $search, $match) ? (int) $match[1] : null;

                $query->where(function ($nested) use ($search, $numericId) {
                    $nested->where('jenis_proyek', 'like', "%{$search}%")
                        ->orWhere('jenis_bangunan', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($customer) => $customer
                            ->where('nama', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));

                    if ($numericId) {
                        $nested->orWhere('id', $numericId);
                    }
                });
            })
            ->when(
                in_array($request->status, $allowedStatuses, true),
                fn ($query) => $query->where('status_pemesanan', $request->status)
            )
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString();

        return view('designer.projects.index', compact('projects'));
    }

    // Admin Pages
    public function pelanggan(Request $request)
    {
        $stats = [
            'total' => User::where('role', 'pelanggan')->count(),
            'active' => User::where('role', 'pelanggan')->whereHas('pemesanans')->count(),
            'new' => User::where('role', 'pelanggan')->where('created_at', '>=', now()->subDays(30))->count(),
            'projects' => Pemesanan::count(),
        ];

        $pelanggan = User::query()
            ->where('role', 'pelanggan')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim();
                $query->where(fn ($nested) => $nested
                    ->where('nama', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('no_telp', 'like', "%{$search}%"));
            })
            ->withCount('pemesanans')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.pelanggan.index', compact('pelanggan', 'stats'));
    }

    public function users(Request $request)
    {
        $userStats = [
            'total' => User::count(),
            'admin' => User::where('role', 'admin')->count(),
            'designer' => User::where('role', 'designer')->count(),
            'pelanggan' => User::where('role', 'pelanggan')->count(),
        ];

        $users = User::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim();
                $query->where(fn ($nested) => $nested
                    ->where('nama', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->role))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'userStats'));
    }
}
