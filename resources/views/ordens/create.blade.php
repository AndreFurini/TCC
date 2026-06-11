@extends('layouts.app')

@section('content')

@php $user = Auth::user(); @endphp

<div style="display:flex; align-items:center; gap:12px; margin-bottom:20px;">
    <a href="{{ $user->isCoordenador() ? route('ordens.index') : route('dashboard') }}"
       style="color:#1a35a8; font-size:1.1rem; text-decoration:none;" title="Voltar">
        <i class="bi bi-arrow-left-circle-fill"></i>
    </a>
    <h5 style="font-weight:700; color:#222; margin:0;">Nova Ordem de Serviço</h5>
</div>

<div style="background:white; border-radius:12px; overflow:hidden;
            box-shadow:0 2px 12px rgba(0,0,0,0.08); max-width:680px;">

    <div style="background:#1a35a8; color:white; font-weight:700;
                font-size:1rem; padding:14px 24px; letter-spacing:0.5px;">
        <i class="bi bi-card-checklist"></i> Ordem de Serviço
    </div>

    <form action="{{ route('ordens.store') }}" method="POST" style="padding:24px;">
        @csrf

        @if($errors->any())
            <div style="background:#fdecea; border:1px solid #e74c3c; color:#c0392b;
                        border-radius:8px; padding:10px 16px; margin-bottom:20px; font-size:0.85rem;">
                <ul style="margin:0; padding-left:18px;">
                    @foreach($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Título --}}
        <div style="margin-bottom:16px;">
            <label style="font-size:0.83rem; color:#444; display:block; margin-bottom:4px;">
                Título: <span style="color:#e74c3c;">*</span>
            </label>
            <input type="text" name="titulo" value="{{ old('titulo') }}"
                   placeholder="Descreva brevemente o problema ou serviço"
                   required
                   style="width:100%; padding:9px 12px;
                          border:1.5px solid {{ $errors->has('titulo') ? '#e74c3c' : '#c5cde8' }};
                          border-radius:6px; font-size:0.93rem; outline:none;">
        </div>

        {{-- Setor --}}
        <div style="margin-bottom:16px;">
            <label style="font-size:0.83rem; color:#444; display:block; margin-bottom:4px;">
                Setor Solicitante: <span style="color:#e74c3c;">*</span>
            </label>
            <select name="setor_id" required
                    style="width:100%; padding:9px 12px;
                           border:1.5px solid {{ $errors->has('setor_id') ? '#e74c3c' : '#c5cde8' }};
                           border-radius:6px; font-size:0.93rem; outline:none; background:white;">
                <option value="">— Selecione o setor —</option>
                @foreach($setores as $setor)
                    <option value="{{ $setor->id }}" {{ old('setor_id') == $setor->id ? 'selected' : '' }}>
                        {{ $setor->nome }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Urgência e Executor — só para Coordenador --}}
        @if($user->isCoordenador())
            <div style="display:flex; gap:16px; margin-bottom:16px; flex-wrap:wrap;">
                <div style="flex:1; min-width:180px;">
                    <label style="font-size:0.83rem; color:#444; display:block; margin-bottom:4px;">
                        Grau de Urgência: <span style="color:#e74c3c;">*</span>
                    </label>
                    <select name="urgencia" required
                            style="width:100%; padding:9px 12px;
                                   border:1.5px solid {{ $errors->has('urgencia') ? '#e74c3c' : '#c5cde8' }};
                                   border-radius:6px; font-size:0.93rem; outline:none; background:white;">
                        <option value="">— Selecione —</option>
                        @foreach(\App\Models\OrdemServico::URGENCIA as $key => $label)
                            <option value="{{ $key }}" {{ old('urgencia') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="flex:1; min-width:180px;">
                    <label style="font-size:0.83rem; color:#444; display:block; margin-bottom:4px;">
                        Executante Responsável:
                    </label>
                    <select name="executor_id"
                            style="width:100%; padding:9px 12px; border:1.5px solid #c5cde8;
                                   border-radius:6px; font-size:0.93rem; outline:none; background:white;">
                        <option value="">— Atribuir depois —</option>
                        @foreach($executores as $executor)
                            <option value="{{ $executor->id }}" {{ old('executor_id') == $executor->id ? 'selected' : '' }}>
                                {{ $executor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif

        {{-- Descrição --}}
        <div style="margin-bottom:24px;">
            <label style="font-size:0.83rem; color:#444; display:block; margin-bottom:4px;">
                Descrição: <span style="color:#e74c3c;">*</span>
            </label>
            <textarea name="descricao" rows="4" required
                      placeholder="Descreva detalhadamente o que precisa ser feito..."
                      style="width:100%; padding:9px 12px;
                             border:1.5px solid {{ $errors->has('descricao') ? '#e74c3c' : '#c5cde8' }};
                             border-radius:6px; font-size:0.93rem; outline:none; resize:vertical;">{{ old('descricao') }}</textarea>
        </div>

        {{-- Botões --}}
        <div style="display:flex; gap:12px; justify-content:flex-end;">
            <a href="{{ $user->isCoordenador() ? route('ordens.index') : route('dashboard') }}"
               style="padding:10px 28px; border:1.5px solid #e74c3c; border-radius:6px;
                      color:#e74c3c; text-decoration:none; font-size:0.9rem; font-weight:600;">
                Cancelar
            </a>
            <button type="submit"
                    style="background:#27ae60; color:white; border:none; border-radius:6px;
                           padding:10px 36px; font-size:0.9rem; font-weight:600; cursor:pointer;">
                Criar
            </button>
        </div>
    </form>
</div>

@endsection
