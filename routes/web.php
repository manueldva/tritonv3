<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\EmpresaController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function() {
    Route::get('/clients',[ClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/create',[ClientController::class, 'create'])->name('clients.create');
    Route::post('/clients',[ClientController::class, 'store'])->name('clients.store');
    //Route::get('/clients',[ClientController::class, 'show'])->name('clients.show');
    Route::get('/clients/{client}/edit',[ClientController::class, 'edit'])->name('clients.edit');
    Route::put('/clients/{client}',[ClientController::class, 'update'])->name('clients.update');
    Route::delete('/clients/{client}',[ClientController::class, 'destroy'])->name('clients.destroy');


    // ¡Nuevas Rutas de Empresas!
    Route::get('/empresas',[EmpresaController::class, 'index'])->name('empresas.index');
    Route::get('/empresas/create',[EmpresaController::class, 'create'])->name('empresas.create');
    Route::post('/empresas',[EmpresaController::class, 'store'])->name('empresas.store');
    Route::get('/empresas/{empresa}/edit',[EmpresaController::class, 'edit'])->name('empresas.edit');
    Route::put('/empresas/{empresa}',[EmpresaController::class, 'update'])->name('empresas.update');
    Route::delete('/empresas/{empresa}',[EmpresaController::class, 'destroy'])->name('empresas.destroy');


});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
