<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrdemServico;
use App\Models\Setor;

class OrdemServicoController extends Controller
{
    public function dashboard()
    {
        $ordens = OrdemServico::with('setor')->get();
        return view('dashboard', compact('ordens'));
    }

    public function create()
    {
        $setores = Setor::all();
        return view('ordens.create', compact('setores'));
    }

    public function store(Request $request)
    {
        OrdemServico::create([
            'titulo' => $request->titulo,
            'descricao' => $request->descricao,
            'status' => 'ABERTA',
            'setor_id' => $request->setor_id
        ]);

        return redirect('/');
    }

    public function edit($id)
    {
        $ordem = OrdemServico::findOrFail($id);
        $setores = Setor::all();

        return view('ordens.edit', compact('ordem', 'setores'));
    }

    public function update(Request $request, $id)
    {
        $ordem = OrdemServico::findOrFail($id);

        $ordem->update([
            'titulo' => $request->titulo,
            'descricao' => $request->descricao,
            'status' => $request->status,
            'setor_id' => $request->setor_id
        ]);

        return redirect('/');
    }

    public function destroy($id)
    {
        $ordem = OrdemServico::findOrFail($id);
        $ordem->delete();

        return redirect('/');
    }
}