@extends('layouts.app')

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
    <h5 style="font-weight:700; color:#222; margin:0;">Ordens de Serviço</h5>
    <a href="{{ route('ordens.create') }}"
       style="background:#1a35a8; color:white; border-radius:8px; padding:10px 22px;
              font-size:0.9rem; font-weight:600; text-decoration:none; display:inline-block;">
        Nova Ordem de Serviço
    </a>
</div>

{{-- Alertas --}}
@if(session('success'))
    <div style="background:#eafaf1; border:1px solid #27ae60; color:#1e8449;
                border-radius:8px; padding:12px 16px; margin-bottom:20px; font-size:0.88rem;">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
    </div>
@endif

{{-- Filtros --}}
<form method="GET" action="{{ route('ordens.index') }}"
      style="background:white; border-radius:10px; padding:16px 20px;
             box-shadow:0 2px 8px rgba(0,0,0,0.06); margin-bottom:20px;
             display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">

    <div style="flex:1; min-width:140px;">
        <label style="font-size:0.78rem; color:#666; display:block; margin-bottom:4px;">Status</label>
        <select name="status"
                style="width:100%; padding:8px 10px; border:1.5px solid #c5cde8;
                       border-radius:6px; font-size:0.88rem; background:white; outline:none;">
            <option value="">Todos</option>
            @foreach(\App\Models\OrdemServico::STATUS as $key => $label)
                <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div style="flex:1; min-width:140px;">
        <label style="font-size:0.78rem; color:#666; display:block; margin-bottom:4px;">Urgência</label>
        <select name="urgencia"
                style="width:100%; padding:8px 10px; border:1.5px solid #c5cde8;
                       border-radius:6px; font-size:0.88rem; background:white; outline:none;">
            <option value="">Todas</option>
            @foreach(\App\Models\OrdemServico::URGENCIA as $key => $label)
                <option value="{{ $key }}" {{ request('urgencia') == $key ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div style="flex:1; min-width:140px;">
        <label style="font-size:0.78rem; color:#666; display:block; margin-bottom:4px;">Setor</label>
        <select name="setor_id"
                style="width:100%; padding:8px 10px; border:1.5px solid #c5cde8;
                       border-radius:6px; font-size:0.88rem; background:white; outline:none;">
            <option value="">Todos</option>
            @foreach($setores as $setor)
                <option value="{{ $setor->id }}" {{ request('setor_id') == $setor->id ? 'selected' : '' }}>
                    {{ $setor->nome }}
                </option>
            @endforeach
        </select>
    </div>

    <div style="display:flex; gap:8px;">
        <button type="submit"
                style="background:#1a35a8; color:white; border:none; border-radius:6px;
                       padding:9px 20px; font-size:0.88rem; font-weight:600; cursor:pointer;">
            Filtrar
        </button>
        <a href="{{ route('ordens.index') }}"
           style="background:#f0f2f8; color:#555; border-radius:6px; padding:9px 16px;
                  font-size:0.88rem; text-decoration:none; display:inline-block;">
            Limpar
        </a>
    </div>
</form>

{{-- Lista de OS --}}
@if($ordens->isEmpty())
    <div style="color:#999; font-size:0.9rem; text-align:center; padding:60px 0;">
        <i class="bi bi-card-checklist" style="font-size:2rem; display:block; margin-bottom:8px;"></i>
        Nenhuma ordem de serviço encontrada.
    </div>
@else
    <div style="display:flex; flex-direction:column; gap:10px;">
        @foreach($ordens as $ordem)
            @php
                $cores = \App\Models\OrdemServico::STATUS_CORES;
                $cor   = $cores[$ordem->status] ?? '#999';
                $urgenciaCores = ['BAIXA'=>'#27ae60','MEDIA'=>'#f39c12','ALTA'=>'#e67e22','URGENTE'=>'#e74c3c'];
                $corUrgencia = $urgenciaCores[$ordem->urgencia] ?? '#999';
            @endphp
            <a href="{{ route('ordens.show', $ordem->id) }}"
               style="background:white; border-radius:10px; padding:16px 20px;
                      box-shadow:0 2px 8px rgba(0,0,0,0.06); text-decoration:none; color:inherit;
                      display:flex; align-items:center; justify-content:space-between; gap:12px;
                      transition: box-shadow 0.2s;"
               onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,0.10)'"
               onmouseout="this.style.boxShadow='0 2px 8px rgba(0,0,0,0.06)'">

                <div style="display:flex; align-items:center; gap:12px; flex:1; min-width:0;">
                    <span style="width:12px; height:12px; background:{{ $cor }};
                                 border-radius:50%; flex-shrink:0;"></span>
                    <div style="min-width:0;">
                        <div style="font-weight:700; font-size:0.95rem; color:#222;
                                    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                            {{ $ordem->titulo }}
                        </div>
                        <div style="font-size:0.8rem; color:#888; margin-top:2px;">
                            {{ $ordem->setor->nome ?? '—' }}
                            @if($ordem->executor)
                                · Executor: {{ $ordem->executor->name }}
                            @else
                                · <span style="color:#e67e22;">Sem executor</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div style="display:flex; align-items:center; gap:16px; flex-shrink:0;">
                    <span style="background:{{ $corUrgencia }}22; color:{{ $corUrgencia }};
                                 font-size:0.75rem; font-weight:700; padding:3px 10px;
                                 border-radius:20px; white-space:nowrap;">
                        {{ \App\Models\OrdemServico::URGENCIA[$ordem->urgencia] ?? $ordem->urgencia }}
                    </span>
                    <span style="font-size:0.8rem; color:#999; white-space:nowrap;">
                        {{ $ordem->created_at->format('d/m/Y') }}
                    </span>
                </div>
            </a>
        @endforeach
    </div>
@endif

@endsection
