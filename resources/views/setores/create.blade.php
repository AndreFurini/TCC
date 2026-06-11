@extends('layouts.app')

@section('content')

<div style="background:white; border-radius:12px; overflow:hidden;
            box-shadow:0 2px 12px rgba(0,0,0,0.08); max-width:600px;">

    <div style="background:#1a35a8; color:white; font-weight:700;
                font-size:1rem; padding:14px 24px; letter-spacing:0.5px;">
        Dados do Setor
    </div>

    <form action="{{ route('setores.store') }}" method="POST" style="padding:24px;">
        @csrf

        @if($errors->any())
            <div style="background:#fdecea; border:1px solid #e74c3c; color:#c0392b;
                        border-radius:8px; padding:10px 16px; margin-bottom:20px; font-size:0.85rem;">
                <ul style="margin:0; padding-left:18px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div style="margin-bottom:20px;">
            <label style="font-size:0.83rem; color:#444; display:block; margin-bottom:4px;">Nome:</label>
            <input type="text" name="nome" value="{{ old('nome') }}" required autofocus
                   style="width:100%; padding:9px 12px; border:1.5px solid #c5cde8;
                          border-radius:6px; font-size:0.95rem; outline:none;">
        </div>

        <div style="margin-bottom:24px;">
            <label style="font-size:0.83rem; color:#444; display:block; margin-bottom:4px;">Responsável:</label>
            <select name="responsavel_id"
                    style="width:100%; padding:9px 12px; border:1.5px solid #c5cde8;
                           border-radius:6px; font-size:0.93rem; outline:none; background:white;">
                <option value="">Selecionar...</option>
                @foreach($usuarios as $usuario)
                    <option value="{{ $usuario->id }}" {{ old('responsavel_id') == $usuario->id ? 'selected' : '' }}>
                        {{ $usuario->name }}
                    </option>
                @endforeach
            </select>
            <div style="font-size:0.78rem; color:#999; margin-top:4px;">
                Opcional — define quem é responsável por este setor.
            </div>
        </div>

        <div style="display:flex; gap:12px; justify-content:flex-end;">
            <a href="{{ route('setores.index') }}"
               style="padding:10px 28px; border:1.5px solid #c5cde8; border-radius:6px;
                      color:#555; text-decoration:none; font-size:0.9rem;">
                Cancelar
            </a>
            <button type="submit"
                    style="background:#27ae60; color:white; border:none; border-radius:6px;
                           padding:10px 36px; font-size:0.9rem; font-weight:600; cursor:pointer;">
                Salvar
            </button>
        </div>
    </form>
</div>

@endsection
