<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Setor;
use App\Models\User;

class SetorController extends Controller
{
    public function index()
    {
        $empresa_id = Auth::user()->empresa_id;
        $setores = Setor::where('empresa_id', $empresa_id)->get();
        return view('setores.index', compact('setores'));
    }

    public function create()
    {
        $empresa_id = Auth::user()->empresa_id;
        $usuarios = User::where('empresa_id', $empresa_id)->orderBy('name')->get();
        return view('setores.form', compact('usuarios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
        ]);

        Setor::create([
            'empresa_id'     => Auth::user()->empresa_id,
            'nome'           => $request->nome,
            'responsavel_id' => $request->responsavel_id ?: null,
        ]);

        return redirect()->route('setores.index')->with('success', 'Setor criado com sucesso!');
    }

    public function edit($id)
    {
        $empresa_id    = Auth::user()->empresa_id;
        $setor         = Setor::where('empresa_id', $empresa_id)->findOrFail($id);
        $usuarios      = User::where('empresa_id', $empresa_id)->orderBy('name')->get();
        $usuariosDoSetor = User::where('empresa_id', $empresa_id)
                               ->where('setor_id', $setor->id)
                               ->get();

        return view('setores.form', compact('setor', 'usuarios', 'usuariosDoSetor'));
    }

    public function update(Request $request, $id)
    {
        $empresa_id = Auth::user()->empresa_id;
        $setor      = Setor::where('empresa_id', $empresa_id)->findOrFail($id);

        $request->validate([
            'nome' => 'required|string|max:255',
        ]);

        $setor->update([
            'nome'           => $request->nome,
            'responsavel_id' => $request->responsavel_id ?: null,
        ]);

        return redirect()->route('setores.index')->with('success', 'Setor atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $empresa_id = Auth::user()->empresa_id;
        $setor      = Setor::where('empresa_id', $empresa_id)->findOrFail($id);
        $setor->delete();

        return redirect()->route('setores.index')->with('success', 'Setor removido com sucesso!');
    }
}
