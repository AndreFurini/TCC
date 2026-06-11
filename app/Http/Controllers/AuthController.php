<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Empresa;

class AuthController extends Controller
{
    // -------------------------------------------------------
    // LOGIN
    // -------------------------------------------------------

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'codigo_empresa' => 'required|string',
            'username'       => 'required|string',
            'password'       => 'required|string',
        ]);

        // Buscar empresa pelo código
        $empresa = Empresa::where('codigo_empresa', $request->codigo_empresa)->first();

        if (!$empresa) {
            return back()->with('error', 'Código da empresa não encontrado.')->withInput();
        }

        // Buscar usuário dentro da empresa
        $user = User::where('username', $request->username)
                    ->where('empresa_id', $empresa->id)
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Usuário ou senha incorretos.')->withInput();
        }

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }

    // -------------------------------------------------------
    // CADASTRO DA EMPRESA
    // -------------------------------------------------------

    public function showCadastro()
    {
        return view('auth.cadastro');
    }

    public function storeCadastro(Request $request)
    {
        $request->validate([
            'nome_empresa'  => 'required|string|max:255',
            'cnpj'          => 'nullable|string|max:20',
            'nome_completo' => 'required|string|max:255',
            'username'      => 'required|string|max:50|unique:users,username',
            'email'         => 'required|email|unique:users,email',
            'password'      => [
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
            'nome_empresa.required'  => 'O nome da empresa é obrigatório.',
            'nome_completo.required' => 'O nome completo é obrigatório.',
            'username.required'      => 'O nome de usuário é obrigatório.',
            'username.unique'        => 'Este nome de usuário já está em uso.',
            'email.required'         => 'O e-mail é obrigatório.',
            'email.unique'           => 'Este e-mail já está cadastrado.',
            'password.min'           => 'A senha deve ter no mínimo 10 caracteres.',
            'password.confirmed'     => 'As senhas não conferem.',
            'password.regex'         => 'A senha não atende aos requisitos de segurança.',
        ]);

        // Gerar código único da empresa (6 dígitos)
        do {
            $codigo = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6));
        } while (Empresa::where('codigo_empresa', $codigo)->exists());

        // Criar empresa
        $empresa = Empresa::create([
            'nome'           => $request->nome_empresa,
            'cnpj'           => $request->cnpj,
            'codigo_empresa' => $codigo,
        ]);

        // Criar usuário Admin
        $user = User::create([
            'empresa_id'    => $empresa->id,
            'name'          => $request->nome_completo,
            'username'      => $request->username,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'role'          => 'admin',
        ]);

        Auth::login($user);

        // Redirecionar para dashboard com código visível
        return redirect()->route('dashboard')->with('codigo_empresa', $codigo);
    }
}
