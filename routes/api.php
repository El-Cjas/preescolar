<?php

use App\Http\Controllers\AlumnoExportController;
use App\Http\Controllers\AlumnoFichaController;
use App\Http\Controllers\Api\AlumnoController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ruta publica no requiere token para que el usuario pueda iniciar sesion
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/alumnos', [AlumnoController::class, 'index']);
    Route::get('/alumnos/exportar/csv', AlumnoExportController::class)->name('api.alumnos.export.csv');
    Route::get('/alumnos/{alumno}/ficha', AlumnoFichaController::class)->name('api.alumnos.ficha');
    Route::get('/alumnos/{id}', [AlumnoController::class, 'show']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
