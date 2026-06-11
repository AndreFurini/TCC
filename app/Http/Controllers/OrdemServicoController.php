<?php

namespace App\Http\Controllers;

use App\Models\OrdemServico;
use App\Models\Setor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrdemServicoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // -------------------------------------------------------
    // INDEX — listagem de OS (Coordenador)
    // -------------------------------------------------------
    public function index(Request $request)
    {
        $user       = Auth::user();
        $empresa_id = $user->empresa_id;

        // Apenas coordenador acessa a listagem completa
        if (!$user->isCoordenador()) {
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
    // CREATE — formulário de nova OS
    // -------------------------------------------------------
    public function create()
    {
        $user = Auth::user();

        // Coordenador e Colaborador podem criar
        if (!$user->isCoordenador() && !$user->isColaborador()) {
            abort(403);
        }

        $empresa_id = $user->empresa_id;
        $setores    = Setor::where('empresa_id', $empresa_id)->orderBy('nome')->get();

        // Executores disponíveis (só coordenador verá o campo de executante)
        $executores = collect();
        if ($user->isCoordenador()) {
            $executores = User::where('empresa_id', $empresa_id)
                ->where('role', 'executor')
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

        if (!$user->isCoordenador() && !$user->isColaborador()) {
            abort(403);
        }

        $rules = [
            'titulo'    => 'required|string|max:255',
            'setor_id'  => 'required|exists:setores,id',
            'descricao' => 'required|string',
        ];

        // Coordenador pode definir urgência e executor
        if ($user->isCoordenador()) {
            $rules['urgencia']    = 'required|in:BAIXA,MEDIA,ALTA,URGENTE';
            $rules['executor_id'] = 'nullable|exists:users,id';
        }

        $request->validate($rules, [
            'titulo.required'    => 'O título é obrigatório.',
            'setor_id.required'  => 'Selecione um setor.',
            'descricao.required' => 'A descrição é obrigatória.',
            'urgencia.required'  => 'Selecione o grau de urgência.',
        ]);

        OrdemServico::create([
            'empresa_id'   => $user->empresa_id,
            'titulo'       => $request->titulo,
            'descricao'    => $request->descricao,
            'status'       => 'ABERTA',
            'urgencia'     => $user->isCoordenador() ? $request->urgencia : 'BAIXA',
            'setor_id'     => $request->setor_id,
            'executor_id'  => $user->isCoordenador() ? $request->executor_id : null,
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

        // Executor só vê OS atribuídas a ele ou do seu setor
        if ($user->isExecutor()) {
            if ($ordem->executor_id !== $user->id && $ordem->setor_id !== $user->setor_id) {
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

        // --- EXECUTOR: só pode adicionar devolutiva e finalizar ---
        if ($user->isExecutor()) {
            if ($ordem->executor_id !== $user->id && $ordem->setor_id !== $user->setor_id) {
                abort(403);
            }

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

        // --- COLABORADOR: pode editar título e descrição se for o criador ---
        if ($user->isColaborador()) {
            if ($ordem->criado_por !== $user->id) {
                abort(403);
            }

            $request->validate([
                'titulo'    => 'required|string|max:255',
                'descricao' => 'required|string',
            ]);

            $ordem->update([
                'titulo'         => $request->titulo,
                'descricao'      => $request->descricao,
                'atualizado_por' => $user->id,
            ]);

            return redirect()->route('dashboard')->with('success', 'OS atualizada com sucesso!');
        }

        abort(403);
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
}
