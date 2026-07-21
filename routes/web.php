<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KatalogController;
use App\Http\Controllers\PemesananController;
use App\Http\Controllers\KonsultasiController;
use App\Http\Controllers\CustomerActivityController;
use App\Http\Controllers\SitemapController;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/tentang-kami', [HomeController::class, 'about'])->name('about');
Route::get('/katalog', [HomeController::class, 'katalog'])->name('katalog');
Route::get('/katalog/{id}', [HomeController::class, 'katalogDetail'])->name('katalog.detail');
Route::get('/api/katalog/{id}', [HomeController::class, 'katalogApi'])->name('katalog.api');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

// Konsultasi routes (public)
Route::get('/konsultasi', [KonsultasiController::class, 'index'])->name('konsultasi.index');
Route::get('/konsultasi/book', [KonsultasiController::class, 'create'])->name('konsultasi.create')->middleware('auth');
Route::post('/konsultasi', [KonsultasiController::class, 'store'])->name('konsultasi.store')->middleware('auth');

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Protected routes
Route::middleware(['auth'])->group(function () {
    // Dashboard routes (Admin & Designer only)
    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])->name('dashboard.admin')->middleware('role:admin');
    Route::get('/dashboard/designer', [DashboardController::class, 'designer'])->name('dashboard.designer')->middleware('role:designer');

    // Customer routes
    Route::get('/aktivitas-saya', [CustomerActivityController::class, 'index'])->name('aktivitas.saya');
    Route::redirect('/pesanan-saya', '/aktivitas-saya?tab=pesanan')->name('pesanan.saya');
    Route::redirect('/konsultasi-saya', '/aktivitas-saya?tab=konsultasi')->name('konsultasi.saya');
    Route::get('/konsultasi/{id}', [KonsultasiController::class, 'show'])->name('konsultasi.show');

    Route::get('/pemesanan/create', [PemesananController::class, 'create'])->name('pemesanan.create');
    Route::post('/pemesanan', [PemesananController::class, 'store'])->name('pemesanan.store');
    Route::get('/pemesanan/{id}', [PemesananController::class, 'show'])->name('pemesanan.show');

    // Admin routes
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('admin/katalog', KatalogController::class, ['as' => 'admin']);
        Route::get('/admin/pemesanan', [PemesananController::class, 'index'])->name('admin.pemesanan.index');
        Route::put('/admin/pemesanan/{id}/status', [PemesananController::class, 'updateStatus'])->name('admin.pemesanan.updateStatus');

        // Admin Konsultasi
        Route::get('/admin/konsultasi', [KonsultasiController::class, 'adminIndex'])->name('admin.konsultasi.index');
        Route::put('/admin/konsultasi/{id}/status', [KonsultasiController::class, 'updateStatus'])->name('admin.konsultasi.updateStatus');
        
        // Admin Data Pelanggan
        Route::get('/admin/pelanggan', [DashboardController::class, 'pelanggan'])->name('admin.pelanggan.index');
        
        // Admin Status Pembayaran digabung ke Kelola Pemesanan
        Route::redirect('/admin/pembayaran', '/admin/pemesanan')->name('admin.pembayaran.index');
        
        // Admin Status Proyek
        Route::get('/admin/proyek', [DashboardController::class, 'proyek'])->name('admin.proyek.index');
        
        // Admin Manajemen User
        Route::get('/admin/users', [DashboardController::class, 'users'])->name('admin.users.index');
    });
});
