<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminFaqController;
use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerActivityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KatalogController;
use App\Http\Controllers\KonsultasiController;
use App\Http\Controllers\NotificationController;
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
Route::get('/konsultasi/book', [KonsultasiController::class, 'create'])->name('konsultasi.create')->middleware(['auth', 'role:pelanggan']);
Route::post('/konsultasi', [KonsultasiController::class, 'store'])->name('konsultasi.store')->middleware(['auth', 'role:pelanggan', 'throttle:customer-forms']);

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
    Route::get('/notifikasi/{notification}', [NotificationController::class, 'open'])->name('notifications.open');
    Route::post('/notifikasi/tandai-semua-dibaca', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // Dashboard routes (Admin & Designer only)
    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])->name('dashboard.admin')->middleware('role:admin');
    Route::get('/dashboard/designer', [DashboardController::class, 'designer'])->name('dashboard.designer')->middleware('role:designer');
    Route::get('/designer/proyek', [DashboardController::class, 'designerProjects'])
        ->name('designer.projects.index')->middleware('role:designer');

    // Customer-only routes
    Route::middleware(['role:pelanggan'])->group(function () {
        Route::get('/pesanan-saya', [CustomerActivityController::class, 'index'])->name('pesanan.saya');
        Route::get('/aktivitas-saya', fn (\Illuminate\Http\Request $request) => redirect()->route('pesanan.saya', $request->only('tab')))
            ->name('aktivitas.saya');
        Route::redirect('/konsultasi-saya', '/pesanan-saya?tab=konsultasi')->name('konsultasi.saya');
    Route::get('/pemesanan/create', [PemesananController::class, 'create'])->name('pemesanan.create');
    Route::post('/pemesanan', [PemesananController::class, 'store'])->name('pemesanan.store')->middleware('throttle:customer-forms');
    Route::post('/pemesanan/{pemesanan}/bukti-pembayaran', [PemesananController::class, 'uploadPaymentEvidence'])
        ->name('pemesanan.payment-evidence.upload')->middleware('throttle:customer-forms');
    Route::post('/pemesanan/{pemesanan}/dp/bukti-pembayaran', [PemesananController::class, 'uploadDpEvidence'])
        ->name('pemesanan.dp-evidence.upload')->middleware('throttle:customer-forms');
    Route::post('/pemesanan/{pemesanan}/dokumen/keputusan', [PemesananController::class, 'decideDocument'])
        ->name('pemesanan.document.decision')->middleware('throttle:customer-forms');
    });

    // Detail routes authorize the owner, admin, or assigned designer in the controller.
    Route::get('/konsultasi/{id}', [KonsultasiController::class, 'show'])->name('konsultasi.show');
    Route::get('/konsultasi/{konsultasi}/lampiran/{index}', [KonsultasiController::class, 'downloadAttachment'])
        ->whereNumber('index')->name('konsultasi.attachment.download');
    Route::get('/pemesanan/{id}', [PemesananController::class, 'show'])->name('pemesanan.show');
    Route::get('/pemesanan/{pemesanan}/bukti-pembayaran', [PemesananController::class, 'downloadPaymentEvidence'])
        ->name('pemesanan.payment-evidence.download');
    Route::get('/pemesanan/{pemesanan}/dokumen/{document}', [PemesananController::class, 'downloadDocument'])
        ->name('pemesanan.document.download');

    Route::put('/designer/proyek/{pemesanan}', [PemesananController::class, 'updateAssignedProject'])
        ->name('designer.proyek.update')->middleware('role:designer');
    Route::post('/designer/konsultasi/{konsultasi}/selesai', [KonsultasiController::class, 'completeByDesigner'])
        ->name('designer.konsultasi.complete')->middleware('role:designer');
    Route::post('/designer/proyek/{pemesanan}/dokumen', [PemesananController::class, 'uploadDocument'])
        ->name('designer.proyek.document.upload')->middleware('role:designer');
    Route::delete('/designer/proyek/{pemesanan}/dokumen/{document}', [PemesananController::class, 'deleteDocument'])
        ->name('designer.proyek.document.delete')->middleware('role:designer');
    Route::post('/designer/proyek/{pemesanan}/dokumen/kirim', [PemesananController::class, 'sendDocuments'])
        ->name('designer.proyek.document.send')->middleware('role:designer');
    Route::post('/designer/proyek/{pemesanan}/survei/selesai', [PemesananController::class, 'completeSurvey'])
        ->name('designer.proyek.survey.complete')->middleware('role:designer');

    // Admin routes
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/categories', [AdminCategoryController::class, 'index'])->name('admin.categories.index');
        Route::post('/admin/categories', [AdminCategoryController::class, 'store'])->name('admin.categories.store');
        Route::put('/admin/categories/{category}', [AdminCategoryController::class, 'update'])->name('admin.categories.update');
        Route::post('/admin/categories/{category}/toggle', [AdminCategoryController::class, 'toggle'])->name('admin.categories.toggle');
        Route::delete('/admin/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('admin.categories.destroy');
        Route::post('/admin/katalog/bulk-action', [KatalogController::class, 'bulkAction'])->name('admin.katalog.bulk-action');
        Route::resource('admin/katalog', KatalogController::class, ['as' => 'admin']);
        Route::resource('admin/faq', AdminFaqController::class, ['as' => 'admin'])->except('show');
        Route::post('/admin/faq/{faq}/toggle', [AdminFaqController::class, 'toggle'])->name('admin.faq.toggle');
        Route::post('/admin/faq/{faq}/move', [AdminFaqController::class, 'move'])->name('admin.faq.move');
        Route::get('/admin/pemesanan', [PemesananController::class, 'index'])->name('admin.pemesanan.index');
        Route::get('/admin/pemesanan/{id}', [PemesananController::class, 'show'])->name('admin.pemesanan.show');
        Route::post('/admin/pemesanan', [PemesananController::class, 'storeAdmin'])->name('admin.pemesanan.store');
        Route::put('/admin/pemesanan/{id}/status', [PemesananController::class, 'updateStatus'])->name('admin.pemesanan.updateStatus');
        Route::post('/admin/pemesanan/{pemesanan}/verifikasi-pembayaran', [PemesananController::class, 'verifyPaymentEvidence'])
            ->name('admin.pemesanan.payment-evidence.verify');
        Route::post('/admin/pemesanan/{pemesanan}/verifikasi-dp', [PemesananController::class, 'verifyDp'])
            ->name('admin.pemesanan.dp.verify');
        Route::post('/admin/pemesanan/{pemesanan}/dokumen', [PemesananController::class, 'uploadDocument'])
            ->name('admin.pemesanan.document.upload');
        Route::delete('/admin/pemesanan/{pemesanan}/dokumen/{document}', [PemesananController::class, 'deleteDocument'])
            ->name('admin.pemesanan.document.delete');
        Route::post('/admin/pemesanan/{pemesanan}/dokumen/kirim', [PemesananController::class, 'sendDocuments'])
            ->name('admin.pemesanan.document.send');
        Route::put('/admin/pemesanan/{pemesanan}/survei', [PemesananController::class, 'scheduleSurvey'])
            ->name('admin.pemesanan.survey.schedule');
        Route::put('/admin/pemesanan/konsultasi/{konsultasi}/desainer', [KonsultasiController::class, 'assignDesigner'])
            ->name('admin.pemesanan.konsultasi.assign');
        Route::post('/admin/pemesanan/konsultasi/{konsultasi}/terima', [KonsultasiController::class, 'accept'])
            ->name('admin.pemesanan.konsultasi.accept');
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
