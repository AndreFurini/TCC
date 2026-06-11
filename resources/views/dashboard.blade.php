@extends('layouts.app')

@section('content')

@php $user = Auth::user(); @endphp

{{-- ============================================================
     ADMIN — Dashboard com filtro por setor
     ============================================================ --}}
@if($user->isAdmin())

<div style="display:flex; align-items:center; gap:12px; margin-bottom:20px; flex-wrap:wrap;">
    <form method="GET" action="{{ route('dashboard') }}" style="display:flex; gap:10px; align-items:center;">
        <select name="setor_id" onchange="this.form.submit()"
            style="padding:9px 14px; border:1.5px solid #1a35a8; border-radius:8px;
                   background:#1a35a8; color:white; font-weight:600; font-size:0.88rem; cursor:pointer;">
            <option value="">▼ Selecionar Setor</option>
            @foreach($setores as $setor)
                <option value="{{ $setor->id }}" {{ request('setor_id') == $setor->id ? 'selected' : '' }}>
                    {{ $setor->nome }}
                </option>
            @endforeach
        </select>
    </form>

    <div style="padding:9px 20px; border:1.5px solid #c5cde8; border-radius:8px;
                background:white; color:#444; font-size:0.88rem; font-weight:500;">
        Setor: {{ $setor_selecionado ? $setor_selecionado->nome : 'Todos' }}
    </div>
</div>

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

{{-- ============================================================
     COORDENADOR — Dashboard com setor fixo
     ============================================================ --}}
@elseif($user->isCoordenador())

<div style="margin-bottom:20px;">
    <div style="display:inline-block; padding:9px 20px; border:1.5px solid #c5cde8;
                border-radius:8px; background:white; color:#444; font-size:0.88rem; font-weight:500;">
        Setor: {{ $user->setor->nome ?? '—' }}
    </div>
</div>

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

@include('partials.cards-contagem')

{{-- ============================================================
     EXECUTOR — Dashboard com lista de OS
     ============================================================ --}}
@elseif($user->isExecutor())

@include('partials.cards-contagem')

<div style="margin-top:24px; display:flex; flex-direction:column; gap:10px;">
    @forelse($ordens as $ordem)
        <a href="{{ route('ordens.show', $ordem->id) }}"
           style="background:white; border-radius:10px; padding:16px 20px;
                  box-shadow:0 2px 8px rgba(0,0,0,0.06); text-decoration:none; color:inherit;
                  display:flex; align-items:center; justify-content:space-between;
                  transition: box-shadow 0.2s;">
            <strong style="font-size:0.95rem; color:#222;">{{ $ordem->titulo }}</strong>
            <span style="font-size:0.8rem; color:#999;">Criado: {{ $ordem->created_at->format('d/m/Y') }}</span>
        </a>
    @empty
        <div style="color:#999; font-size:0.9rem; text-align:center; padding:40px 0;">
            Nenhuma OS atribuída a você no momento.
        </div>
    @endforelse
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
