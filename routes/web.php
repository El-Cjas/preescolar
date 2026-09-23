<?php

use App\Http\Controllers\AlumnoExportController;
use App\Http\Controllers\AlumnoFichaController;
use App\Livewire\Alumnos\Index as AlumnosIndex;
use App\Livewire\Dashboard;
use App\Livewire\Representantes\Index as RepresentantesIndex;
use App\Livewire\Trabajadores\Index as TrabajadoresIndex;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');
    Route::get('alumnos', AlumnosIndex::class)->name('alumnos.index');
    Route::get('alumnos/exportar/csv', AlumnoExportController::class)->name('alumnos.export.csv');
    Route::get('alumnos/{alumno}/ficha', AlumnoFichaController::class)->name('alumnos.ficha');
    Route::get('representantes', RepresentantesIndex::class)->name('representantes.index');
    Route::get('trabajadores', TrabajadoresIndex::class)->name('trabajadores.index');
});

require __DIR__.'/settings.php';
