<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


// Rute ini dapat diakses siapa saja tanpa perlu login.
Route::get('/', [LandingPageController::class, 'index'])->name('landing');

// Rute ini akan mengarahkan ke halaman login jika pengguna belum login.
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dapat diakses oleh role apapun selama sudah login.
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    
    // Route::resource secara otomatis membuat rute untuk index, create, store, show, edit, update, destroy.
    Route::resource('peran', RoleController::class);


    // Hanya dapat diakses oleh pengguna dengan role "Administrator".
    Route::resource('pengguna', UserController::class)->middleware('role:Administrator');

    // Rute untuk proses export data pesanan ke Excel.
    Route::get('pesanan/export', [PesananController::class, 'export'])->name('pesanan.export');
    // Rute untuk proses import data pesanan dari Excel.
    Route::post('pesanan/import', [PesananController::class, 'import'])->name('pesanan.import');
    
    // Rute resource untuk CRUD Pesanan.
    Route::resource('pesanan', PesananController::class);

    // Anda bisa menambahkan resource controller lain di sini, misal:
    // Route::resource('produk', ProdukController::class);
    // Route::resource('pelanggan', PelangganController::class);
});

// Ini akan memuat rute default untuk autentikasi dari Laravel Breeze (login, register, dll.)
require __DIR__.'/auth.php';
