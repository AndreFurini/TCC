<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — CoordenaTask</title>
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
        }

        .login-card {
            display: flex;
            width: 560px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
        }

        /* Painel esquerdo — Login */
        .panel-login {
            flex: 1;
            background: #ffffff;
            padding: 40px 32px;
            border: 2px solid #1a35a8;
            border-right: none;
            border-radius: 12px 0 0 12px;
        }

        .panel-login h2 {
            color: #1a35a8;
            font-weight: 700;
            font-size: 1.5rem;
            letter-spacing: 1px;
            text-align: center;
            margin-bottom: 28px;
        }

        .panel-login label {
            font-size: 0.85rem;
            color: #444;
            margin-bottom: 4px;
            display: block;
        }

        .panel-login input {
            width: 100%;
            padding: 8px 12px;
            border: 1.5px solid #c5cde8;
            border-radius: 6px;
            font-size: 0.95rem;
            margin-bottom: 16px;
            outline: none;
            transition: border-color 0.2s;
        }

        .panel-login input:focus {
            border-color: #1a35a8;
        }

        .btn-login {
            width: 100%;
            background-color: #1a35a8;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 6px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 4px;
            transition: background 0.2s;
        }

        .btn-login:hover {
            background-color: #142c90;
        }

        .error-msg {
            background: #fdecea;
            color: #c0392b;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 0.85rem;
            margin-bottom: 14px;
        }

        /* Painel direito — Cadastrar */
        .panel-cadastro {
            width: 210px;
            background-color: #1a35a8;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 24px;
            border-radius: 0 12px 12px 0;
            gap: 20px;
        }

        .panel-cadastro h2 {
            color: #ffffff;
            font-weight: 700;
            font-size: 1.4rem;
            letter-spacing: 1px;
            text-align: center;
            margin: 0;
        }

        .btn-cadastro {
            background: #ffffff;
            color: #1a35a8;
            border: none;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            display: block;
            width: 100%;
            transition: background 0.2s;
        }

        .btn-cadastro:hover {
            background: #e8edf8;
            color: #1a35a8;
        }
    </style>
</head>
<body>

<div class="login-card">

    <!-- PAINEL LOGIN -->
    <div class="panel-login">
        <h2>LOGIN</h2>

        @if(session('error'))
            <div class="error-msg">{{ session('error') }}</div>
        @endif

        <form action="{{ route('auth.login') }}" method="POST">
            @csrf

            <label>Código da Empresa:</label>
            <input type="text" name="codigo_empresa" value="{{ old('codigo_empresa') }}" required autofocus>

            <label>Nome de Usuário:</label>
            <input type="text" name="username" value="{{ old('username') }}" required>

            <label>Senha:</label>
            <input type="password" name="password" required>

            <button type="submit" class="btn-login">Entrar</button>
        </form>
    </div>

    <!-- PAINEL CADASTRAR -->
    <div class="panel-cadastro">
        <h2>CADASTRAR</h2>
        <a href="{{ route('cadastro.empresa') }}" class="btn-cadastro">
            Iniciar Cadastro<br>da Empresa
        </a>
    </div>

</div>

</body>
</html>
