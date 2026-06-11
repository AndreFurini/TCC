@extends('layouts.app')

@section('content')

<div style="background:white; border-radius:12px; overflow:hidden;
            box-shadow:0 2px 12px rgba(0,0,0,0.08); max-width:700px;">

    <div style="background:#1a35a8; color:white; font-weight:700;
                font-size:1rem; padding:14px 24px; letter-spacing:0.5px;">
        Dados do Usuário
    </div>

    <form action="{{ route('usuarios.store') }}" method="POST" style="padding:24px;">
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

        {{-- Nome Completo --}}
        <div style="margin-bottom:16px;">
            <label style="font-size:0.83rem; color:#444; display:block; margin-bottom:4px;">Nome Completo:</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   style="width:100%; padding:9px 12px; border:1.5px solid {{ $errors->has('name') ? '#e74c3c' : '#c5cde8' }};
                          border-radius:6px; font-size:0.93rem; outline:none;">
        </div>

        {{-- Username + Email --}}
        <div style="display:flex; gap:16px; margin-bottom:16px;">
            <div style="flex:1;">
                <label style="font-size:0.83rem; color:#444; display:block; margin-bottom:4px;">Nome de Usuário:</label>
                <input type="text" name="username" value="{{ old('username') }}" required
                       style="width:100%; padding:9px 12px; border:1.5px solid {{ $errors->has('username') ? '#e74c3c' : '#c5cde8' }};
                              border-radius:6px; font-size:0.93rem; outline:none;">
            </div>
            <div style="flex:1;">
                <label style="font-size:0.83rem; color:#444; display:block; margin-bottom:4px;">E-mail:</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       style="width:100%; padding:9px 12px; border:1.5px solid {{ $errors->has('email') ? '#e74c3c' : '#c5cde8' }};
                              border-radius:6px; font-size:0.93rem; outline:none;">
            </div>
        </div>

        {{-- Senha + Função + Setor --}}
        <div style="display:flex; gap:16px; margin-bottom:16px;">
            <div style="flex:1;">
                <label style="font-size:0.83rem; color:#444; display:block; margin-bottom:4px;">Senha:</label>
                <input type="password" name="password" id="senhaInput" oninput="validarSenha()" required
                       style="width:100%; padding:9px 12px; border:1.5px solid {{ $errors->has('password') ? '#e74c3c' : '#c5cde8' }};
                              border-radius:6px; font-size:0.93rem; outline:none;">
            </div>
            <div style="flex:1;">
                <label style="font-size:0.83rem; color:#444; display:block; margin-bottom:4px;">Função:</label>
                <select name="role" required
                        style="width:100%; padding:9px 12px; border:1.5px solid {{ $errors->has('role') ? '#e74c3c' : '#c5cde8' }};
                               border-radius:6px; font-size:0.93rem; outline:none; background:white;">
                    <option value="">Selecionar...</option>
                    @foreach($roles as $key => $label)
                        @if($key !== 'admin')
                            <option value="{{ $key }}" {{ old('role') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>
        </div>

        <div style="display:flex; gap:16px; margin-bottom:20px;">
            <div style="flex:1;">
                <label style="font-size:0.83rem; color:#444; display:block; margin-bottom:4px;">Confirmar Senha:</label>
                <input type="password" name="password_confirmation" required
                       style="width:100%; padding:9px 12px; border:1.5px solid #c5cde8;
                              border-radius:6px; font-size:0.93rem; outline:none;">
            </div>
            <div style="flex:1;">
                <label style="font-size:0.83rem; color:#444; display:block; margin-bottom:4px;">Setor:</label>
                <select name="setor_id" required
                        style="width:100%; padding:9px 12px; border:1.5px solid {{ $errors->has('setor_id') ? '#e74c3c' : '#c5cde8' }};
                               border-radius:6px; font-size:0.93rem; outline:none; background:white;">
                    <option value="">Selecionar...</option>
                    @foreach($setores as $setor)
                        <option value="{{ $setor->id }}" {{ old('setor_id') == $setor->id ? 'selected' : '' }}>
                            {{ $setor->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Regras de senha --}}
        <div style="background:#f4f6fb; border-radius:8px; padding:12px 16px;
                    font-size:0.82rem; color:#555; margin-bottom:20px;">
            <ul style="margin:0; padding-left:18px;">
                <li id="rule-len"     class="invalid">Mínimo 10 dígitos</li>
                <li id="rule-upper"   class="invalid">Pelo menos 1 letra maiúscula (A-Z)</li>
                <li id="rule-lower"   class="invalid">Pelo menos 1 letra minúscula (a-z)</li>
                <li id="rule-number"  class="invalid">Pelo menos 1 número (0-9)</li>
                <li id="rule-special" class="invalid">Pelo menos 1 caractere especial (@,#,$,%,&,*)</li>
                <li id="rule-space"   class="invalid">Sem espaços</li>
            </ul>
        </div>

        <div style="display:flex; gap:12px; justify-content:flex-end;">
            <a href="{{ route('usuarios.index') }}"
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

<style>
    li.valid   { color: #27ae60; }
    li.invalid { color: #aaa; }
</style>

@push('scripts')
<script>
function validarSenha() {
    const senha = document.getElementById('senhaInput').value;
    const regras = {
        'rule-len':     senha.length >= 10,
        'rule-upper':   /[A-Z]/.test(senha),
        'rule-lower':   /[a-z]/.test(senha),
        'rule-number':  /[0-9]/.test(senha),
        'rule-special': /[@#$%&*!]/.test(senha),
        'rule-space':   !/\s/.test(senha) && senha.length > 0,
    };
    for (const [id, valido] of Object.entries(regras)) {
        document.getElementById(id).className = valido ? 'valid' : 'invalid';
    }
}
</script>
@endpush

@endsection
