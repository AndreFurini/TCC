<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoordenaTask</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background-color: #eef1f7;
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
        }

        /* ---- TOPBAR ---- */
        .topbar {
            background: #ffffff;
            padding: 10px 28px;
            display: flex;
            align-items: center;
            gap: 12px 24px;
            border-bottom: 1px solid #dde3f0;
            position: sticky;
            top: 0;
            z-index: 100;
            flex-wrap: wrap;
        }

        .topbar-brand { flex-shrink: 0; }
        .topbar-title { font-size: 1rem; font-weight: 700; color: #1a35a8; line-height: 1.2; }
        .topbar-code  { font-size: 0.75rem; color: #888; font-weight: 500; }

        /* ---- TOP NAV (módulos) ---- */
        .topnav {
            display: flex;
            align-items: center;
            gap: 4px;
            flex: 1;
            min-width: 0;
            overflow-x: auto;
        }

        .topnav a {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            white-space: nowrap;
            color: #5b6478;
            text-decoration: none;
            font-size: 0.82rem;
            font-weight: 600;
            letter-spacing: 0.2px;
            padding: 8px 14px;
            border-radius: 8px;
            transition: color 0.2s, background 0.2s;
        }

        .topnav a i { font-size: 1.05rem; }

        .topnav a:hover { color: #1a35a8; background: #eef1f7; }
        .topnav a.active { color: #1a35a8; background: #eef3ff; }

        .topnav-divider {
            width: 1px;
            height: 22px;
            background: #dde3f0;
            margin: 0 6px;
            flex-shrink: 0;
        }

        /* ---- USUÁRIO (direita da barra) ---- */
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
            margin-left: auto;
        }

        .topbar-user {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
            text-align: right;
        }
        .topbar-user-name { font-size: 0.85rem; color: #333; font-weight: 600; }
        .topbar-user-role { font-size: 0.72rem; color: #8a93a5; }

        .avatar {
            width: 36px;
            height: 36px;
            background: #1a35a8;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .btn-logout {
            font-size: 0.8rem;
            color: #888;
            background: none;
            border: 1px solid #dde3f0;
            border-radius: 6px;
            padding: 4px 10px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-logout:hover { color: #e74c3c; border-color: #e74c3c; }

        /* ---- PAGE BODY ---- */
        .main-content { min-height: calc(100vh - 57px); }
        .page-body { padding: 28px; }

        /* ---- BOTÕES DE AÇÃO (tamanho padrão: usuários e setores) ---- */
        .toolbar-acoes {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .btn-acao {
            min-width: 150px;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 0.88rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 1px solid transparent;
            cursor: pointer;
            transition: opacity 0.2s, background 0.2s, border-color 0.2s;
        }

        .btn-acao--primary { background: #1a35a8; color: #fff; }
        .btn-acao--primary:hover { background: #142a86; }

        .btn-acao--neutro { background: #fff; color: #1a35a8; border-color: #c5cde8; }
        .btn-acao--neutro:hover { background: #eef3ff; }

        .btn-acao--danger { background: #fff; color: #e74c3c; border-color: #f1b0a8; }
        .btn-acao--danger:hover { background: #fdecea; }

        .btn-acao--warning { background: #fff; color: #a86a00; border-color: #f0cd8f; }
        .btn-acao--warning:hover { background: #fef6e7; }

        .btn-acao--success { background: #fff; color: #1e8449; border-color: #a3d9b8; }
        .btn-acao--success:hover { background: #eafaf1; }

        .btn-acao:disabled,
        .btn-acao.is-disabled {
            opacity: 0.4;
            cursor: default;
            pointer-events: none;
        }

        /* ---- "Visualizar inativos" ---- */
        .check-inativos {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-left: 4px;
            font-size: 0.85rem;
            color: #555;
            cursor: pointer;
            user-select: none;
        }
        .check-inativos input { width: 15px; height: 15px; cursor: pointer; }

        /* ---- LINHAS SELECIONÁVEIS (listas de usuários e setores) ---- */
        .linha-selecionavel {
            cursor: pointer;
            outline: 2px solid transparent;
            outline-offset: -2px;
            transition: outline-color 0.15s, box-shadow 0.15s;
        }
        .linha-selecionavel:hover { box-shadow: 0 4px 14px rgba(0, 0, 0, 0.10); }
        .linha-selecionavel.selecionado {
            outline-color: #1a35a8;
            box-shadow: 0 4px 16px rgba(26, 53, 168, 0.18);
        }
        .linha-inativa { opacity: 0.55; }

        /* ---- TABELA DE ORDENS DE SERVIÇO (lista + dashboard do executor) ---- */
        .tabela-os-wrap {
            overflow-x: auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }
        .tabela-os { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        .tabela-os th, .tabela-os td { padding: 12px 14px; text-align: left; white-space: nowrap; }
        .tabela-os thead th {
            background: #f4f6fb; color: #5b6478; font-weight: 700;
            font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.3px;
            border-bottom: 1px solid #e3e8f4;
        }
        .tabela-os tbody tr {
            border-bottom: 1px solid #eef1f7; cursor: pointer;
            transition: background 0.15s;
        }
        .tabela-os tbody tr:last-child { border-bottom: none; }
        .tabela-os tbody tr:hover { background: #f7f9fd; }
        .tabela-os td.col-titulo {
            white-space: normal; max-width: 280px;
            font-weight: 600; color: #222;
        }
        .tabela-os .badge-tab {
            display: inline-block; font-size: 0.72rem; font-weight: 700;
            padding: 3px 10px; border-radius: 20px; white-space: nowrap;
        }
        .tabela-os td.muted { color: #999; }

        .badge-inativo {
            background: #f0f2f8;
            color: #8a93a5;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.3px;
            padding: 2px 8px;
            border-radius: 20px;
            margin-left: 8px;
            text-transform: uppercase;
            vertical-align: middle;
        }

        /* ---- MODAL SIMPLES (aviso + OK) ---- */
        .modal-simples {
            position: fixed;
            inset: 0;
            z-index: 200;
            background: rgba(20, 25, 45, 0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-simples[hidden] { display: none; }
        .modal-simples-box {
            background: #fff;
            border-radius: 12px;
            padding: 28px 26px;
            max-width: 380px;
            width: 100%;
            text-align: center;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.25);
        }
        .modal-simples-icone { font-size: 2rem; color: #e67e22; margin-bottom: 10px; }
        .modal-simples-icone.perigo { color: #e74c3c; }
        .modal-simples-box p {
            font-size: 0.9rem;
            color: #444;
            margin: 0 0 20px;
            line-height: 1.5;
        }
        .modal-simples-acoes {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        @media (max-width: 640px) {
            .topbar { padding: 10px 16px; }
            .topbar-right { order: 2; }
            .topnav { order: 3; width: 100%; flex: none; }
            .page-body { padding: 18px; }
        }

        /* ---- ALERTS ---- */
        .alert-codigo {
            background: #eef3ff;
            border: 1px solid #1a35a8;
            color: #1a35a8;
            border-radius: 8px;
            padding: 14px 20px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        .alert-codigo strong { font-size: 1.1rem; letter-spacing: 2px; }

        .alert-success-custom {
            background: #eafaf1;
            border: 1px solid #27ae60;
            color: #1e8449;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 0.88rem;
        }

        .alert-warning-custom {
            background: #fef6e7;
            border: 1px solid #f5a623;
            color: #a86a00;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 0.88rem;
        }

        .alert-error-custom {
            background: #fdecea;
            border: 1px solid #e74c3c;
            color: #c0392b;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 0.88rem;
        }
    </style>
    @stack('styles')
</head>
<body>

@php $user = Auth::user(); @endphp

<!-- TOPBAR -->
<header class="topbar">

    <div class="topbar-brand">
        <div class="topbar-title">CoordenaTask</div>
        <div class="topbar-code">Código da Empresa: {{ $user->empresa->codigo_empresa ?? '' }}</div>
    </div>

    <!-- MÓDULOS -->
    <nav class="topnav">
        @if($user->isAdmin())
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-fill"></i> Início
            </a>
            <span class="topnav-divider"></span>
            <a href="{{ route('ordens.index') }}" class="{{ request()->routeIs('ordens.*') ? 'active' : '' }}">
                <i class="bi bi-card-checklist"></i> Ordens
            </a>
            <a href="{{ route('usuarios.index') }}" class="{{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i> Usuários
            </a>
            <a href="{{ route('setores.index') }}" class="{{ request()->routeIs('setores.*') ? 'active' : '' }}">
                <i class="bi bi-diagram-3-fill"></i> Setores
            </a>

        @elseif($user->isCoordenador())
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-fill"></i> Início
            </a>
            <span class="topnav-divider"></span>
            <a href="{{ route('ordens.index') }}" class="{{ request()->routeIs('ordens.*') ? 'active' : '' }}">
                <i class="bi bi-card-checklist"></i> Ordens
            </a>
            <a href="{{ route('usuarios.index') }}" class="{{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i> Usuários
            </a>
            <a href="{{ route('setores.index') }}" class="{{ request()->routeIs('setores.*') ? 'active' : '' }}">
                <i class="bi bi-diagram-3-fill"></i> Setor
            </a>

        @elseif($user->isExecutor())
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-fill"></i> Início
            </a>
            <span class="topnav-divider"></span>
            <a href="{{ route('ordens.historico') }}" class="{{ request()->routeIs('ordens.*') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i> Histórico
            </a>

        @elseif($user->isColaborador())
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-fill"></i> Início
            </a>
            <span class="topnav-divider"></span>
            <a href="{{ route('ordens.historico') }}" class="{{ request()->routeIs('ordens.*') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i> Histórico
            </a>
        @endif
    </nav>

    <!-- USUÁRIO + PERFIL -->
    <div class="topbar-right">
        <div class="topbar-user">
            <span class="topbar-user-name">{{ $user->name }}</span>
            <span class="topbar-user-role">{{ \App\Models\User::ROLES[$user->role] ?? $user->role }}</span>
        </div>
        <div class="avatar"><i class="bi bi-person-fill"></i></div>
        <form action="{{ route('auth.logout') }}" method="POST" style="margin:0">
            @csrf
            <button type="submit" class="btn-logout">Sair</button>
        </form>
    </div>

</header>

<!-- CONTEÚDO -->
<main class="main-content">
    <div class="page-body">

        @if(session('codigo_empresa'))
            <div class="alert-codigo">
                <i class="bi bi-info-circle"></i>
                Empresa cadastrada! Código de acesso: <strong>{{ session('codigo_empresa') }}</strong>
                — anote, ele será usado no login de todos os usuários.
            </div>
        @endif

        @if(session('success'))
            <div class="alert-success-custom">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('warning'))
            <div class="alert-warning-custom">
                <i class="bi bi-exclamation-triangle"></i> {{ session('warning') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert-error-custom">
                <i class="bi bi-x-circle"></i> {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
