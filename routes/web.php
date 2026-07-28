<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerActivityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KatalogController;
use App\Http\Controllers\KonsultasiController;
use App\Http\Controllers\PemesananController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

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
Route::post('/konsultasi', [KonsultasiController::class, 'store'])->name('konsultasi.store')->middleware(['auth', 'throttle:customer-forms']);

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('throttle:login');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post')->middleware('throttle:login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email')->middleware('throttle:password');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update')->middleware('throttle:password');

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
    Route::get('/konsultasi/{konsultasi}/lampiran/{index}', [KonsultasiController::class, 'attachment'])
        ->whereNumber('index')->name('konsultasi.attachment');

    Route::get('/pemesanan/create', [PemesananController::class, 'create'])->name('pemesanan.create');
    Route::post('/pemesanan', [PemesananController::class, 'store'])->name('pemesanan.store')->middleware('throttle:customer-forms');
    Route::get('/pemesanan/{id}', [PemesananController::class, 'show'])->name('pemesanan.show');
    Route::get('/pemesanan/{pemesanan}/lampiran/{index}', [PemesananController::class, 'attachment'])
        ->whereNumber('index')->name('pemesanan.attachment');

    // Admin routes
    Route::middleware(['role:admin'])->group(function () {
        Route::post('/admin/katalog/bulk-action', [KatalogController::class, 'bulkAction'])->name('admin.katalog.bulk-action');
        Route::resource('admin/katalog', KatalogController::class, ['as' => 'admin']);
        Route::get('/admin/pemesanan', [PemesananController::class, 'index'])->name('admin.pemesanan.index');
        Route::post('/admin/pemesanan', [PemesananController::class, 'storeAdmin'])->name('admin.pemesanan.store');
        Route::put('/admin/pemesanan/{id}/status', [PemesananController::class, 'updateStatus'])->name('admin.pemesanan.updateStatus');
        Route::put('/admin/pemesanan/konsultasi/{konsultasi}/status', [KonsultasiController::class, 'updateStatus'])
            ->name('admin.pemesanan.konsultasi.update');
        Route::post('/admin/pemesanan/konsultasi/{konsultasi}/proyek', [KonsultasiController::class, 'convertToProject'])
            ->name('admin.pemesanan.konsultasi.convert');

        // Admin Data Pelanggan
        Route::get('/admin/pelanggan', [DashboardController::class, 'pelanggan'])->name('admin.pelanggan.index');

        // URL lama diarahkan ke alur pesanan yang kini memuat pengelolaan proyek.
        Route::redirect('/admin/proyek', '/admin/pemesanan')->name('admin.proyek.index');
        Route::put('/admin/proyek/{pemesanan}', [PemesananController::class, 'updateProject'])->name('admin.proyek.update');

        // Admin Manajemen User
        Route::get('/admin/users', [DashboardController::class, 'users'])->name('admin.users.index');
        Route::post('/admin/users', [AdminUserController::class, 'store'])->name('admin.users.store');
        Route::put('/admin/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
        Route::delete('/admin/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
    });
});
