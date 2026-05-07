<?php

use App\Http\Controllers\DisposisiController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LetterController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');


Route::middleware(['auth'])->group(function () {
    
    // Letters
    Route::get('/letters/masuk', [LetterController::class, 'masuk'])->name('masuk');
    Route::get('/letters/create', [LetterController::class, 'create'])->name('letters.create');
    Route::post('/letters', [LetterController::class, 'store'])->name('letters.store');
    Route::get('/letters', [LetterController::class, 'index'])->name('letters.index');
    Route::get('/letters/{id}', [LetterController::class, 'show'])->name('letters.show');
     Route::get('/letters/{id}/download-pdf', [LetterController::class, 'downloadPdf'])->name('letters.pdf');
     Route::get('/letters/{id}/print', [LetterController::class, 'printPdf'])->name('letters.print');
    
     // Tambahkan route edit dan update ini:
    Route::get('letters/{id}/edit', [LetterController::class, 'edit'])->name('letters.edit');
    Route::post('letters/{id}/update', [LetterController::class, 'update'])->name('update');
    Route::post('letters/{id}/delete', [LetterController::class, 'destroy'])->name('destroy');
    
    
    // AJAX API for Dynamic Form
    Route::get('/api/template/{id}/fields', [LetterController::class, 'getFields'])->name('api.template.fields');
    Route::get('/api/generate-nomor-surat', [LetterController::class, 'generateNomorSurat']);

    // Disposisi
    Route::get('/disposisi/inbox', [DisposisiController::class, 'inbox'])->name('disposisi.inbox');
    // Tambahkan ini agar URL bisa dibuka langsung di browser
    Route::get('/disposisi', [DisposisiController::class, 'index'])->name('disposisi.inbox');
    Route::post('/disposisi', [DisposisiController::class, 'store'])->name('disposisi.store');
    Route::post('/disposisi/{id}/process', [DisposisiController::class, 'process'])->name('disposisi.process');
     // GET: Tampilkan detail disposisi
    Route::get('/disposisi/{id}', [DisposisiController::class, 'show'])
         ->name('disposisi.show');

         //profile
         // Profile routes
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/upload-signature', [ProfileController::class, 'uploadSignature'])->name('profile.upload-signature');
    Route::delete('/profile/remove-signature', [ProfileController::class, 'removeSignature'])->name('profile.remove-signature');
});
         
    
});

Route::get('/', function () {
    return redirect('/login');
});