<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro da Empresa — CoordenaTask</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            background-color: #eef1f7;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
            padding: 32px 16px;
        }

        .cadastro-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
            width: 100%;
            max-width: 640px;
            overflow: hidden;
        }

        .section-header {
            background-color: #1a35a8;
            color: white;
            font-weight: 700;
            font-size: 1rem;
            padding: 12px 24px;
            letter-spacing: 0.5px;
        }

        .section-body {
            padding: 24px 24px 8px 24px;
        }

        .divider-blue {
            height: 8px;
            background-color: #1a35a8;
        }

        label {
            font-size: 0.85rem;
            color: #444;
            margin-bottom: 4px;
            display: block;
        }

        input, select {
            width: 100%;
            padding: 9px 12px;
            border: 1.5px solid #c5cde8;
            border-radius: 6px;
            font-size: 0.93rem;
            margin-bottom: 16px;
            outline: none;
            transition: border-color 0.2s;
            background: #fff;
        }

        input:focus, select:focus {
            border-color: #1a35a8;
        }

        input.is-invalid {
            border-color: #e74c3c;
        }

        .invalid-feedback {
            color: #e74c3c;
            font-size: 0.8rem;
            margin-top: -12px;
            margin-bottom: 12px;
            display: block;
        }

        .row-fields {
            display: flex;
            gap: 16px;
        }

        .row-fields > div {
            flex: 1;
        }

        .password-wrap {
            display: flex;
            gap: 16px;
        }

        .password-wrap > div {
            flex: 1;
        }

        /* Checklist de senha */
        .password-rules {
            background: #f4f6fb;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 0.82rem;
            color: #555;
            margin-bottom: 16px;
        }

        .password-rules ul {
            margin: 0;
            padding-left: 18px;
        }

        .password-rules li {
            margin-bottom: 3px;
        }

        .password-rules li.valid {
            color: #27ae60;
        }

        .password-rules li.invalid {
            color: #999;
        }

        /* Botão salvar */
        .footer-actions {
            padding: 16px 24px 24px 24px;
            display: flex;
            justify-content: flex-end;
        }

        .btn-salvar {
            background-color: #27ae60;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 11px 48px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-salvar:hover {
            background-color: #219150;
        }

        .btn-voltar {
            background: none;
            color: #1a35a8;
            border: none;
            font-size: 0.88rem;
            cursor: pointer;
            margin-right: 16px;
            text-decoration: underline;
        }

        .error-msg {
            background: #fdecea;
            color: #c0392b;
            border-radius: 6px;
            padding: 10px 16px;
            font-size: 0.85rem;
            margin: 16px 24px 0;
        }
    </style>
</head>
<body>

<div class="cadastro-card">

    <form action="{{ route('cadastro.empresa.store') }}" method="POST" id="formCadastro">
        @csrf

        @if($errors->any())
            <div class="error-msg">
                <ul style="margin:0; padding-left:18px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- SEÇÃO: DADOS DA EMPRESA -->
        <div class="section-header">Dados da Empresa</div>
        <div class="section-body">

            <label>Nome da Empresa:</label>
            <input type="text" name="nome_empresa" value="{{ old('nome_empresa') }}"
                   class="{{ $errors->has('nome_empresa') ? 'is-invalid' : '' }}" required>
            @error('nome_empresa') <span class="invalid-feedback">{{ $message }}</span> @enderror

            <label>CNPJ: <span style="color:#999; font-weight:400">(opcional)</span></label>
            <input type="text" name="cnpj" value="{{ old('cnpj') }}" placeholder="00.000.000/0000-00"
                   style="max-width: 260px;">
        </div>

        <!-- DIVISOR -->
        <div class="divider-blue"></div>

        <!-- SEÇÃO: DADOS DO ADMINISTRADOR -->
        <div class="section-body">

            <label>Nome Completo:</label>
            <input type="text" name="nome_completo" value="{{ old('nome_completo') }}"
                   class="{{ $errors->has('nome_completo') ? 'is-invalid' : '' }}" required>
            @error('nome_completo') <span class="invalid-feedback">{{ $message }}</span> @enderror

            <div class="row-fields">
                <div>
                    <label>Nome de Usuário:</label>
                    <input type="text" name="username" value="{{ old('username') }}"
                           class="{{ $errors->has('username') ? 'is-invalid' : '' }}" required>
                    @error('username') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label>E-mail:</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="{{ $errors->has('email') ? 'is-invalid' : '' }}" required>
                    @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="password-wrap">
                <div>
                    <label>Senha:</label>
                    <input type="password" name="password" id="senhaInput"
                           class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                           oninput="validarSenha()" required>
                    @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror

                    <label>Confirmar Senha:</label>
                    <input type="password" name="password_confirmation"
                           class="{{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}" required>
                </div>

                <div class="password-rules">
                    <ul>
                        <li id="rule-len" class="invalid">Mínimo 10 dígitos</li>
                        <li id="rule-upper" class="invalid">Pelo menos 1 letra maiúscula (A-Z)</li>
                        <li id="rule-lower" class="invalid">Pelo menos 1 letra minúscula (a-z)</li>
                        <li id="rule-number" class="invalid">Pelo menos 1 número (0-9)</li>
                        <li id="rule-special" class="invalid">Pelo menos 1 caractere especial (@,#,$,%,&,*)</li>
                        <li id="rule-space" class="invalid">Sem espaços</li>
                    </ul>
                </div>
            </div>

        </div>

        <!-- AÇÕES -->
        <div class="footer-actions">
            <a href="{{ route('login') }}" class="btn-voltar">Voltar ao Login</a>
            <button type="submit" class="btn-salvar">Salvar</button>
        </div>

    </form>
</div>

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
        const el = document.getElementById(id);
        el.classList.toggle('valid', valido);
        el.classList.toggle('invalid', !valido);
    }
}
</script>

</body>
</html>
