<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\OrdemServico;
use App\Models\Setor;
use App\Models\User;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user       = Auth::user();
        $empresa_id = $user->empresa_id;

        // Query base da empresa
        $query = OrdemServico::where('empresa_id', $empresa_id);

        // Admin e Coordenador: podem filtrar por setor (enxergam a empresa toda)
        $setores          = collect();
        $setor_selecionado = null;

        if ($user->isAdmin() || $user->isCoordenador()) {
            $setores = Setor::where('empresa_id', $empresa_id)->orderBy('nome')->get();
            if ($request->filled('setor_id')) {
                $query->where('setor_id', $request->setor_id);
                $setor_selecionado = $setores->find($request->setor_id);
            }
        }

        // Executor: vê as OS do próprio setor + as que ele mesmo criou
        // (agora ele também pode abrir OS, inclusive pra outro setor)
        if ($user->isExecutor()) {
            $query->where(function ($q) use ($user) {
                $q->where('setor_id', $user->setor_id)
                  ->orWhere('criado_por', $user->id);
            });
        }

        // Colaborador: só vê as que criou
        if ($user->isColaborador()) {
            $query->where('criado_por', $user->id);
        }

        $abertas      = (clone $query)->where('status', 'ABERTA')->count();
        $em_andamento = (clone $query)->where('status', 'EM_ANDAMENTO')->count();
        $finalizadas  = (clone $query)->where('status', 'FINALIZADA')->count();

        // OS urgente (Admin e Coordenador) — respeita o setor selecionado pelo admin
        $urgente = null;
        if ($user->isAdmin() || $user->isCoordenador()) {
            $urgente = (clone $query)
                ->where('urgencia', 'URGENTE')
                ->where('status', '!=', 'FINALIZADA')
                ->latest()
                ->first();
        }

        // Lista de OS (Executor e Colaborador)
        $ordens = null;
        if ($user->isExecutor() || $user->isColaborador()) {
            $ordens = (clone $query)->with(['setor', 'executor'])->latest()->get();
        }

        // ---------------------------------------------------------------
        // Painel extra do Admin e do Coordenador: atrasadas, sem executor,
        // distribuição por urgência, busca. Ranking de setores e setores
        // sem responsável são só do Admin (gestão da empresa).
        // ---------------------------------------------------------------
        $atrasadas             = 0;
        $semExecutor           = 0;
        $semExecutorMeuSetor   = 0;
        $distribuicaoUrgencia  = collect();
        $rankingSetores        = collect();
        $setoresSemResponsavel = collect();
        $ordensRecentes        = collect();

        if ($user->isAdmin() || $user->isCoordenador()) {
            $emAberto = (clone $query)->whereNotIn('status', ['FINALIZADA', 'CANCELADA']);

            $atrasadas = (clone $emAberto)
                ->whereNotNull('data_entrega')
                ->where('data_entrega', '<', now()->startOfDay())
                ->count();

            $semExecutor = (clone $emAberto)->whereNull('executor_id')->count();

            $distribuicaoUrgencia = (clone $emAberto)
                ->selectRaw('urgencia, COUNT(*) as total')
                ->groupBy('urgencia')
                ->pluck('total', 'urgencia');

            // Filtros e pesquisa (Situação, Prioridade, título) — só afeta esta lista.
            $ordensRecentes = (clone $query)
                ->with(['setor', 'executor'])
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
                ->when($request->filled('urgencia'), fn ($q) => $q->where('urgencia', $request->urgencia))
                ->when($request->filled('busca'), fn ($q) => $q->where('titulo', 'like', '%'.$request->busca.'%'))
                ->latest('updated_at')
                ->take(15)
                ->get();
        }

        // Painel extra do Executor: atrasadas (no que ele vê: setor + o que
        // ele mesmo pediu) e a fila do PRÓPRIO setor ainda sem executor
        // definido — diferente do "sem executor" do admin/coordenador, que
        // é da empresa toda (ou do setor que eles selecionaram no filtro).
        if ($user->isExecutor()) {
            $emAbertoExecutor = (clone $query)->whereNotIn('status', ['FINALIZADA', 'CANCELADA']);

            $atrasadas = (clone $emAbertoExecutor)
                ->whereNotNull('data_entrega')
                ->where('data_entrega', '<', now()->startOfDay())
                ->count();

            $semExecutorMeuSetor = OrdemServico::where('empresa_id', $empresa_id)
                ->where('setor_id', $user->setor_id)
                ->whereNotIn('status', ['FINALIZADA', 'CANCELADA'])
                ->whereNull('executor_id')
                ->count();
        }

        if ($user->isAdmin()) {
            $rankingSetores = Setor::where('empresa_id', $empresa_id)
                ->where('ativo', true)
                ->withCount(['ordensServico as os_abertas_count' => function ($q) {
                    $q->whereNotIn('status', ['FINALIZADA', 'CANCELADA']);
                }])
                ->orderByDesc('os_abertas_count')
                ->get();

            $setoresSemResponsavel = Setor::where('empresa_id', $empresa_id)
                ->where('ativo', true)
                ->whereNull('responsavel_id')
                ->orderBy('nome')
                ->get();
        }

        return view('dashboard', compact(
            'user', 'abertas', 'em_andamento', 'finalizadas',
            'urgente', 'ordens', 'setores', 'setor_selecionado',
            'atrasadas', 'semExecutor', 'semExecutorMeuSetor', 'distribuicaoUrgencia',
            'rankingSetores', 'setoresSemResponsavel', 'ordensRecentes'
        ));
    }
}
