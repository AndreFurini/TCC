@extends('layouts.app')

@section('content')

<div style="display:flex; align-items:center; gap:12px; margin-bottom:20px;">
    <a href="{{ route('setores.index') }}"
       style="color:#1a35a8; font-size:1.1rem; text-decoration:none;" title="Voltar">
        <i class="bi bi-arrow-left-circle-fill"></i>
    </a>
    <h5 style="font-weight:700; color:#222; margin:0;">
        {{ isset($setor) ? 'Editar Setor' : 'Novo Setor' }}
    </h5>
</div>

<div style="display:flex; gap:20px; align-items:flex-start; flex-wrap:wrap;">

    {{-- FORMULÁRIO --}}
    <div style="background:white; border-radius:12px; overflow:hidden;
                box-shadow:0 2px 12px rgba(0,0,0,0.08); flex:1; min-width:280px; max-width:480px;">

        <div style="background:#1a35a8; color:white; font-weight:700;
                    font-size:1rem; padding:14px 24px; letter-spacing:0.5px;">
            <i class="bi bi-diagram-3"></i> Dados do Setor
        </div>

        <form action="{{ isset($setor) ? route('setores.update', $setor) : route('setores.store') }}"
              method="POST" style="padding:24px;">
            @csrf
            @if(isset($setor))
                @method('PUT')
            @endif

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

            {{-- Nome --}}
            <div style="margin-bottom:16px;">
                <label style="font-size:0.83rem; color:#444; display:block; margin-bottom:4px;">
                    Nome do Setor: <span style="color:#e74c3c;">*</span>
                </label>
                <input type="text" name="nome"
                       value="{{ old('nome', $setor->nome ?? '') }}"
                       placeholder="Ex: Tecnologia da Informação"
                       required
                       style="width:100%; padding:9px 12px;
                              border:1.5px solid {{ $errors->has('nome') ? '#e74c3c' : '#c5cde8' }};
                              border-radius:6px; font-size:0.93rem; outline:none;">
            </div>

            {{-- Responsável --}}
            <div style="margin-bottom:24px;">
                <label style="font-size:0.83rem; color:#444; display:block; margin-bottom:4px;">
                    Responsável:
                </label>
                <select name="responsavel_id"
                        style="width:100%; padding:9px 12px;
                               border:1.5px solid #c5cde8;
                               border-radius:6px; font-size:0.93rem; outline:none; background:white;">
                    <option value="">— Selecione um responsável —</option>
                    @foreach($usuarios as $usuario)
                        <option value="{{ $usuario->id }}"
                            {{ old('responsavel_id', $setor->responsavel_id ?? '') == $usuario->id ? 'selected' : '' }}>
                            {{ $usuario->name }} ({{ \App\Models\User::ROLES[$usuario->role] ?? $usuario->role }})
                        </option>
                    @endforeach
                </select>
                <div style="font-size:0.78rem; color:#999; margin-top:4px;">
                    Opcional — define quem é responsável por este setor.
                </div>
            </div>

            {{-- Botões --}}
            <div style="display:flex; gap:12px; justify-content:flex-end;">
                <a href="{{ route('setores.index') }}"
                   style="padding:10px 28px; border:1.5px solid #c5cde8; border-radius:6px;
                          color:#555; text-decoration:none; font-size:0.9rem;">
                    Cancelar
                </a>
                <button type="submit"
                        style="background:#27ae60; color:white; border:none; border-radius:6px;
                               padding:10px 36px; font-size:0.9rem; font-weight:600; cursor:pointer;">
                    {{ isset($setor) ? 'Salvar Alterações' : 'Criar Setor' }}
                </button>
            </div>
        </form>
    </div>

    {{-- USUÁRIOS DO SETOR — só na edição --}}
    @if(isset($setor))
        <div style="background:white; border-radius:12px; overflow:hidden;
                    box-shadow:0 2px 12px rgba(0,0,0,0.08); flex:1; min-width:260px; max-width:400px;">

            <div style="background:#1a35a8; color:white; font-weight:700;
                        font-size:1rem; padding:14px 24px; letter-spacing:0.5px;">
                <i class="bi bi-people"></i> Usuários do Setor
            </div>

            @if(isset($usuariosDoSetor) && $usuariosDoSetor->isNotEmpty())
                <div style="padding:8px 0;">
                    @foreach($usuariosDoSetor as $membro)
                        <div style="display:flex; align-items:center; justify-content:space-between;
                                    padding:12px 20px; border-bottom:1px solid #f0f2f8;">
                            <div>
                                <div style="font-weight:600; font-size:0.93rem; color:#222;">
                                    {{ $membro->name }}
                                </div>
                                <div style="font-size:0.78rem; color:#999;">
                                    @{{ $membro->username }}
                                </div>
                            </div>
                            @php
                                $badgeColors = [
                                    'admin'       => '#1a35a8',
                                    'coordenador' => '#6c5ce7',
                                    'executor'    => '#f39c12',
                                    'colaborador' => '#636e72',
                                ];
                                $bgColor = $badgeColors[$membro->role] ?? '#999';
                            @endphp
                            <span style="background:{{ $bgColor }}; color:white; font-size:0.75rem;
                                         font-weight:600; padding:3px 10px; border-radius:20px;">
                                {{ \App\Models\User::ROLES[$membro->role] ?? $membro->role }}
                            </span>
                        </div>
                    @endforeach
                </div>
                <div style="padding:10px 20px; font-size:0.78rem; color:#999;">
                    <i class="bi bi-info-circle"></i>
                    Para vincular usuários, edite o cadastro do usuário.
                </div>
            @else
                <div style="color:#999; font-size:0.88rem; text-align:center; padding:40px 20px;">
                    <i class="bi bi-people" style="font-size:1.8rem; display:block; margin-bottom:8px;"></i>
                    Nenhum usuário vinculado a este setor.
                </div>
            @endif
        </div>
    @endif

</div>

@endsection
