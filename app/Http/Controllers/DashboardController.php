<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\OrdemServico;
use App\Models\Setor;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user       = Auth::user();
        $empresa_id = $user->empresa_id;

        // Query base da empresa
        $query = OrdemServico::where('empresa_id', $empresa_id);

        // Admin: pode filtrar por setor
        $setores          = collect();
        $setor_selecionado = null;

        if ($user->isAdmin()) {
            $setores = Setor::where('empresa_id', $empresa_id)->get();
            if ($request->filled('setor_id')) {
                $query->where('setor_id', $request->setor_id);
                $setor_selecionado = $setores->find($request->setor_id);
            }
        }

        // Executor: só vê as suas e do setor
        if ($user->isExecutor()) {
            $query->where(function ($q) use ($user) {
                $q->where('executor_id', $user->id)
                  ->orWhere('setor_id', $user->setor_id);
            });
        }

        // Colaborador: só vê as que criou
        if ($user->isColaborador()) {
            $query->where('criado_por', $user->id);
        }

        $abertas      = (clone $query)->where('status', 'ABERTA')->count();
        $em_andamento = (clone $query)->where('status', 'EM_ANDAMENTO')->count();
        $finalizadas  = (clone $query)->where('status', 'FINALIZADA')->count();

        // OS urgente (Admin e Coordenador)
        $urgente = null;
        if ($user->isAdmin() || $user->isCoordenador()) {
            $urgente = OrdemServico::where('empresa_id', $empresa_id)
                ->where('urgencia', 'URGENTE')
                ->where('status', '!=', 'FINALIZADA')
                ->latest()
                ->first();
        }

        // Lista de OS (Executor e Colaborador)
        $ordens = null;
        if ($user->isExecutor() || $user->isColaborador()) {
            $ordens = (clone $query)->with(['setor'])->latest()->get();
        }

        return view('dashboard', compact(
            'user', 'abertas', 'em_andamento', 'finalizadas',
            'urgente', 'ordens', 'setores', 'setor_selecionado'
        ));
    }
}
