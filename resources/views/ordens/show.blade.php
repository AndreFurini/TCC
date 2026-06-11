@extends('layouts.app')

@section('content')

@php
    $user        = Auth::user();
    $cores       = \App\Models\OrdemServico::STATUS_CORES;
    $cor         = $cores[$ordem->status] ?? '#999';
    $urgCores    = ['BAIXA'=>'#27ae60','MEDIA'=>'#f39c12','ALTA'=>'#e67e22','URGENTE'=>'#e74c3c'];
    $corUrg      = $urgCores[$ordem->urgencia] ?? '#999';
    $podeCriar   = $ordem->criado_por === $user->id;
@endphp

<div style="display:flex; align-items:center; gap:12px; margin-bottom:20px;">
    <a href="{{ $user->isCoordenador() ? route('ordens.index') : route('dashboard') }}"
       style="color:#1a35a8; font-size:1.1rem; text-decoration:none;" title="Voltar">
        <i class="bi bi-arrow-left-circle-fill"></i>
    </a>
    <h5 style="font-weight:700; color:#222; margin:0; flex:1;">Detalhe da OS</h5>

    {{-- Badge de status --}}
    <span style="background:{{ $cor }}22; color:{{ $cor }}; font-size:0.8rem;
                 font-weight:700; padding:4px 14px; border-radius:20px;">
        {{ \App\Models\OrdemServico::STATUS[$ordem->status] ?? $ordem->status }}
    </span>

    {{-- Badge de urgência --}}
    <span style="background:{{ $corUrg }}22; color:{{ $corUrg }}; font-size:0.8rem;
                 font-weight:700; padding:4px 14px; border-radius:20px;">
        {{ \App\Models\OrdemServico::URGENCIA[$ordem->urgencia] ?? $ordem->urgencia }}
    </span>
</div>

@if(session('success'))
    <div style="background:#eafaf1; border:1px solid #27ae60; color:#1e8449;
                border-radius:8px; padding:12px 16px; margin-bottom:20px; font-size:0.88rem;">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
    </div>
@endif

<form action="{{ route('ordens.update', $ordem->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div style="background:white; border-radius:12px; overflow:hidden;
                box-shadow:0 2px 12px rgba(0,0,0,0.08); max-width:760px;">

        <div style="background:#1a35a8; color:white; font-weight:700;
                    font-size:1rem; padding:14px 24px; letter-spacing:0.5px;">
            <i class="bi bi-card-checklist"></i> Ordem de Serviço
        </div>

        <div style="padding:24px;">

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
                <label style="font-size:0.83rem; color:#444; display:block; margin-bottom:4px;">Título:</label>
                @if($user->isColaborador() && $podeCriar)
                    <input type="text" name="titulo" value="{{ old('titulo', $ordem->titulo) }}" required
                           style="width:100%; padding:9px 12px; border:1.5px solid #c5cde8;
                                  border-radius:6px; font-size:0.93rem; outline:none;">
                @else
                    <div style="padding:9px 12px; background:#f4f6fb; border-radius:6px;
                                font-size:0.93rem; color:#333;">
                        {{ $ordem->titulo }}
                    </div>
                @endif
            </div>

            {{-- Setor --}}
            <div style="margin-bottom:16px;">
                <label style="font-size:0.83rem; color:#444; display:block; margin-bottom:4px;">Setor:</label>
                <div style="padding:9px 12px; background:#f4f6fb; border-radius:6px;
                            font-size:0.93rem; color:#333;">
                    {{ $ordem->setor->nome ?? '—' }}
                </div>
            </div>

            {{-- Descrição --}}
            <div style="margin-bottom:16px;">
                <label style="font-size:0.83rem; color:#444; display:block; margin-bottom:4px;">Descrição:</label>
                @if($user->isColaborador() && $podeCriar)
                    <textarea name="descricao" rows="4" required
                              style="width:100%; padding:9px 12px; border:1.5px solid #c5cde8;
                                     border-radius:6px; font-size:0.93rem; outline:none; resize:vertical;">{{ old('descricao', $ordem->descricao) }}</textarea>
                @else
                    <div style="padding:9px 12px; background:#f4f6fb; border-radius:6px;
                                font-size:0.93rem; color:#333; white-space:pre-wrap; line-height:1.5;">
                        {{ $ordem->descricao }}
                    </div>
                @endif
            </div>

            {{-- Executante e Status — Coordenador edita, demais só visualizam --}}
            <div style="display:flex; gap:16px; margin-bottom:16px; flex-wrap:wrap;">
                <div style="flex:1; min-width:180px;">
                    <label style="font-size:0.83rem; color:#444; display:block; margin-bottom:4px;">Executante:</label>
                    @if($user->isCoordenador())
                        <select name="executor_id"
                                style="width:100%; padding:9px 12px; border:1.5px solid #c5cde8;
                                       border-radius:6px; font-size:0.93rem; outline:none; background:white;">
                            <option value="">— Sem executante —</option>
                            @foreach($executores as $exec)
                                <option value="{{ $exec->id }}"
                                    {{ old('executor_id', $ordem->executor_id) == $exec->id ? 'selected' : '' }}>
                                    {{ $exec->name }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <div style="padding:9px 12px; background:#f4f6fb; border-radius:6px;
                                    font-size:0.93rem; color:#333;">
                            {{ $ordem->executor->name ?? '—' }}
                        </div>
                    @endif
                </div>

                <div style="flex:1; min-width:180px;">
                    <label style="font-size:0.83rem; color:#444; display:block; margin-bottom:4px;">Status:</label>
                    @if($user->isCoordenador())
                        <select name="status" required
                                style="width:100%; padding:9px 12px; border:1.5px solid #c5cde8;
                                       border-radius:6px; font-size:0.93rem; outline:none; background:white;">
                            @foreach(\App\Models\OrdemServico::STATUS as $key => $label)
                                <option value="{{ $key }}"
                                    {{ old('status', $ordem->status) == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <div style="padding:9px 12px; background:#f4f6fb; border-radius:6px;
                                    font-size:0.93rem; color:#333;">
                            {{ \App\Models\OrdemServico::STATUS[$ordem->status] ?? $ordem->status }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Devolutiva — Coordenador e Executor podem escrever --}}
            <div style="margin-bottom:16px;">
                <label style="font-size:0.83rem; color:#444; display:block; margin-bottom:4px;">Devolutiva:</label>
                @if($user->isCoordenador() || $user->isExecutor())
                    <textarea name="devolutiva" rows="3"
                              placeholder="Comentários sobre a OS, andamento ou resposta ao solicitante..."
                              style="width:100%; padding:9px 12px; border:1.5px solid #c5cde8;
                                     border-radius:6px; font-size:0.93rem; outline:none; resize:vertical;">{{ old('devolutiva', $ordem->devolutiva) }}</textarea>
                @else
                    <div style="padding:9px 12px; background:#f4f6fb; border-radius:6px;
                                font-size:0.93rem; color:{{ $ordem->devolutiva ? '#333' : '#aaa' }}; white-space:pre-wrap;">
                        {{ $ordem->devolutiva ?: 'Sem devolutiva ainda.' }}
                    </div>
                @endif
            </div>

            {{-- Metadados: criado por / atualizado por / datas --}}
            <div style="background:#f8f9fd; border-radius:8px; padding:12px 16px;
                        font-size:0.8rem; color:#888; margin-bottom:20px;
                        display:flex; flex-wrap:wrap; gap:16px;">
                <span>
                    <i class="bi bi-person-plus"></i>
                    Criado por: <strong>{{ $ordem->criadoPor->name ?? '—' }}</strong>
                </span>
                <span>
                    <i class="bi bi-calendar3"></i>
                    Em: <strong>{{ $ordem->created_at->format('d/m/Y H:i') }}</strong>
                </span>
                @if($ordem->atualizadoPor && $ordem->atualizadoPor->id !== $ordem->criado_por)
                    <span>
                        <i class="bi bi-pencil"></i>
                        Atualizado por: <strong>{{ $ordem->atualizadoPor->name }}</strong>
                        em <strong>{{ $ordem->updated_at->format('d/m/Y H:i') }}</strong>
                    </span>
                @endif
            </div>

            {{-- Botões por role --}}
            <div style="display:flex; gap:12px; justify-content:flex-end; flex-wrap:wrap;">

                {{-- COORDENADOR: Salvar e Excluir (se criou) --}}
                @if($user->isCoordenador())
                    @if($podeCriar)
                        <form action="{{ route('ordens.destroy', $ordem->id) }}" method="POST"
                              onsubmit="return confirm('Excluir esta OS? Esta ação não pode ser desfeita.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    style="background:#e74c3c; color:white; border:none; border-radius:6px;
                                           padding:10px 28px; font-size:0.9rem; font-weight:600; cursor:pointer;">
                                Excluir
                            </button>
                        </form>
                    @endif
                    <button type="submit"
                            style="background:#27ae60; color:white; border:none; border-radius:6px;
                                   padding:10px 36px; font-size:0.9rem; font-weight:600; cursor:pointer;">
                        Salvar
                    </button>
                @endif

                {{-- EXECUTOR: Devolutiva + Finalizar --}}
                @if($user->isExecutor())
                    <button type="submit" name="finalizar" value="1"
                            @if($ordem->status === 'FINALIZADA') disabled @endif
                            style="background:{{ $ordem->status === 'FINALIZADA' ? '#ccc' : '#27ae60' }};
                                   color:white; border:none; border-radius:6px;
                                   padding:10px 36px; font-size:0.9rem; font-weight:600;
                                   cursor:{{ $ordem->status === 'FINALIZADA' ? 'not-allowed' : 'pointer' }};">
                        {{ $ordem->status === 'FINALIZADA' ? 'Finalizada' : 'Finalizar OS' }}
                    </button>
                    @if($ordem->status !== 'FINALIZADA')
                        <button type="submit"
                                style="background:#1a35a8; color:white; border:none; border-radius:6px;
                                       padding:10px 28px; font-size:0.9rem; font-weight:600; cursor:pointer;">
                            Salvar Devolutiva
                        </button>
                    @endif
                @endif

                {{-- COLABORADOR: Salvar (título/desc) e Excluir (se criou) --}}
                @if($user->isColaborador())
                    @if($podeCriar)
                        <form action="{{ route('ordens.destroy', $ordem->id) }}" method="POST"
                              onsubmit="return confirm('Excluir esta OS?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    style="background:#e74c3c; color:white; border:none; border-radius:6px;
                                           padding:10px 28px; font-size:0.9rem; font-weight:600; cursor:pointer;">
                                Excluir
                            </button>
                        </form>
                        <button type="submit"
                                style="background:#27ae60; color:white; border:none; border-radius:6px;
                                       padding:10px 36px; font-size:0.9rem; font-weight:600; cursor:pointer;">
                            Salvar
                        </button>
                    @endif
                @endif

            </div>
        </div>
    </div>
</form>

@endsection
