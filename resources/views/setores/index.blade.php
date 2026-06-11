@extends('layouts.app')

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <h5 style="font-weight:700; color:#222; margin:0;">Setores</h5>
    <a href="{{ route('setores.create') }}"
       style="background:#1a35a8; color:white; border-radius:8px; padding:10px 22px;
              font-size:0.9rem; font-weight:600; text-decoration:none; display:inline-block;">
        Cadastrar Novo Setor
    </a>
</div>

{{-- Alertas --}}
@if(session('sucesso'))
    <div style="background:#eafaf1; border:1px solid #27ae60; color:#1e8449;
                border-radius:8px; padding:12px 16px; margin-bottom:20px; font-size:0.88rem;">
        <i class="bi bi-check-circle"></i> {{ session('sucesso') }}
    </div>
@endif

@if(session('erro'))
    <div style="background:#fdecea; border:1px solid #e74c3c; color:#c0392b;
                border-radius:8px; padding:12px 16px; margin-bottom:20px; font-size:0.88rem;">
        <i class="bi bi-exclamation-circle"></i> {{ session('erro') }}
    </div>
@endif

@if($setores->isEmpty())
    <div style="color:#999; font-size:0.9rem; text-align:center; padding:60px 0;">
        <i class="bi bi-diagram-3" style="font-size:2rem; display:block; margin-bottom:8px;"></i>
        Nenhum setor cadastrado ainda.
    </div>
@else
    <div style="display:flex; flex-direction:column; gap:10px;">
        @foreach($setores as $setor)
            <div style="background:white; border-radius:10px; padding:16px 20px;
                        box-shadow:0 2px 8px rgba(0,0,0,0.06);
                        display:flex; align-items:center; justify-content:space-between;">

                <div>
                    <div style="font-weight:700; font-size:0.97rem; color:#222;">
                        {{ $setor->nome }}
                    </div>
                    <div style="font-size:0.82rem; color:#888; margin-top:2px;">
                        <i class="bi bi-person"></i>
                        Responsável: {{ $setor->responsavel ? $setor->responsavel->name : '—' }}
                    </div>
                </div>

                <div style="display:flex; align-items:center; gap:24px;">
                    <span style="font-size:0.85rem; color:#555;">
                        <i class="bi bi-people"></i>
                        {{ $setor->usuarios->count() }}
                        {{ $setor->usuarios->count() === 1 ? 'usuário' : 'usuários' }}
                    </span>

                    <div style="display:flex; gap:12px;">
                        <a href="{{ route('setores.edit', $setor) }}"
                           title="Editar"
                           style="color:#1a35a8; font-size:1.1rem; text-decoration:none;">
                            <i class="bi bi-pencil-fill"></i>
                        </a>

                        <form action="{{ route('setores.destroy', $setor) }}" method="POST"
                              onsubmit="return confirm('Excluir o setor \'{{ addslashes($setor->nome) }}\'?')">
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
