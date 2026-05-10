<?php

use App\Http\Controllers\DisposisiController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LetterController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// 1. Root: Langsung lempar ke login
Route::get('/', function () {
    return redirect()->route('login');
});

// 2. Auth Routes (Bawaan Laravel UI)
Auth::routes();


// 3. Protected Routes (Harus Login)
Route::middleware(['auth'])->group(function () {
    // Rute Dashboard Utama
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');

    // Pastikan rute 'home' juga mengarah ke dashboard agar tidak error lagi
    Route::get('/home', function() {
        return redirect()->route('dashboard');
    })->name('home');

    // --- Letters Group ---
    Route::prefix('letters')->name('letters.')->group(function () {
        Route::get('/', [LetterController::class, 'index'])->name('index');
        Route::get('/masuk', [LetterController::class, 'masuk'])->name('masuk');
        Route::get('/create', [LetterController::class, 'create'])->name('create');
        Route::post('/store', [LetterController::class, 'store'])->name('store');
        Route::post('/process', [LetterController::class, 'process'])->name('process');
        Route::get('/{id}', [LetterController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [LetterController::class, 'edit'])->name('edit');
        Route::post('/{id}/update', [LetterController::class, 'update'])->name('update');
        Route::post('/{id}/delete', [LetterController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/download-pdf', [LetterController::class, 'downloadPdf'])->name('pdf');
        Route::get('/{id}/print', [LetterController::class, 'printPdf'])->name('print');
    });

    // --- Disposisi Group ---
    Route::prefix('disposisi')->name('disposisi.')->group(function () {
        Route::get('/', [DisposisiController::class, 'index'])->name('index');
        Route::get('/inbox', [DisposisiController::class, 'inbox'])->name('inbox');
        Route::post('/', [DisposisiController::class, 'store'])->name('store');
        Route::get('/{id}', [DisposisiController::class, 'show'])->name('show');
        Route::post('/{id}/process', [DisposisiController::class, 'process'])->name('process');
         // ✅ TAMBAHKAN INI untuk fitur balas
        Route::post('/{id}/reply', [DisposisiController::class, 'reply'])->name('reply');
    });

    // --- Profile Group ---
    Route::middleware('auth')->group(function () {
    // Route Profile lainnya...
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    
    // TAMBAHKAN BARIS INI:
    Route::post('/profile/upload-photo', [ProfileController::class, 'uploadPhoto'])->name('profile.upload-photo');
    
    // Opsional: Jika Anda menggunakan fitur signature juga
    Route::post('/profile/upload-signature', [ProfileController::class, 'uploadSignature'])->name('profile.upload-signature');
    });
    // --- AJAX API Group ---
    Route::prefix('api')->group(function () {
        Route::get('/template/{id}/fields', [LetterController::class, 'getFields'])->name('api.template.fields');
        Route::get('/generate-nomor-surat', [LetterController::class, 'generateNomorSurat']);
    });

});