<?php

use App\Http\Controllers\ClientController;
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
    Route::put('/clients',[ClientController::class, 'update'])->name('clients.update');
    Route::delete('/clients/{client}',[ClientController::class, 'destroy'])->name('clients.destroy');

});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
