<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Pemesanan;
use App\Models\Invoice;
use App\Models\Konsultasi;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function admin()
    {
        $stats = [
            'total_projects' => Pemesanan::count(),
            'pending_orders' => Pemesanan::where('status_pemesanan', 'pending')->count(),
            'completed_projects' => Pemesanan::where('status_pemesanan', 'selesai')->count(),
            'total_revenue' => Invoice::query()
                ->where('status_invoice', 'dibayar')
                ->whereHas('pemesanan', fn ($query) => $query->where('status_pemesanan', 'selesai'))
                ->sum('total_tagihan'),
            'consultations' => Konsultasi::where('status', 'confirmed')->count(),
            'new_customers' => User::where('role', 'pelanggan')->where('created_at', '>=', Carbon::now()->subDays(30))->count(),
        ];

        $recent_projects = Pemesanan::with(['user', 'katalog', 'rfq.katalog'])
                                  ->latest()
                                  ->take(5)
                                  ->get();

        $monthly_orders = Pemesanan::query()
            ->whereYear('created_at', Carbon::now()->year)
            ->get(['created_at'])
            ->groupBy(fn ($pemesanan) => $pemesanan->created_at->month)
            ->map(fn ($items) => $items->count())
            ->toArray();

        return view('dashboard.admin', compact('stats', 'recent_projects', 'monthly_orders'));
    }

    public function designer()
    {
        $stats = [
            'assigned_projects' => Pemesanan::count(), // In real app, filter by assigned designer
            'in_progress' => Pemesanan::where('status_pemesanan', 'sedang_dikerjakan')->count(),
            'completed_this_month' => Pemesanan::where('status_pemesanan', 'selesai')
                                              ->whereMonth('updated_at', Carbon::now()->month)
                                              ->count(),
            'pending_reviews' => Pemesanan::where('status_pemesanan', 'dikonfirmasi')->count(),
        ];

        $my_projects = Pemesanan::with(['user', 'katalog', 'rfq.katalog'])
                               ->latest()
                               ->take(5)
                               ->get();

        return view('dashboard.designer', compact('stats', 'my_projects'));
    }

    public function pelangganDashboard()
    {
        $user = auth()->user();
        
        $stats = [
            'total_projects' => $user->pemesanans()->count(),
            'active_projects' => $user->pemesanans()->whereIn('status_pemesanan', ['pending', 'dikonfirmasi', 'sedang_dikerjakan'])->count(),
            'completed_projects' => $user->pemesanans()->where('status_pemesanan', 'selesai')->count(),
            'total_spent' => Invoice::query()
                ->where('status_invoice', 'dibayar')
                ->whereHas('pemesanan', fn ($query) => $query->where('id_user', $user->id))
                ->sum('total_tagihan'),
        ];

        $my_orders = $user->pemesanans()
                         ->with(['katalog', 'rfq.katalog'])
                         ->latest()
                         ->take(5)
                         ->get();

        $upcoming_activities = [
            [
                'time' => '09:00',
                'title' => 'Konsultasi Ruang - Maya Indira',
                'type' => 'meeting'
            ],
            [
                'time' => '12:00',
                'title' => 'Review Progress Proyek Mingguan',
                'type' => 'review'
            ],
            [
                'time' => '01:30',
                'title' => 'Inspeksi Furniture - Budi Santoso',
                'type' => 'inspection'
            ]
        ];

        return view('dashboard.pelanggan', compact('stats', 'my_orders', 'upcoming_activities'));
    }

    // Admin Pages
    public function pelanggan()
    {
        $pelanggan = User::where('role', 'pelanggan')
                        ->withCount(['pemesanans'])
                        ->latest()
                        ->paginate(10);
        
        return view('admin.pelanggan.index', compact('pelanggan'));
    }

    public function proyek()
    {
        $projectStats = [
            'konsultasi' => Pemesanan::whereIn('status_pemesanan', ['pending', 'dikonfirmasi'])->count(),
            'tahap_desain_produksi' => Pemesanan::where('status_pemesanan', 'sedang_dikerjakan')->count(),
            'persetujuan' => Pemesanan::where('status_pemesanan', 'dibatalkan')->count(),
            'selesai' => Pemesanan::where('status_pemesanan', 'selesai')->count(),
        ];

        $featuredProjects = Pemesanan::with(['user'])
            ->latest()
            ->take(4)
            ->get();

        $proyek = Pemesanan::with(['user'])
            ->latest()
            ->paginate(10);
        
        return view('admin.proyek.index', compact('proyek', 'projectStats', 'featuredProjects'));
    }

    public function users()
    {
        $userStats = [
            'total' => User::count(),
            'admin' => User::where('role', 'admin')->count(),
            'designer' => User::where('role', 'designer')->count(),
            'pelanggan' => User::where('role', 'pelanggan')->count(),
        ];

        $users = User::latest()->paginate(10);
        
        return view('admin.users.index', compact('users', 'userStats'));
    }
}
