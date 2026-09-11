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

    // Setores (Admin) — excluir só quando não há OS vinculada; senão, inativar/reativar
    Route::resource('setores', SetorController::class);
    Route::patch('setores/{setor}/inativar', [SetorController::class, 'inativar'])->name('setores.inativar');
    Route::patch('setores/{setor}/reativar', [SetorController::class, 'reativar'])->name('setores.reativar');

    // Usuários (Admin) — excluir só quando não há OS vinculada; senão, inativar/reativar
    Route::resource('usuarios', UsuarioController::class);
    Route::patch('usuarios/{usuario}/inativar', [UsuarioController::class, 'inativar'])->name('usuarios.inativar');
    Route::patch('usuarios/{usuario}/reativar', [UsuarioController::class, 'reativar'])->name('usuarios.reativar');

    // Ordens de Serviço
    Route::resource('ordens', OrdemServicoController::class);
    Route::patch('ordens/{orden}/assumir', [OrdemServicoController::class, 'assumir'])->name('ordens.assumir');
    Route::patch('ordens/{orden}/liberar', [OrdemServicoController::class, 'liberar'])->name('ordens.liberar');

});
