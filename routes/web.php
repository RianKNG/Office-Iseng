<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CabangController;
use App\Http\Controllers\DisposisiController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\LetterController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// =============================================================================
// 🏠 ROOT REDIRECT
// =============================================================================
Route::get('/', function () {
    return redirect()->route('login');
});
// =============================================================================
// 🔐 AUTH ROUTES (Manual)
// =============================================================================
// use App\Http\Controllers\Auth\LoginController;

// Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
// Route::post('/login', [LoginController::class, 'login']);
// Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
// Route::get('/dashboard', [HomeController::class, 'index'])->middleware(['auth', 'admin']);
// =============================================================================
// 🔐 AUTH ROUTES (Laravel UI)
// =============================================================================
Auth::routes();

// =============================================================================
// 🔒 PROTECTED ROUTES (Wajib Login)
// =============================================================================
Route::middleware(['auth'])->group(function () {
    
    // 📊 Dashboard Utama
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
    Route::get('/home', fn() => redirect()->route('dashboard'))->name('home');

    // =============================================================================
    // 📄 LETTERS MANAGEMENT
    // =============================================================================
    Route::prefix('letters')->name('letters.')->group(function () {
    
        // ✅ SEMUA SURAT (Default)
        Route::get('/', [LetterController::class, 'index'])->name('index');
        
        // ✅ JENIS SURAT (HARD FILTER by jenis)
        Route::get('/masuk', [LetterController::class, 'masuk'])->name('masuk');
        Route::get('/keluar', [LetterController::class, 'keluar'])->name('keluar');  // ✅ TAMBAH INI
        Route::get('/nota', [LetterController::class, 'nota'])->name('nota');        // ✅ TAMBAH INI
        
        // ✅ CREATE & STORE (harus SEBELUM /{id} agar tidak bentrok)
        Route::get('/create', [LetterController::class, 'create'])->name('create');
        Route::post('/store', [LetterController::class, 'store'])->name('store');
        
        // ✅ DETAIL & AKSI (pakai {id} di akhir)
        Route::get('/{id}', [LetterController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [LetterController::class, 'edit'])->name('edit');
        Route::post('/{id}/update', [LetterController::class, 'update'])->name('update');
        Route::delete('/{id}', [LetterController::class, 'destroy'])->name('destroy');
        
        // ✅ PDF & PRINT
        Route::get('/{id}/download-pdf', [LetterController::class, 'downloadPdf'])->name('pdf');
        Route::get('/{id}/print', [LetterController::class, 'printPdf'])->name('print');
        
    });

    // =============================================================================
    // 🔄 DISPOSISI MANAGEMENT
    // =============================================================================
    Route::prefix('disposisi')->name('disposisi.')->group(function () {
        Route::get('/inbox', [DisposisiController::class, 'inbox'])->name('inbox');
        Route::get('/{id}', [DisposisiController::class, 'show'])->name('show');
        Route::post('/{id}/process', [DisposisiController::class, 'process'])->name('process');
        Route::post('/{id}/reply', [DisposisiController::class, 'reply'])->name('reply');
    });

    // =============================================================================
    // 👤 PROFILE MANAGEMENT
    // =============================================================================
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::post('/upload-photo', [ProfileController::class, 'uploadPhoto'])->name('upload-photo');
        Route::post('/upload-signature', [ProfileController::class, 'uploadSignature'])->name('upload-signature');
    });

    // =============================================================================
    // ⚙️ API ROUTES
    // =============================================================================
    Route::prefix('api')->group(function () {
        Route::get('/template/{id}/fields', [LetterController::class, 'getFields'])
            ->name('api.template.fields');
        Route::get('/generate-nomor-surat', [LetterController::class, 'generateNomorSurat'])
            ->name('api.generate-nomor-surat');
    });

    // 🔹 CRUD CABANG
        Route::get('/admin/cabangs', [CabangController::class, 'index'])->name('admin.cabangs');
        Route::get('/admin/cabangs/create', [App\Http\Controllers\CabangController::class, 'create'])->name('admin.cabangs.create');
        Route::post('/admin/cabangs', [App\Http\Controllers\CabangController::class, 'store'])->name('admin.cabangs.store');
        Route::get('/admin/cabangs/{id}/edit', [App\Http\Controllers\CabangController::class, 'edit'])->name('admin.cabangs.edit');
        Route::put('/admin/cabangs/{id}', [App\Http\Controllers\CabangController::class, 'update'])->name('admin.cabangs.update');
        Route::delete('/admin/cabangs/{id}', [App\Http\Controllers\CabangController::class, 'destroy'])->name('admin.cabangs.destroy');

            // 🔹 CRUD JABATAN
        Route::get('/admin/jabatans', [JabatanController::class, 'index'])->name('admin.jabatans');
        Route::get('/admin/jabatans/create', [App\Http\Controllers\JabatanController::class, 'create'])->name('admin.jabatans.create');
        Route::post('/admin/jabatans', [App\Http\Controllers\JabatanController::class, 'store'])->name('admin.jabatans.store');
        Route::get('/admin/jabatans/{id}/edit', [App\Http\Controllers\JabatanController::class, 'edit'])->name('admin.jabatans.edit');
        Route::put('/admin/jabatans/{id}', [App\Http\Controllers\JabatanController::class, 'update'])->name('admin.jabatans.update');
        Route::delete('/admin/jabatans/{id}', [App\Http\Controllers\JabatanController::class, 'destroy'])->name('admin.jabatans.destroy');
    // =============================================================================
    // 👑 ADMIN-ONLY ROUTES (Full CRUD)
    // =============================================================================
    Route::middleware(['auth', 'admin'])
          ->prefix('admin')
          ->name('admin.')
          ->group(function () {
        
        // 📊 Admin Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        
        // 👥 USER MANAGEMENT - FULL CRUD
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [AdminController::class, 'users'])->name('index');
            Route::get('/create', [AdminController::class, 'createUser'])->name('create');
            Route::post('/store', [AdminController::class, 'storeUser'])->name('store');
            Route::get('/{id}/edit', [AdminController::class, 'editUser'])->name('edit');
            Route::post('/{id}/update', [AdminController::class, 'updateUser'])->name('update');
            Route::delete('/{id}/delete', [AdminController::class, 'deleteUser'])->name('delete');
        });
        
        // 📄 LETTER MANAGEMENT - READ ONLY
        Route::get('/letters', [AdminController::class, 'letters'])->name('letters');
        
        // 🔄 DISPOSISI MANAGEMENT - READ ONLY
        Route::get('/disposisi', [AdminController::class, 'disposisi'])->name('disposisi');
        
        // ⚙️ TEMPLATE MANAGEMENT - Opsional
        Route::get('/templates', [AdminController::class, 'templates'])->name('templates');
        
    }); // ✅ Tutup group admin

}); // ✅ Tutup group auth
// ✅ TIDAK ADA KURUNG LAGI DI SINI - FILE BERAKHIR DI SINI