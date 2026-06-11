@extends('layouts.app')

@section('content')

<div style="margin-bottom:20px;">
    <a href="{{ route('usuarios.create') }}"
       style="background:#1a35a8; color:white; border-radius:8px; padding:10px 22px;
              font-size:0.9rem; font-weight:600; text-decoration:none; display:inline-block;">
        Cadastrar Novo Usuário
    </a>
</div>

<div style="display:flex; flex-direction:column; gap:10px;">
    @forelse($usuarios as $usuario)
        <div style="background:white; border-radius:10px; padding:16px 20px;
                    box-shadow:0 2px 8px rgba(0,0,0,0.06);
                    display:flex; align-items:center; justify-content:space-between;">

            <div>
                <div style="font-weight:700; font-size:0.97rem; color:#222;">{{ $usuario->name }}</div>
                <div style="font-size:0.82rem; color:#888; margin-top:2px;">
                    {{ \App\Models\User::ROLES[$usuario->role] ?? $usuario->role }}
                </div>
            </div>

            <div style="display:flex; align-items:center; gap:24px;">
                <span style="font-size:0.85rem; color:#555;">
                    Setor: {{ $usuario->setor->nome ?? '—' }}
                </span>

                <div style="display:flex; gap:12px;">
                    <a href="{{ route('usuarios.edit', $usuario->id) }}"
                       title="Editar"
                       style="color:#1a35a8; font-size:1.1rem; text-decoration:none;">
                        <i class="bi bi-pencil-fill"></i>
                    </a>

                    <form action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST"
                          onsubmit="return confirm('Excluir usuário {{ addslashes($usuario->name) }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" title="Excluir"
                                style="background:none; border:none; color:#e74c3c;
                                       font-size:1.1rem; cursor:pointer; padding:0;">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </form>
                </div>
            </div>

        </div>
    @empty
        <div style="color:#999; font-size:0.9rem; text-align:center; padding:40px 0;">
            Nenhum usuário cadastrado ainda.
        </div>
    @endforelse
</div>

@endsection
