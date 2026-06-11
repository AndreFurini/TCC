<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setor;

class SetorController extends Controller
{
    public function index()
    {
        $setores = Setor::all();
        return view('setores.index', compact('setores'));
    }

    public function create()
    {
        return view('setores.form');
    }

    public function store(Request $request)
    {
        Setor::create([
            'nome' => $request->nome
        ]);

        return redirect('/setores');
    }

    public function edit($id)
    {
        $setor = Setor::findOrFail($id);
        return view('setores.form', compact('setor'));
    }

    public function update(Request $request, $id)
    {
        $setor = Setor::findOrFail($id);
        $setor->update([
            'nome' => $request->nome
        ]);

        return redirect('/setores');
    }

    public function destroy($id)
    {
        $setor = Setor::findOrFail($id);
        $setor->delete();

        return redirect('/setores');
    }
}
