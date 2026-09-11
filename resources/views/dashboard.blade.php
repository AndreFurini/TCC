@extends('layouts.app')

@section('content')

@php $user = Auth::user(); @endphp

{{-- ============================================================
     ADMIN — Dashboard com filtro por setor
     ============================================================ --}}
@if($user->isAdmin())

{{-- Filtros e pesquisa --}}
<form method="GET" action="{{ route('dashboard') }}"
      style="background:white; border-radius:10px; padding:16px 20px;
             box-shadow:0 2px 8px rgba(0,0,0,0.06); margin-bottom:20px;
             display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">

    <div style="flex:1; min-width:150px;">
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

    <div style="flex:1; min-width:140px;">
        <label style="font-size:0.78rem; color:#666; display:block; margin-bottom:4px;">Situação</label>
        <select name="status"
                style="width:100%; padding:8px 10px; border:1.5px solid #c5cde8;
                       border-radius:6px; font-size:0.88rem; background:white; outline:none;">
            <option value="">Todas</option>
            @foreach(\App\Models\OrdemServico::STATUS as $key => $label)
                <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
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
                <option value="{{ $key }}" {{ request('urgencia') == $key ? 'selected' : '' }}>{{ $label }}</option>
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
        <a href="{{ route('dashboard') }}"
           style="background:#f0f2f8; color:#555; border-radius:6px; padding:9px 16px;
                  font-size:0.88rem; text-decoration:none; display:inline-block;">
            Limpar
        </a>
    </div>
</form>

<p style="font-size:0.78rem; color:#999; margin:-12px 0 20px;">
    <i class="bi bi-info-circle"></i>
    Setor filtra todo o painel abaixo. Situação, Prioridade e Busca filtram só a lista "Atividade Recente".
</p>

{{-- Cards de contagem --}}
@include('partials.cards-contagem')

{{-- Atrasadas + Sem executor --}}
<div style="display:grid; grid-template-columns: repeat(2, 1fr); gap:16px; margin-top:16px;">
    <div style="background:white; border-radius:12px; padding:20px 24px;
                box-shadow:0 2px 8px rgba(0,0,0,0.07); text-align:center;">
        <div style="display:flex; align-items:center; justify-content:center; gap:8px; margin-bottom:10px;">
            <i class="bi bi-alarm" style="color:#e74c3c;"></i>
            <span style="font-size:0.82rem; font-weight:600; color:#555;">OS Atrasadas</span>
        </div>
        <div style="font-size:2rem; font-weight:700; color:{{ $atrasadas > 0 ? '#e74c3c' : '#222' }};">
            {{ str_pad($atrasadas, 2, '0', STR_PAD_LEFT) }}
        </div>
        <div style="font-size:0.72rem; color:#999; margin-top:4px;">data de entrega vencida, não finalizada</div>
    </div>

    <div style="background:white; border-radius:12px; padding:20px 24px;
                box-shadow:0 2px 8px rgba(0,0,0,0.07); text-align:center;">
        <div style="display:flex; align-items:center; justify-content:center; gap:8px; margin-bottom:10px;">
            <i class="bi bi-person-dash" style="color:#e67e22;"></i>
            <span style="font-size:0.82rem; font-weight:600; color:#555;">OS Sem Executor</span>
        </div>
        <div style="font-size:2rem; font-weight:700; color:{{ $semExecutor > 0 ? '#e67e22' : '#222' }};">
            {{ str_pad($semExecutor, 2, '0', STR_PAD_LEFT) }}
        </div>
        <div style="font-size:0.72rem; color:#999; margin-top:4px;">abertas/andamento sem responsável</div>
    </div>
</div>

{{-- Distribuição por urgência --}}
@php
    $urgCores = ['BAIXA'=>'#27ae60','MEDIA'=>'#f39c12','ALTA'=>'#e67e22','URGENTE'=>'#e74c3c'];
@endphp
<div style="background:white; border-radius:12px; padding:18px 20px; margin-top:16px;
            box-shadow:0 2px 8px rgba(0,0,0,0.07);">
    <div style="font-size:0.82rem; font-weight:600; color:#555; margin-bottom:14px;">
        Distribuição por Prioridade <span style="color:#aaa; font-weight:400;">(OS abertas/em andamento)</span>
    </div>
    <div style="display:flex; gap:12px; flex-wrap:wrap;">
        @foreach(\App\Models\OrdemServico::URGENCIA as $key => $label)
            @php $cor = $urgCores[$key] ?? '#999'; @endphp
            <div style="flex:1; min-width:110px; background:{{ $cor }}11; border-radius:8px;
                        padding:12px; text-align:center;">
                <div style="font-size:1.4rem; font-weight:700; color:{{ $cor }};">
                    {{ $distribuicaoUrgencia[$key] ?? 0 }}
                </div>
                <div style="font-size:0.75rem; color:#666; margin-top:2px;">{{ $label }}</div>
            </div>
        @endforeach
    </div>
</div>

{{-- Ranking de setores + Setores sem responsável --}}
<div style="display:flex; gap:16px; flex-wrap:wrap; margin-top:16px;">

    <div style="flex:1; min-width:260px; background:white; border-radius:12px; padding:18px 20px;
                box-shadow:0 2px 8px rgba(0,0,0,0.07);">
        <div style="font-size:0.82rem; font-weight:600; color:#555; margin-bottom:12px;">
            Ranking de Setores <span style="color:#aaa; font-weight:400;">(OS abertas/andamento)</span>
        </div>
        @forelse($rankingSetores as $setor)
            <a href="{{ route('ordens.index', ['setor_id' => $setor->id]) }}"
               style="display:flex; align-items:center; justify-content:space-between;
                      padding:8px 0; border-bottom:1px solid #f0f2f8; text-decoration:none; color:inherit;">
                <span style="font-size:0.88rem; color:#333;">{{ $setor->nome }}</span>
                <span style="background:#eef1f7; color:#1a35a8; font-size:0.78rem; font-weight:700;
                             padding:2px 10px; border-radius:20px;">
                    {{ $setor->os_abertas_count }}
                </span>
            </a>
        @empty
            <p style="color:#999; font-size:0.85rem; margin:0;">Nenhum setor cadastrado.</p>
        @endforelse
    </div>

    <div style="flex:1; min-width:260px; background:white; border-radius:12px; padding:18px 20px;
                box-shadow:0 2px 8px rgba(0,0,0,0.07);">
        <div style="font-size:0.82rem; font-weight:600; color:#555; margin-bottom:12px;">
            Setores Sem Responsável
        </div>
        @forelse($setoresSemResponsavel as $setor)
            <a href="{{ route('setores.edit', $setor) }}"
               style="display:flex; align-items:center; justify-content:space-between;
                      padding:8px 0; border-bottom:1px solid #f0f2f8; text-decoration:none; color:inherit;">
                <span style="font-size:0.88rem; color:#333;">
                    <i class="bi bi-exclamation-triangle" style="color:#f5a623;"></i> {{ $setor->nome }}
                </span>
                <span style="font-size:0.78rem; color:#1a35a8; font-weight:600;">Definir →</span>
            </a>
        @empty
            <p style="color:#999; font-size:0.85rem; margin:0;">
                <i class="bi bi-check-circle" style="color:#27ae60;"></i> Todos os setores têm responsável.
            </p>
        @endforelse
    </div>

</div>

{{-- Atividade recente (respeita Situação/Prioridade/Busca) --}}
<div style="margin-top:24px;">
    <div style="font-size:0.9rem; font-weight:700; color:#222; margin-bottom:12px;">
        Atividade Recente
    </div>

    @if($ordensRecentes->isEmpty())
        <div style="color:#999; font-size:0.9rem; text-align:center; padding:40px 0; background:white;
                    border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
            Nenhuma ordem de serviço encontrada para esse filtro.
        </div>
    @else
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
                    @foreach($ordensRecentes as $ordem)
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
</div>

{{-- ============================================================
     COORDENADOR — Dashboard com setor fixo
     ============================================================ --}}
@elseif($user->isCoordenador())

{{-- Filtros e pesquisa --}}
<form method="GET" action="{{ route('dashboard') }}"
      style="background:white; border-radius:10px; padding:16px 20px;
             box-shadow:0 2px 8px rgba(0,0,0,0.06); margin-bottom:20px;
             display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">

    <div style="flex:1; min-width:150px;">
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

    <div style="flex:1; min-width:140px;">
        <label style="font-size:0.78rem; color:#666; display:block; margin-bottom:4px;">Situação</label>
        <select name="status"
                style="width:100%; padding:8px 10px; border:1.5px solid #c5cde8;
                       border-radius:6px; font-size:0.88rem; background:white; outline:none;">
            <option value="">Todas</option>
            @foreach(\App\Models\OrdemServico::STATUS as $key => $label)
                <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
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
                <option value="{{ $key }}" {{ request('urgencia') == $key ? 'selected' : '' }}>{{ $label }}</option>
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
        <a href="{{ route('dashboard') }}"
           style="background:#f0f2f8; color:#555; border-radius:6px; padding:9px 16px;
                  font-size:0.88rem; text-decoration:none; display:inline-block;">
            Limpar
        </a>
    </div>
</form>

<p style="font-size:0.78rem; color:#999; margin:-12px 0 20px;">
    <i class="bi bi-info-circle"></i>
    Setor filtra todo o painel abaixo. Situação, Prioridade e Busca filtram só a lista "Atividade Recente".
</p>

{{-- OS Urgente --}}
@if($urgente)
    <div style="background:white; border-radius:10px; padding:18px 20px; margin-bottom:20px;
                box-shadow:0 2px 8px rgba(0,0,0,0.07); border-left: 4px solid #e74c3c;">
        <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
            <span style="width:12px; height:12px; background:#e74c3c; border-radius:50%; display:inline-block;"></span>
            <strong style="font-size:0.9rem;">Ordem URGENTE</strong>
        </div>
        <div style="font-weight:600; font-size:1rem; color:#222;">{{ $urgente->titulo }}</div>
        <div style="font-size:0.8rem; color:#999; text-align:right; margin-top:8px;">
            {{ $urgente->created_at->format('d/m/Y') }}
        </div>
    </div>
@endif

{{-- Cards de contagem --}}
@include('partials.cards-contagem')

{{-- Atrasadas + Sem executor --}}
<div style="display:grid; grid-template-columns: repeat(2, 1fr); gap:16px; margin-top:16px;">
    <div style="background:white; border-radius:12px; padding:20px 24px;
                box-shadow:0 2px 8px rgba(0,0,0,0.07); text-align:center;">
        <div style="display:flex; align-items:center; justify-content:center; gap:8px; margin-bottom:10px;">
            <i class="bi bi-alarm" style="color:#e74c3c;"></i>
            <span style="font-size:0.82rem; font-weight:600; color:#555;">OS Atrasadas</span>
        </div>
        <div style="font-size:2rem; font-weight:700; color:{{ $atrasadas > 0 ? '#e74c3c' : '#222' }};">
            {{ str_pad($atrasadas, 2, '0', STR_PAD_LEFT) }}
        </div>
        <div style="font-size:0.72rem; color:#999; margin-top:4px;">data de entrega vencida, não finalizada</div>
    </div>

    <div style="background:white; border-radius:12px; padding:20px 24px;
                box-shadow:0 2px 8px rgba(0,0,0,0.07); text-align:center;">
        <div style="display:flex; align-items:center; justify-content:center; gap:8px; margin-bottom:10px;">
            <i class="bi bi-person-dash" style="color:#e67e22;"></i>
            <span style="font-size:0.82rem; font-weight:600; color:#555;">OS Sem Executor</span>
        </div>
        <div style="font-size:2rem; font-weight:700; color:{{ $semExecutor > 0 ? '#e67e22' : '#222' }};">
            {{ str_pad($semExecutor, 2, '0', STR_PAD_LEFT) }}
        </div>
        <div style="font-size:0.72rem; color:#999; margin-top:4px;">abertas/andamento sem responsável</div>
    </div>
</div>

{{-- Distribuição por urgência --}}
@php
    $urgCores = ['BAIXA'=>'#27ae60','MEDIA'=>'#f39c12','ALTA'=>'#e67e22','URGENTE'=>'#e74c3c'];
@endphp
<div style="background:white; border-radius:12px; padding:18px 20px; margin-top:16px;
            box-shadow:0 2px 8px rgba(0,0,0,0.07);">
    <div style="font-size:0.82rem; font-weight:600; color:#555; margin-bottom:14px;">
        Distribuição por Prioridade <span style="color:#aaa; font-weight:400;">(OS abertas/em andamento)</span>
    </div>
    <div style="display:flex; gap:12px; flex-wrap:wrap;">
        @foreach(\App\Models\OrdemServico::URGENCIA as $key => $label)
            @php $cor = $urgCores[$key] ?? '#999'; @endphp
            <div style="flex:1; min-width:110px; background:{{ $cor }}11; border-radius:8px;
                        padding:12px; text-align:center;">
                <div style="font-size:1.4rem; font-weight:700; color:{{ $cor }};">
                    {{ $distribuicaoUrgencia[$key] ?? 0 }}
                </div>
                <div style="font-size:0.75rem; color:#666; margin-top:2px;">{{ $label }}</div>
            </div>
        @endforeach
    </div>
</div>

{{-- Atividade recente (respeita Situação/Prioridade/Busca) --}}
<div style="margin-top:24px;">
    <div style="font-size:0.9rem; font-weight:700; color:#222; margin-bottom:12px;">
        Atividade Recente
    </div>

    @if($ordensRecentes->isEmpty())
        <div style="color:#999; font-size:0.9rem; text-align:center; padding:40px 0; background:white;
                    border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
            Nenhuma ordem de serviço encontrada para esse filtro.
        </div>
    @else
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
                    @foreach($ordensRecentes as $ordem)
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
</div>

{{-- ============================================================
     EXECUTOR — Dashboard com lista de OS
     ============================================================ --}}
@elseif($user->isExecutor())

@include('partials.cards-contagem')

<div style="margin-top:24px;">
    @if($ordens->isEmpty())
        <div style="color:#999; font-size:0.9rem; text-align:center; padding:60px 0;">
            <i class="bi bi-card-checklist" style="font-size:2rem; display:block; margin-bottom:8px;"></i>
            Nenhuma ordem de serviço atribuída a você no momento.
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
</div>

{{-- ============================================================
     COLABORADOR — Dashboard com lista e botão nova OS
     ============================================================ --}}
@elseif($user->isColaborador())

@include('partials.cards-contagem')

<div style="margin-top:20px; margin-bottom:16px;">
    <a href="{{ route('ordens.create') }}"
       style="background:#1a35a8; color:white; border-radius:8px; padding:10px 22px;
              font-size:0.9rem; font-weight:600; text-decoration:none; display:inline-block;">
        Nova Ordem de Serviço
    </a>
</div>

<div style="display:flex; flex-direction:column; gap:10px;">
    @forelse($ordens as $ordem)
        @php
            $cores = ['ABERTA'=>'#f5a623','EM_ANDAMENTO'=>'#1a35a8','FINALIZADA'=>'#27ae60','CANCELADA'=>'#e74c3c'];
            $cor = $cores[$ordem->status] ?? '#999';
        @endphp
        <a href="{{ route('ordens.show', $ordem->id) }}"
           style="background:white; border-radius:10px; padding:16px 20px;
                  box-shadow:0 2px 8px rgba(0,0,0,0.06); text-decoration:none; color:inherit;
                  display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:12px;">
                <span style="width:12px; height:12px; background:{{ $cor }}; border-radius:50%; flex-shrink:0;"></span>
                <strong style="font-size:0.95rem; color:#222;">{{ $ordem->titulo }}</strong>
            </div>
            <span style="font-size:0.8rem; color:#999;">Criado: {{ $ordem->created_at->format('d/m/Y') }}</span>
        </a>
    @empty
        <div style="color:#999; font-size:0.9rem; text-align:center; padding:40px 0;">
            Você ainda não abriu nenhuma OS.
        </div>
    @endforelse
</div>

@endif

@endsection
