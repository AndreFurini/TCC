@extends('layouts.app')

@section('content')

@php
    $user        = Auth::user();
    $cores       = \App\Models\OrdemServico::STATUS_CORES;
    $cor         = $cores[$ordem->status] ?? '#999';
    $urgCores    = ['BAIXA'=>'#27ae60','MEDIA'=>'#f39c12','ALTA'=>'#e67e22','URGENTE'=>'#e74c3c'];
    $corUrg      = $urgCores[$ordem->urgencia] ?? '#999';
    $podeCriar    = $ordem->criado_por === $user->id;
    $osEmAberto   = !in_array($ordem->status, ['FINALIZADA', 'CANCELADA'], true);

    // Executor "formalmente atribuído": é o executor_id de fato da OS
    // (seja porque o coordenador atribuiu, seja porque ele mesmo assumiu).
    $souExecutorFormal = $user->isExecutor() && $ordem->executor_id === $user->id;

    // Pode "assumir": é do setor dele, ainda sem executante, e a OS não
    // está encerrada. Se ele também for quem criou (pediu algo pro próprio
    // setor), assumir prevalece sobre editar como solicitante.
    $podeAssumir = $user->isExecutor()
        && $ordem->executor_id === null
        && $ordem->setor_id === $user->setor_id
        && $osEmAberto;

    $podeLiberar = $souExecutorFormal && $osEmAberto;

    // Executor "como solicitante": só criou a OS — não é o executor formal
    // nem candidato a assumir (ex.: pediu algo pra outro setor, ou o setor
    // dele já tem outro executor cuidando). Editar título/descrição, igual colaborador.
    $executorComoSolicitante = $user->isExecutor() && !$souExecutorFormal && !$podeAssumir;
    $podeEditarTituloDescricao = ($user->isColaborador() || $executorComoSolicitante) && $podeCriar;
@endphp

<div style="display:flex; align-items:center; gap:12px; margin-bottom:20px;">
    <a href="{{ ($user->isCoordenador() || $user->isAdmin()) ? route('ordens.index') : route('dashboard') }}"
       style="color:#1a35a8; font-size:1.1rem; text-decoration:none;" title="Voltar">
        <i class="bi bi-arrow-left-circle-fill"></i>
    </a>
    <h5 style="font-weight:700; color:#222; margin:0;">Detalhe da OS</h5>
</div>

@if($podeAssumir)
    <div style="background:#eef3ff; border:1px solid #1a35a8; border-radius:10px;
                padding:16px 20px; margin-bottom:20px; max-width:760px;
                display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap;">
        <div style="font-size:0.88rem; color:#1a35a8;">
            <i class="bi bi-info-circle"></i>
            Esta OS é do seu setor e ainda não tem executante definido.
        </div>
        <form action="{{ route('ordens.assumir', $ordem->id) }}" method="POST" style="margin:0;">
            @csrf
            @method('PATCH')
            <button type="submit"
                    style="background:#1a35a8; color:white; border:none; border-radius:6px;
                           padding:10px 24px; font-size:0.9rem; font-weight:600; cursor:pointer; white-space:nowrap;">
                <i class="bi bi-hand-index-thumb"></i> Assumir esta OS
            </button>
        </form>
    </div>
@endif

<form action="{{ route('ordens.update', $ordem->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div style="background:white; border-radius:12px; overflow:hidden;
                box-shadow:0 2px 12px rgba(0,0,0,0.08); max-width:760px;">

        <div style="background:#1a35a8; color:white; font-weight:700;
                    font-size:1rem; padding:14px 24px; letter-spacing:0.5px;
                    display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
            <span><i class="bi bi-card-checklist"></i> Ordem de Serviço</span>

            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                {{-- Situação --}}
                <span style="background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.35);
                             color:white; font-size:0.76rem; font-weight:700; letter-spacing:normal;
                             padding:4px 12px 4px 10px; border-radius:20px;
                             display:inline-flex; align-items:center; gap:6px;">
                    <span style="width:8px; height:8px; border-radius:50%; background:{{ $cor }};
                                 box-shadow:0 0 0 2px rgba(255,255,255,0.55); display:inline-block;"></span>
                    {{ \App\Models\OrdemServico::STATUS[$ordem->status] ?? $ordem->status }}
                </span>

                {{-- Prioridade/urgência --}}
                <span style="background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.35);
                             color:white; font-size:0.76rem; font-weight:700; letter-spacing:normal;
                             padding:4px 12px 4px 10px; border-radius:20px;
                             display:inline-flex; align-items:center; gap:6px;">
                    <span style="width:8px; height:8px; border-radius:50%; background:{{ $corUrg }};
                                 box-shadow:0 0 0 2px rgba(255,255,255,0.55); display:inline-block;"></span>
                    {{ \App\Models\OrdemServico::URGENCIA[$ordem->urgencia] ?? $ordem->urgencia }}
                </span>
            </div>
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
                @if($podeEditarTituloDescricao)
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
                @if($podeEditarTituloDescricao)
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

            {{-- Data de Entrega Prevista (definida no cadastro) --}}
            <div style="margin-bottom:16px;">
                <label style="font-size:0.83rem; color:#444; display:block; margin-bottom:4px;">Data de Entrega Prevista:</label>
                <div style="padding:9px 12px; background:#f4f6fb; border-radius:6px;
                            font-size:0.93rem; color:{{ $ordem->data_entrega ? '#333' : '#aaa' }};">
                    {{ $ordem->data_entrega ? $ordem->data_entrega->format('d/m/Y') : 'Não informada' }}
                </div>
            </div>

            {{-- Devolutiva — Coordenador e Executor podem escrever --}}
            <div style="margin-bottom:16px;">
                <label style="font-size:0.83rem; color:#444; display:block; margin-bottom:4px;">Devolutiva:</label>
                @if($user->isCoordenador() || $souExecutorFormal)
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
                @if($ordem->alterada_pelo_criador_em)
                    <span>
                        <i class="bi bi-clock-history"></i>
                        Última alteração pelo solicitante:
                        <strong>{{ $ordem->alterada_pelo_criador_em->format('d/m/Y H:i') }}</strong>
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

                {{-- EXECUTOR formalmente atribuído: Devolutiva + Finalizar --}}
                @if($souExecutorFormal)
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

                {{-- COLABORADOR, ou EXECUTOR como solicitante: Salvar (título/desc) e Excluir (se criou) --}}
                @if($user->isColaborador() || $executorComoSolicitante)
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

@if($podeLiberar)
    <div style="max-width:760px; display:flex; justify-content:flex-end; margin-top:12px;">
        <form action="{{ route('ordens.liberar', $ordem->id) }}" method="POST"
              onsubmit="return confirm('Se desvincular desta OS? Ela volta pra fila do seu setor, sem executante (status Aberta).')">
            @csrf
            @method('PATCH')
            <button type="submit"
                    style="background:#fff; color:#e67e22; border:1.5px solid #e67e22; border-radius:6px;
                           padding:10px 28px; font-size:0.9rem; font-weight:600; cursor:pointer;">
                <i class="bi bi-box-arrow-right"></i> Liberar OS
            </button>
        </form>
    </div>
@endif

@endsection
