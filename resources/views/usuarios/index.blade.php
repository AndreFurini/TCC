@extends('layouts.app')

@push('styles')
<style>
    .btn-cadastrar {
        background:#1a35a8; color:#fff; border-radius:8px; padding:10px 22px;
        font-size:0.9rem; font-weight:600; text-decoration:none;
        display:inline-flex; align-items:center; gap:8px;
        transition:background 0.2s;
    }
    .btn-cadastrar:hover { background:#142a86; }

    .toolbar-cadastro { margin-bottom:20px; }

    .lista-vazia {
        display:flex; flex-direction:column; align-items:center;
        text-align:center; padding:60px 20px; gap:14px;
    }
    .lista-vazia .icone-vazio { font-size:2.4rem; color:#c5cde8; }
    .lista-vazia p { color:#999; font-size:0.9rem; margin:0; }
</style>
@endpush

@section('content')

@if($usuarios->isEmpty())

    <div class="lista-vazia">
        <i class="bi bi-people icone-vazio"></i>
        <p>Nenhum usuário cadastrado ainda.</p>
        <a href="{{ route('usuarios.create') }}" class="btn-cadastrar">
            <i class="bi bi-plus-lg"></i> Cadastrar Novo Usuário
        </a>
    </div>

@else

    <div class="toolbar-cadastro">
        <a href="{{ route('usuarios.create') }}" class="btn-cadastrar">
            <i class="bi bi-plus-lg"></i> Cadastrar Novo Usuário
        </a>
    </div>

    <div style="display:flex; flex-direction:column; gap:10px;">
        @foreach($usuarios as $usuario)
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
        @endforeach
    </div>

@endif

@endsection
