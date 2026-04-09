<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\SetorController;
use App\Http\Controllers\OrdemServicoController;

// Página inicial (dashboard)
Route::get('/', [OrdemServicoController::class, 'dashboard']);

// SETORES
Route::resource('setores', SetorController::class);

// ORDENS DE SERVIÇO
Route::resource('ordens', OrdemServicoController::class);