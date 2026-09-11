@extends('layouts.app')

@push('styles')
<style>
    .btn-nova-os {
        background:#1a35a8; color:#fff; border-radius:8px; padding:9px 20px;
        font-size:0.88rem; font-weight:600; text-decoration:none;
        display:inline-flex; align-items:center; gap:8px; white-space:nowrap;
        transition:background 0.2s;
    }
    .btn-nova-os:hover { background:#142a86; }
</style>
@endpush

@section('content')

<div style="display:flex; align-items:center; gap:16px; margin-bottom:20px; flex-wrap:wrap;">
    <h5 style="font-weight:700; color:#222; margin:0;">Ordens de Serviço</h5>
    @if(Auth::user()->isCoordenador() || Auth::user()->isAdmin())
        <a href="{{ route('ordens.create') }}" class="btn-nova-os">
            <i class="bi bi-plus-lg"></i> Nova Ordem de Serviço
        </a>
    @endif
</div>

{{-- Filtros --}}
<form method="GET" action="{{ route('ordens.index') }}"
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

{{-- Lista de OS (tabela) --}}
@if($ordens->isEmpty())
    <div style="color:#999; font-size:0.9rem; text-align:center; padding:60px 0;">
        <i class="bi bi-card-checklist" style="font-size:2rem; display:block; margin-bottom:8px;"></i>
        Nenhuma ordem de serviço encontrada.
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
                    @endphp
                    <tr onclick="location.href='{{ route('ordens.show', $ordem->id) }}'">
                        <td class="muted">#{{ $ordem->id }}</td>
                        <td>
                            <span class="badge-tab" style="background:{{ $uCor }}22; color:{{ $uCor }};">
                                {{ \App\Models\OrdemServico::URGENCIA[$ordem->urgencia] ?? $ordem->urgencia }}
                            </span>
                        </td>
                        <td class="col-titulo">{{ $ordem->titulo }}</td>
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
