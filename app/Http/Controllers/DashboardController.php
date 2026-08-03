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

        return view('dashboard.admin', compact('stats', 'recentActivities'));
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
