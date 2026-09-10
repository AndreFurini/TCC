<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Setor;

class UsuarioController extends Controller
{
    // Listagem
    public function index()
    {
        $empresa_id = Auth::user()->empresa_id;

        $usuarios = User::where('empresa_id', $empresa_id)
            ->where('id', '!=', Auth::id()) // não lista o próprio admin
            ->with('setor')
            ->orderBy('name')
            ->get();

        return view('usuarios.index', compact('usuarios'));
    }

    // Formulário de criação
    public function create()
    {
        $setores = Setor::where('empresa_id', Auth::user()->empresa_id)->get();
        // 'admin' fica de fora: só é criado no cadastro da empresa
        $roles   = collect(User::ROLES)->except('admin')->all();

        return view('usuarios.create', compact('setores', 'roles'));
    }

    // Salvar novo usuário
    public function store(Request $request)
    {
        $empresa_id = Auth::user()->empresa_id;

        $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'email'    => 'required|email|unique:users,email',
            'role'     => 'required|in:coordenador,executor,colaborador',
            'setor_id' => 'required|exists:setores,id',
            'password' => [
                'required',
                'confirmed',
                'min:10',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[@#$%&*!]/',
                'not_regex:/\s/',
            ],
        ], [
            'name.required'     => 'O nome completo é obrigatório.',
            'username.required' => 'O nome de usuário é obrigatório.',
            'username.unique'   => 'Este nome de usuário já está em uso.',
            'email.unique'      => 'Este e-mail já está cadastrado.',
            'role.required'     => 'Selecione uma função.',
            'setor_id.required' => 'Selecione um setor.',
            'password.min'      => 'A senha deve ter no mínimo 10 caracteres.',
            'password.confirmed'=> 'As senhas não conferem.',
            'password.regex'    => 'A senha não atende aos requisitos de segurança.',
        ]);

        User::create([
            'empresa_id' => $empresa_id,
            'name'       => $request->name,
            'username'   => $request->username,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'role'       => $request->role,
            'setor_id'   => $request->setor_id,
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuário cadastrado com sucesso!');
    }

    // Formulário de edição
    public function edit($id)
    {
        $empresa_id = Auth::user()->empresa_id;
        $usuario    = User::where('empresa_id', $empresa_id)->findOrFail($id);
        $setores    = Setor::where('empresa_id', $empresa_id)->get();
        $roles      = collect(User::ROLES)->except('admin')->all();

        return view('usuarios.edit', compact('usuario', 'setores', 'roles'));
    }

    // Atualizar usuário
    public function update(Request $request, $id)
    {
        $empresa_id = Auth::user()->empresa_id;
        $usuario    = User::where('empresa_id', $empresa_id)->findOrFail($id);

        $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username,' . $usuario->id,
            'email'    => 'required|email|unique:users,email,' . $usuario->id,
            'role'     => 'required|in:coordenador,executor,colaborador',
            'setor_id' => 'required|exists:setores,id',
            'password' => [
                'nullable',
                'confirmed',
                'min:10',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[@#$%&*!]/',
                'not_regex:/\s/',
            ],
        ], [
            'username.unique'   => 'Este nome de usuário já está em uso.',
            'email.unique'      => 'Este e-mail já está cadastrado.',
            'password.min'      => 'A senha deve ter no mínimo 10 caracteres.',
            'password.confirmed'=> 'As senhas não conferem.',
        ]);

        $dados = [
            'name'     => $request->name,
            'username' => $request->username,
            'email'    => $request->email,
            'role'     => $request->role,
            'setor_id' => $request->setor_id,
        ];

        // Só atualiza senha se preenchida
        if ($request->filled('password')) {
            $dados['password'] = Hash::make($request->password);
        }

        $usuario->update($dados);

        return redirect()->route('usuarios.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    // Excluir usuário (ou inativar, se houver histórico vinculado)
    public function destroy($id)
    {
        $empresa_id = Auth::user()->empresa_id;
        $usuario    = User::where('empresa_id', $empresa_id)->findOrFail($id);

        if ($usuario->id === Auth::id()) {
            return redirect()->route('usuarios.index')
                ->with('error', 'Você não pode excluir o próprio usuário.');
        }

        // Regra: só exclui de verdade quando não há nenhum vínculo em outras tabelas.
        if ($usuario->possuiVinculos()) {
            $usuario->update(['ativo' => false]);

            return redirect()->route('usuarios.index')
                ->with('warning', 'Usuário possui histórico vinculado e não pode ser excluído — foi inativado.');
        }

        $usuario->delete();

        return redirect()->route('usuarios.index')->with('success', 'Usuário removido com sucesso!');
    }
}
