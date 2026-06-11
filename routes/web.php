<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SetorController;
use App\Http\Controllers\OrdemServicoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\DashboardController;

// -------------------------------------------------------
// ROTAS PÚBLICAS
// -------------------------------------------------------
Route::get('/',         [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',   [AuthController::class, 'login'])->name('auth.login');
Route::post('/logout',  [AuthController::class, 'logout'])->name('auth.logout');

Route::get('/cadastro',  [AuthController::class, 'showCadastro'])->name('cadastro.empresa');
Route::post('/cadastro', [AuthController::class, 'storeCadastro'])->name('cadastro.empresa.store');

// -------------------------------------------------------
// ROTAS PROTEGIDAS (requer login)
// -------------------------------------------------------
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Setores (Admin)
    Route::resource('setores', SetorController::class);

    // Usuários (Admin)
    Route::resource('usuarios', UsuarioController::class);

    // Ordens de Serviço
    Route::resource('ordens', OrdemServicoController::class);

});
