<?php

namespace App\Http\Controllers;

use App\Models\OrdemServico;
use App\Models\Setor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrdemServicoController extends Controller
{
    // -------------------------------------------------------
    // INDEX — listagem de OS (Coordenador e Admin)
    // -------------------------------------------------------
    public function index(Request $request)
    {
        $user       = Auth::user();
        $empresa_id = $user->empresa_id;

        // Coordenador e Admin veem a listagem completa da empresa.
        // Admin apenas visualiza (histórico + filtros); não cria/edita OS.
        if (!$user->isCoordenador() && !$user->isAdmin()) {
            return redirect()->route('dashboard');
        }

        $query = OrdemServico::where('empresa_id', $empresa_id)->with(['setor', 'executor', 'criadoPor']);

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('urgencia')) {
            $query->where('urgencia', $request->urgencia);
        }
        if ($request->filled('setor_id')) {
            $query->where('setor_id', $request->setor_id);
        }

        $ordens  = $query->latest()->get();
        $setores = Setor::where('empresa_id', $empresa_id)->get();

        return view('ordens.index', compact('ordens', 'setores'));
    }

    // -------------------------------------------------------
    // HISTÓRICO — listagem completa de OS do Executor/Colaborador (tudo
    // que cada um vê: executor = próprio setor + o que ele mesmo criou;
    // colaborador = só o que ele criou), sem restrição de status, com
    // filtros de pesquisa. Substitui o acesso a /ordens pra esses
    // papéis (não gerenciam a empresa toda como coordenador/admin).
    // -------------------------------------------------------
    public function historico(Request $request)
    {
        $user = Auth::user();

        if (!$user->isExecutor() && !$user->isColaborador()) {
            abort(403);
        }

        $empresa_id = $user->empresa_id;

        $query = OrdemServico::where('empresa_id', $empresa_id);

        if ($user->isExecutor()) {
            $query->where(function ($q) use ($user) {
                $q->where('setor_id', $user->setor_id)
                  ->orWhere('criado_por', $user->id);
            });
        } else {
            $query->where('criado_por', $user->id);
        }

        $query->with(['setor', 'executor']);

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('urgencia')) {
            $query->where('urgencia', $request->urgencia);
        }
        if ($request->filled('setor_id')) {
            $query->where('setor_id', $request->setor_id);
        }
        if ($request->filled('busca')) {
            $query->where('titulo', 'like', '%'.$request->busca.'%');
        }

        $ordens  = $query->latest()->get();
        $setores = Setor::where('empresa_id', $empresa_id)->orderBy('nome')->get();

        return view('ordens.historico', compact('ordens', 'setores'));
    }

    // -------------------------------------------------------
    // CREATE — formulário de nova OS
    // -------------------------------------------------------
    public function create()
    {
        $user = Auth::user();

        // Admin, Coordenador, Executor e Colaborador podem criar
        if (!$user->isAdmin() && !$user->isCoordenador() && !$user->isExecutor() && !$user->isColaborador()) {
            abort(403);
        }

        $empresa_id = $user->empresa_id;
        $setores    = Setor::where('empresa_id', $empresa_id)->orderBy('nome')->get();

        // Executores disponíveis (Admin e Coordenador veem o campo de executante)
        $executores = collect();
        if ($user->isAdmin() || $user->isCoordenador()) {
            $executores = User::where('empresa_id', $empresa_id)
                ->where('role', 'executor')
                ->where('ativo', true)
                ->orderBy('name')
                ->get();
        }

        return view('ordens.create', compact('setores', 'executores'));
    }

    // -------------------------------------------------------
    // STORE — salvar nova OS
    // -------------------------------------------------------
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->isAdmin() && !$user->isCoordenador() && !$user->isExecutor() && !$user->isColaborador()) {
            abort(403);
        }

        // Admin e Coordenador definem urgência e executor; Executor e Colaborador não.
        $comOpcoes = $user->isAdmin() || $user->isCoordenador();

        $rules = [
            'titulo'       => 'required|string|max:255',
            'setor_id'     => 'required|exists:setores,id',
            'descricao'    => 'required|string',
            'data_entrega' => 'nullable|date',
        ];

        if ($comOpcoes) {
            $rules['urgencia']    = 'required|in:BAIXA,MEDIA,ALTA,URGENTE';
            $rules['executor_id'] = 'nullable|exists:users,id';
        }

        $request->validate($rules, [
            'titulo.required'    => 'O título é obrigatório.',
            'setor_id.required'  => 'Selecione um setor.',
            'descricao.required' => 'A descrição é obrigatória.',
            'urgencia.required'  => 'Selecione o grau de urgência.',
            'data_entrega.date'  => 'Data de entrega inválida.',
        ]);

        OrdemServico::create([
            'empresa_id'   => $user->empresa_id,
            'titulo'       => $request->titulo,
            'descricao'    => $request->descricao,
            'status'       => 'ABERTA',
            'urgencia'     => $comOpcoes ? $request->urgencia : 'BAIXA',
            'setor_id'     => $request->setor_id,
            'executor_id'  => $comOpcoes ? $request->executor_id : null,
            'data_entrega' => $request->data_entrega ?: null,
            'criado_por'   => $user->id,
            'atualizado_por' => $user->id,
        ]);

        return redirect()->route('dashboard')->with('success', 'Ordem de serviço criada com sucesso!');
    }

    // -------------------------------------------------------
    // SHOW — detalhe da OS
    // -------------------------------------------------------
    public function show($id)
    {
        $user   = Auth::user();
        $ordem  = OrdemServico::where('empresa_id', $user->empresa_id)
            ->with(['setor', 'executor', 'criadoPor', 'atualizadoPor'])
            ->findOrFail($id);

        // Executor só vê OS atribuídas a ele, do seu setor, ou que ele mesmo criou
        if ($user->isExecutor()) {
            if ($ordem->executor_id !== $user->id
                && $ordem->setor_id !== $user->setor_id
                && $ordem->criado_por !== $user->id) {
                abort(403);
            }
        }

        // Colaborador só vê OS que criou
        if ($user->isColaborador()) {
            if ($ordem->criado_por !== $user->id) {
                abort(403);
            }
        }

        $executores = collect();
        if ($user->isCoordenador()) {
            $executores = User::where('empresa_id', $user->empresa_id)
                ->where('role', 'executor')
                ->where(function ($q) use ($ordem) {
                    // executores ativos + o executor já atribuído (mesmo que inativo)
                    $q->where('ativo', true)
                      ->orWhere('id', $ordem->executor_id);
                })
                ->orderBy('name')
                ->get();
        }

        return view('ordens.show', compact('ordem', 'executores'));
    }

    // -------------------------------------------------------
    // UPDATE — atualizar OS
    // -------------------------------------------------------
    public function update(Request $request, $id)
    {
        $user  = Auth::user();
        $ordem = OrdemServico::where('empresa_id', $user->empresa_id)->findOrFail($id);

        // --- COORDENADOR: pode editar tudo ---
        if ($user->isCoordenador()) {
            $request->validate([
                'executor_id' => 'nullable|exists:users,id',
                'status'      => 'required|in:ABERTA,EM_ANDAMENTO,FINALIZADA,CANCELADA',
                'devolutiva'  => 'nullable|string',
            ]);

            $ordem->update([
                'executor_id'    => $request->executor_id,
                'status'         => $request->status,
                'devolutiva'     => $request->devolutiva,
                'atualizado_por' => $user->id,
            ]);

            return redirect()->route('ordens.index')->with('success', 'OS atualizada com sucesso!');
        }

        // --- EXECUTOR: se for o atribuído (ou do mesmo setor), age como
        // executor (devolutiva/finalizar); se só criou a OS — ex.: pediu algo
        // pra outro setor — age como colaborador (edita título/descrição) ---
        if ($user->isExecutor()) {
            $souExecutorAtribuido = $ordem->executor_id === $user->id || $ordem->setor_id === $user->setor_id;
            $souCriador           = $ordem->criado_por === $user->id;

            if (!$souExecutorAtribuido && !$souCriador) {
                abort(403);
            }

            if ($souExecutorAtribuido) {
                $request->validate(['devolutiva' => 'nullable|string']);

                $dados = [
                    'devolutiva'     => $request->devolutiva,
                    'atualizado_por' => $user->id,
                ];

                if ($request->has('finalizar')) {
                    $dados['status'] = 'FINALIZADA';
                }

                $ordem->update($dados);

                return redirect()->route('dashboard')->with('success', 'OS atualizada com sucesso!');
            }

            return $this->salvarEdicaoDoCriador($request, $ordem, $user);
        }

        // --- COLABORADOR: pode editar título e descrição se for o criador ---
        if ($user->isColaborador()) {
            if ($ordem->criado_por !== $user->id) {
                abort(403);
            }

            return $this->salvarEdicaoDoCriador($request, $ordem, $user);
        }

        abort(403);
    }

    /**
     * Edição de título/descrição por quem CRIOU a OS mas não é o
     * executor responsável por ela (colaborador, ou executor que abriu
     * um chamado fora do seu escopo de execução).
     */
    private function salvarEdicaoDoCriador(Request $request, OrdemServico $ordem, User $user)
    {
        $request->validate([
            'titulo'    => 'required|string|max:255',
            'descricao' => 'required|string',
        ]);

        $ordem->fill([
            'titulo'         => $request->titulo,
            'descricao'      => $request->descricao,
            'atualizado_por' => $user->id,
        ]);

        // Só marca a data de alteração quando o criador realmente
        // mudou o conteúdo (título ou descrição).
        if ($ordem->isDirty(['titulo', 'descricao'])) {
            $ordem->alterada_pelo_criador_em = now();
        }

        $ordem->save();

        return redirect()->route('dashboard')->with('success', 'OS atualizada com sucesso!');
    }

    // -------------------------------------------------------
    // DESTROY — excluir OS (só quem criou)
    // -------------------------------------------------------
    public function destroy($id)
    {
        $user  = Auth::user();
        $ordem = OrdemServico::where('empresa_id', $user->empresa_id)->findOrFail($id);

        if ($ordem->criado_por !== $user->id) {
            abort(403, 'Apenas quem criou a OS pode excluí-la.');
        }

        $ordem->delete();

        return redirect()->route('dashboard')->with('success', 'OS excluída com sucesso!');
    }

    // -------------------------------------------------------
    // ASSUMIR — executor se autoatribui a uma OS do próprio setor
    // que ainda não tem executante definido. O coordenador continua
    // podendo reatribuir a qualquer momento pelo detalhe da OS.
    // -------------------------------------------------------
    public function assumir($id)
    {
        $user  = Auth::user();
        $ordem = OrdemServico::where('empresa_id', $user->empresa_id)->findOrFail($id);

        if (!$user->isExecutor()) {
            abort(403);
        }

        if ($ordem->setor_id !== $user->setor_id) {
            abort(403, 'Você só pode assumir ordens do seu próprio setor.');
        }

        if ($ordem->executor_id !== null) {
            return redirect()->route('ordens.show', $ordem->id)
                ->with('error', 'Esta OS já tem um executante definido.');
        }

        if (in_array($ordem->status, ['FINALIZADA', 'CANCELADA'], true)) {
            return redirect()->route('ordens.show', $ordem->id)
                ->with('error', 'Esta OS já foi encerrada.');
        }

        $ordem->update([
            'executor_id'    => $user->id,
            'status'         => 'EM_ANDAMENTO',
            'atualizado_por' => $user->id,
        ]);

        return redirect()->route('ordens.show', $ordem->id)->with('success', 'Você assumiu esta OS.');
    }

    // -------------------------------------------------------
    // LIBERAR — executor se desvincula de uma OS que está com ele,
    // voltando o status pra Aberta (pra outro pegar ou o coordenador
    // reatribuir).
    // -------------------------------------------------------
    public function liberar($id)
    {
        $user  = Auth::user();
        $ordem = OrdemServico::where('empresa_id', $user->empresa_id)->findOrFail($id);

        if (!$user->isExecutor() || $ordem->executor_id !== $user->id) {
            abort(403);
        }

        if (in_array($ordem->status, ['FINALIZADA', 'CANCELADA'], true)) {
            return redirect()->route('ordens.show', $ordem->id)
                ->with('error', 'Esta OS já foi encerrada, não é possível se desvincular.');
        }

        $dados = [
            'executor_id'    => null,
            'atualizado_por' => $user->id,
        ];

        if ($ordem->status === 'EM_ANDAMENTO') {
            $dados['status'] = 'ABERTA';
        }

        $ordem->update($dados);

        return redirect()->route('ordens.show', $ordem->id)->with('success', 'Você se desvinculou desta OS.');
    }
}
