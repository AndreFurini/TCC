<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Setor;
use App\Models\User;

class SetorController extends Controller
{
    public function index(Request $request)
    {
        $empresa_id  = Auth::user()->empresa_id;
        $verInativos = $request->boolean('inativos');

        $setores = Setor::where('empresa_id', $empresa_id)
            ->when(!$verInativos, fn ($q) => $q->where('ativo', true))
            ->with(['responsavel', 'usuarios'])
            ->orderBy('ativo', 'desc')
            ->orderBy('nome')
            ->get();

        return view('setores.index', compact('setores', 'verInativos'));
    }

    public function create()
    {
        $empresa_id = Auth::user()->empresa_id;
        $usuarios = User::where('empresa_id', $empresa_id)
            ->where('ativo', true)
            ->orderBy('name')
            ->get();
        $usuariosSemSetor = User::where('empresa_id', $empresa_id)
            ->where('ativo', true)
            ->whereNull('setor_id')
            ->orderBy('name')
            ->get();
        return view('setores.form', compact('usuarios', 'usuariosSemSetor'));
    }

    public function store(Request $request)
    {
        $empresa_id = Auth::user()->empresa_id;

        $request->validate([
            'nome'        => 'required|string|max:255',
            'usuarios'    => 'nullable|array',
            'usuarios.*'  => 'integer',
        ]);

        $setor = Setor::create([
            'empresa_id'     => $empresa_id,
            'nome'           => $request->nome,
            'responsavel_id' => $request->responsavel_id ?: null,
        ]);

        $this->vincularUsuariosSemSetor($request, $setor, $empresa_id);

        return redirect()->route('setores.index')->with('success', 'Setor criado com sucesso!');
    }

    /**
     * Vincula ao setor os usuários selecionados que ainda não pertencem a nenhum setor.
     * O filtro whereNull('setor_id') garante que usuários já lotados em outro setor
     * não sejam movidos por um id enviado indevidamente no formulário.
     */
    private function vincularUsuariosSemSetor(Request $request, Setor $setor, int $empresa_id): void
    {
        if (!$request->filled('usuarios')) {
            return;
        }

        User::where('empresa_id', $empresa_id)
            ->whereIn('id', $request->usuarios)
            ->whereNull('setor_id')
            ->update(['setor_id' => $setor->id]);
    }

    public function edit($id)
    {
        $empresa_id    = Auth::user()->empresa_id;
        $setor         = Setor::where('empresa_id', $empresa_id)->findOrFail($id);
        $usuarios      = User::where('empresa_id', $empresa_id)
            ->where(function ($q) use ($setor) {
                $q->where('ativo', true)
                  ->orWhere('id', $setor->responsavel_id);
            })
            ->orderBy('name')
            ->get();
        $usuariosDoSetor = User::where('empresa_id', $empresa_id)
                               ->where('setor_id', $setor->id)
                               ->get();
        $usuariosSemSetor = User::where('empresa_id', $empresa_id)
                                ->where('ativo', true)
                                ->whereNull('setor_id')
                                ->orderBy('name')
                                ->get();

        return view('setores.form', compact('setor', 'usuarios', 'usuariosDoSetor', 'usuariosSemSetor'));
    }

    public function update(Request $request, $id)
    {
        $empresa_id = Auth::user()->empresa_id;
        $setor      = Setor::where('empresa_id', $empresa_id)->findOrFail($id);

        $request->validate([
            'nome'        => 'required|string|max:255',
            'usuarios'    => 'nullable|array',
            'usuarios.*'  => 'integer',
        ]);

        $setor->update([
            'nome'           => $request->nome,
            'responsavel_id' => $request->responsavel_id ?: null,
        ]);

        $this->vincularUsuariosSemSetor($request, $setor, $empresa_id);

        return redirect()->route('setores.index')->with('success', 'Setor atualizado com sucesso!');
    }

    // Excluir setor — só é permitido quando NÃO há nenhuma OS vinculada.
    public function destroy(Request $request, $id)
    {
        $empresa_id = Auth::user()->empresa_id;
        $setor      = Setor::where('empresa_id', $empresa_id)->findOrFail($id);

        if ($setor->temOrdensVinculadas()) {
            return redirect()->route('setores.index', $this->filtroInativos($request))
                ->with('error', "Não é possível excluir \"{$setor->nome}\": há ordens de serviço vinculadas. Use \"Inativar\".");
        }

        // Usuários lotados no setor têm setor_id zerado pela FK (nullOnDelete).
        $setor->delete();

        return redirect()->route('setores.index', $this->filtroInativos($request))
            ->with('success', "Setor \"{$setor->nome}\" excluído.");
    }

    // Inativar setor — alternativa à exclusão quando há OS vinculada.
    public function inativar(Request $request, $id)
    {
        $empresa_id = Auth::user()->empresa_id;
        $setor      = Setor::where('empresa_id', $empresa_id)->findOrFail($id);

        $setor->update(['ativo' => false]);

        return redirect()->route('setores.index', $this->filtroInativos($request))
            ->with('success', "Setor \"{$setor->nome}\" inativado.");
    }

    // Reativar setor inativado.
    public function reativar(Request $request, $id)
    {
        $empresa_id = Auth::user()->empresa_id;
        $setor      = Setor::where('empresa_id', $empresa_id)->findOrFail($id);

        $setor->update(['ativo' => true]);

        return redirect()->route('setores.index', $this->filtroInativos($request))
            ->with('success', "Setor \"{$setor->nome}\" reativado.");
    }

    // Mantém o filtro "visualizar inativos" após a ação.
    private function filtroInativos(Request $request): array
    {
        return $request->boolean('inativos') ? ['inativos' => 1] : [];
    }
}
