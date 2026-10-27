<?php

use App\Http\Controllers\Api\CitaController;
use Illuminate\Support\Facades\Route;

// Consulta de citas. Sin ?estado devuelve solo las confirmadas.
Route::get('/citas', [CitaController::class, 'index'])->name('api.citas.index');

// Agendar una cita requiere un token válido de Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/citas', [CitaController::class, 'store'])->name('api.citas.store');
});