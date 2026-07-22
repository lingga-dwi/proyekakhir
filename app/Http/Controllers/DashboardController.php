<?php

namespace App\Http\Controllers;

use App\Models\Pemesanan;
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
            'pending_orders' => Pemesanan::where('status_pemesanan', 'pending')->count(),
            'deadlines_soon' => Pemesanan::whereNotIn('status_pemesanan', ['selesai', 'dibatalkan'])
                ->whereNotNull('target_selesai')
                ->whereDate('target_selesai', '<=', $deadlineLimit)
                ->count(),
            'new_customers' => User::where('role', 'pelanggan')->where('created_at', '>=', Carbon::now()->subDays(30))->count(),
        ];

        $attentionProjects = Pemesanan::with(['user', 'designer'])
            ->whereNotIn('status_pemesanan', ['selesai', 'dibatalkan'])
            ->orderByRaw(
                "CASE
                    WHEN status_pemesanan = 'pending' THEN 0
                    WHEN target_selesai IS NOT NULL AND target_selesai < ? THEN 1
                    WHEN target_selesai IS NOT NULL AND target_selesai <= ? THEN 2
                    WHEN designer_id IS NULL THEN 3
                    ELSE 4
                END",
                [$today->toDateString(), $deadlineLimit->toDateString()]
            )
            ->orderByRaw('CASE WHEN target_selesai IS NULL THEN 1 ELSE 0 END')
            ->orderBy('target_selesai')
            ->latest('updated_at')
            ->take(8)
            ->get();

        $recentActivities = StatusTracking::with(['pemesanan.user'])
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
                'title' => match ($tracking->status) {
                    'pending' => 'Pesanan baru diterima',
                    'dikonfirmasi' => 'Pesanan dikonfirmasi',
                    'sedang_dikerjakan' => 'Proyek sedang dikerjakan',
                    'selesai' => 'Proyek diselesaikan',
                    'dibatalkan' => 'Proyek dibatalkan',
                    default => 'Progres diperbarui',
                },
                'description' => ($tracking->pemesanan->user?->nama ?? 'Pelanggan').' · '.($tracking->pemesanan->jenis_proyek ?: 'Proyek interior'),
                'occurred_at' => $tracking->created_at,
                'url' => route('pemesanan.show', $tracking->pemesanan),
            ]);

        return view('dashboard.admin', compact('stats', 'attentionProjects', 'recentActivities'));
    }

    public function designer()
    {
        $designer = auth()->user();
        $assignedProjects = Pemesanan::where('designer_id', $designer->id);

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

        return view('dashboard.designer', compact('stats', 'my_projects'));
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

    public function proyek(Request $request)
    {
        $projectStats = [
            'persiapan' => Pemesanan::whereIn('status_pemesanan', ['pending', 'dikonfirmasi'])->count(),
            'tahap_desain_produksi' => Pemesanan::where('status_pemesanan', 'sedang_dikerjakan')->count(),
            'selesai' => Pemesanan::where('status_pemesanan', 'selesai')->count(),
            'dibatalkan' => Pemesanan::where('status_pemesanan', 'dibatalkan')->count(),
        ];

        $proyek = Pemesanan::with(['user', 'designer'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim();
                $query->where(fn ($nested) => $nested
                    ->where('jenis_proyek', 'like', "%{$search}%")
                    ->orWhere('jenis_bangunan', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($userQuery) => $userQuery
                        ->where('nama', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")));
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status_pemesanan', $request->status))
            ->when($request->filled('designer'), fn ($query) => $query->where('designer_id', $request->designer))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $designers = User::where('role', 'designer')->orderBy('nama')->get(['id', 'nama']);

        return view('admin.proyek.index', compact('proyek', 'projectStats', 'designers'));
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
