<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Setor;

class UsuarioController extends Controller
{
    // Só Admin e Coordenador têm acesso à gestão de usuários.
    private function garantirAcesso(): void
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isCoordenador()) {
            abort(403);
        }
    }

    // Listagem — Admin e Coordenador veem todos os usuários da empresa,
    // mas só podem agir (editar/inativar/excluir) sobre os que cadastraram.
    public function index(Request $request)
    {
        $this->garantirAcesso();

        $empresa_id  = Auth::user()->empresa_id;
        $verInativos = $request->boolean('inativos');

        $usuarios = User::where('empresa_id', $empresa_id)
            ->where('id', '!=', Auth::id()) // não lista o próprio usuário
            ->when(!$verInativos, fn ($q) => $q->where('ativo', true))
            ->with(['setor', 'criador'])
            ->orderBy('ativo', 'desc')
            ->orderBy('name')
            ->get();

        return view('usuarios.index', compact('usuarios', 'verInativos'));
    }

    // Formulário de criação
    public function create()
    {
        $this->garantirAcesso();

        $setores = Setor::where('empresa_id', Auth::user()->empresa_id)->get();
        // 'admin' fica de fora: só é criado no cadastro da empresa
        $roles   = collect(User::ROLES)->except('admin')->all();

        return view('usuarios.create', compact('setores', 'roles'));
    }

    // Salvar novo usuário — quem cadastra vira o "dono" (criado_por) dele.
    public function store(Request $request)
    {
        $this->garantirAcesso();

        $empresa_id = Auth::user()->empresa_id;

        $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'email'    => 'required|email|unique:users,email',
            'role'     => 'nullable|in:coordenador,executor,colaborador',
            'setor_id' => 'nullable|exists:setores,id',
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
            'role'       => $request->role ?: 'colaborador',
            'setor_id'   => $request->setor_id ?: null,
            'criado_por' => Auth::id(),
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuário cadastrado com sucesso!');
    }

    // Formulário de edição — só quem cadastrou o usuário pode editá-lo.
    public function edit($id)
    {
        $this->garantirAcesso();

        $empresa_id = Auth::user()->empresa_id;
        $usuario    = User::where('empresa_id', $empresa_id)->findOrFail($id);

        if (!$usuario->podeSerGerenciadoPor(Auth::user())) {
            abort(403, 'Você só pode editar usuários que você mesmo cadastrou.');
        }

        $setores = Setor::where('empresa_id', $empresa_id)->get();
        $roles   = collect(User::ROLES)->except('admin')->all();

        return view('usuarios.edit', compact('usuario', 'setores', 'roles'));
    }

    // Atualizar usuário
    public function update(Request $request, $id)
    {
        $this->garantirAcesso();

        $empresa_id = Auth::user()->empresa_id;
        $usuario    = User::where('empresa_id', $empresa_id)->findOrFail($id);

        if (!$usuario->podeSerGerenciadoPor(Auth::user())) {
            abort(403, 'Você só pode editar usuários que você mesmo cadastrou.');
        }

        $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username,' . $usuario->id,
            'email'    => 'required|email|unique:users,email,' . $usuario->id,
            'role'     => 'nullable|in:coordenador,executor,colaborador',
            'setor_id' => 'nullable|exists:setores,id',
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
            'role'     => $request->role ?: $usuario->role,
            'setor_id' => $request->setor_id ?: null,
        ];

        // Só atualiza senha se preenchida
        if ($request->filled('password')) {
            $dados['password'] = Hash::make($request->password);
        }

        $usuario->update($dados);

        return redirect()->route('usuarios.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    // Excluir usuário — só é permitido quando NÃO há nenhuma OS vinculada,
    // e só quem cadastrou o usuário pode excluí-lo.
    public function destroy(Request $request, $id)
    {
        $this->garantirAcesso();

        $empresa_id = Auth::user()->empresa_id;
        $usuario    = User::where('empresa_id', $empresa_id)->findOrFail($id);

        if ($usuario->id === Auth::id()) {
            return redirect()->route('usuarios.index', $this->filtroInativos($request))
                ->with('error', 'Você não pode excluir o próprio usuário.');
        }

        if (!$usuario->podeSerGerenciadoPor(Auth::user())) {
            return redirect()->route('usuarios.index', $this->filtroInativos($request))
                ->with('error', "Você só pode excluir usuários que você mesmo cadastrou.");
        }

        if ($usuario->temOrdensVinculadas()) {
            return redirect()->route('usuarios.index', $this->filtroInativos($request))
                ->with('error', "Não é possível excluir \"{$usuario->name}\": há ordens de serviço vinculadas. Use \"Inativar\".");
        }

        $usuario->delete();

        return redirect()->route('usuarios.index', $this->filtroInativos($request))
            ->with('success', "Usuário \"{$usuario->name}\" excluído.");
    }

    // Inativar usuário — alternativa à exclusão quando há OS vinculada.
    public function inativar(Request $request, $id)
    {
        $this->garantirAcesso();

        $empresa_id = Auth::user()->empresa_id;
        $usuario    = User::where('empresa_id', $empresa_id)->findOrFail($id);

        if ($usuario->id === Auth::id()) {
            return redirect()->route('usuarios.index', $this->filtroInativos($request))
                ->with('error', 'Você não pode inativar o próprio usuário.');
        }

        if (!$usuario->podeSerGerenciadoPor(Auth::user())) {
            return redirect()->route('usuarios.index', $this->filtroInativos($request))
                ->with('error', "Você só pode inativar usuários que você mesmo cadastrou.");
        }

        $usuario->update(['ativo' => false]);

        return redirect()->route('usuarios.index', $this->filtroInativos($request))
            ->with('success', "Usuário \"{$usuario->name}\" inativado.");
    }

    // Reativar usuário inativado.
    public function reativar(Request $request, $id)
    {
        $this->garantirAcesso();

        $empresa_id = Auth::user()->empresa_id;
        $usuario    = User::where('empresa_id', $empresa_id)->findOrFail($id);

        if (!$usuario->podeSerGerenciadoPor(Auth::user())) {
            return redirect()->route('usuarios.index', $this->filtroInativos($request))
                ->with('error', "Você só pode reativar usuários que você mesmo cadastrou.");
        }

        $usuario->update(['ativo' => true]);

        return redirect()->route('usuarios.index', $this->filtroInativos($request))
            ->with('success', "Usuário \"{$usuario->name}\" reativado.");
    }

    // Mantém o filtro "visualizar inativos" após a ação.
    private function filtroInativos(Request $request): array
    {
        return $request->boolean('inativos') ? ['inativos' => 1] : [];
    }
}
