@extends('layouts.app')

@section('content')

@php $user = Auth::user(); @endphp

<div style="display:flex; align-items:center; gap:16px; margin-bottom:20px; flex-wrap:wrap;">
    <h5 style="font-weight:700; color:#222; margin:0;">Histórico</h5>
</div>

{{-- Filtros e pesquisa --}}
<form method="GET" action="{{ route('ordens.historico') }}"
      style="background:white; border-radius:10px; padding:16px 20px;
             box-shadow:0 2px 8px rgba(0,0,0,0.06); margin-bottom:20px;
             display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">

    <div style="flex:1; min-width:140px;">
        <label style="font-size:0.78rem; color:#666; display:block; margin-bottom:4px;">Situação</label>
        <select name="status"
                style="width:100%; padding:8px 10px; border:1.5px solid #c5cde8;
                       border-radius:6px; font-size:0.88rem; background:white; outline:none;">
            <option value="">Todas</option>
            @foreach(\App\Models\OrdemServico::STATUS as $key => $label)
                <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div style="flex:1; min-width:140px;">
        <label style="font-size:0.78rem; color:#666; display:block; margin-bottom:4px;">Prioridade</label>
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

    <div style="flex:2; min-width:200px;">
        <label style="font-size:0.78rem; color:#666; display:block; margin-bottom:4px;">Buscar por título</label>
        <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Digite parte do título..."
               style="width:100%; padding:8px 10px; border:1.5px solid #c5cde8;
                      border-radius:6px; font-size:0.88rem; outline:none;">
    </div>

    <div style="display:flex; gap:8px;">
        <button type="submit"
                style="background:#1a35a8; color:white; border:none; border-radius:6px;
                       padding:9px 20px; font-size:0.88rem; font-weight:600; cursor:pointer;">
            Filtrar
        </button>
        <a href="{{ route('ordens.historico') }}"
           style="background:#f0f2f8; color:#555; border-radius:6px; padding:9px 16px;
                  font-size:0.88rem; text-decoration:none; display:inline-block;">
            Limpar
        </a>
    </div>
</form>

{{-- Lista de OS (tabela) --}}
@if($ordens->isEmpty())
    <div style="color:#999; font-size:0.9rem; text-align:center; padding:60px 0;">
        <i class="bi bi-clock-history" style="font-size:2rem; display:block; margin-bottom:8px;"></i>
        Nenhuma ordem de serviço encontrada para esse filtro.
    </div>
@else
    @php
        $urgCores = ['BAIXA'=>'#27ae60','MEDIA'=>'#f39c12','ALTA'=>'#e67e22','URGENTE'=>'#e74c3c'];
    @endphp
    <div class="tabela-os-wrap">
        <table class="tabela-os">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Prioridade</th>
                    <th>Título</th>
                    <th>Situação</th>
                    <th>Setor solicitante</th>
                    <th>Executor</th>
                    <th>Data de solicitação</th>
                    <th>Data de entrega</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ordens as $ordem)
                    @php
                        $uCor = $urgCores[$ordem->urgencia] ?? '#999';
                        $sCor = \App\Models\OrdemServico::STATUS_CORES[$ordem->status] ?? '#999';
                        // Mesma prioridade de ordens/show.blade.php e dashboard.blade.php.
                        $atribuidaAVoce = $ordem->executor_id === $user->id;
                        $disponivel     = !$atribuidaAVoce && $ordem->executor_id === null && $ordem->setor_id === $user->setor_id;
                        $souSolicitante = !$atribuidaAVoce && !$disponivel && $ordem->criado_por === $user->id;
                    @endphp
                    <tr onclick="location.href='{{ route('ordens.show', $ordem->id) }}'">
                        <td class="muted">#{{ $ordem->id }}</td>
                        <td>
                            <span class="badge-tab" style="background:{{ $uCor }}22; color:{{ $uCor }};">
                                {{ \App\Models\OrdemServico::URGENCIA[$ordem->urgencia] ?? $ordem->urgencia }}
                            </span>
                        </td>
                        <td class="col-titulo">
                            {{ $ordem->titulo }}
                            {{-- Selo de relação só faz sentido pro executor: pro colaborador
                                 toda linha é sempre "ele solicitou", por definição do escopo. --}}
                            @if($user->isExecutor())
                                @if($atribuidaAVoce)
                                    <span class="badge-tab" style="background:#1a35a822; color:#1a35a8; margin-left:4px;">
                                        Atribuída a você
                                    </span>
                                @elseif($disponivel)
                                    <span class="badge-tab" style="background:#27ae6022; color:#27ae60; margin-left:4px;">
                                        Disponível
                                    </span>
                                @elseif($souSolicitante)
                                    <span class="badge-tab" style="background:#6c5ce722; color:#6c5ce7; margin-left:4px;">
                                        Você solicitou
                                    </span>
                                @else
                                    <span class="badge-tab" style="background:#99999922; color:#666; margin-left:4px;">
                                        Do seu setor
                                    </span>
                                @endif
                            @endif
                        </td>
                        <td>
                            <span class="badge-tab" style="background:{{ $sCor }}22; color:{{ $sCor }};">
                                {{ \App\Models\OrdemServico::STATUS[$ordem->status] ?? $ordem->status }}
                            </span>
                        </td>
                        <td>{{ $ordem->setor->nome ?? '—' }}</td>
                        <td @class(['muted' => !$ordem->executor])>{{ $ordem->executor->name ?? '—' }}</td>
                        <td>{{ $ordem->created_at->format('d/m/Y') }}</td>
                        <td @class(['muted' => !$ordem->data_entrega])>
                            {{ $ordem->data_entrega ? $ordem->data_entrega->format('d/m/Y') : '—' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@endsection
