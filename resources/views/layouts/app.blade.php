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
            display: flex;
        }

        /* ---- SIDEBAR ---- */
        .sidebar {
            width: 72px;
            background-color: #1a35a8;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px 0;
            position: fixed;
            top: 0; left: 0;
            z-index: 100;
        }

        .sidebar a {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: rgba(255,255,255,0.65);
            text-decoration: none;
            font-size: 0.62rem;
            font-weight: 600;
            letter-spacing: 0.3px;
            padding: 12px 8px;
            width: 100%;
            text-align: center;
            transition: color 0.2s, background 0.2s;
            gap: 4px;
        }

        .sidebar a i { font-size: 1.3rem; }

        .sidebar a:hover,
        .sidebar a.active {
            color: #ffffff;
            background: rgba(255,255,255,0.12);
        }

        .sidebar-divider {
            width: 36px;
            height: 1px;
            background: rgba(255,255,255,0.2);
            margin: 8px auto;
        }

        /* ---- MAIN ---- */
        .main-content {
            margin-left: 72px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ---- TOPBAR ---- */
        .topbar {
            background: #ffffff;
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #dde3f0;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar-title { font-size: 1rem; font-weight: 700; color: #1a35a8; }
        .topbar-code  { font-size: 0.8rem; color: #888; font-weight: 500; }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

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
        .page-body { padding: 28px; flex: 1; }

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
    </style>
    @stack('styles')
</head>
<body>

@php $user = Auth::user(); @endphp

<!-- SIDEBAR -->
<nav class="sidebar">

    @if($user->isAdmin())
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-fill"></i> Dashboard
        </a>
        <div class="sidebar-divider"></div>
        <a href="{{ route('usuarios.index') }}" class="{{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
            <i class="bi bi-people-fill"></i> Usuários
        </a>
        <a href="{{ route('setores.index') }}" class="{{ request()->routeIs('setores.*') ? 'active' : '' }}">
            <i class="bi bi-diagram-3-fill"></i> Setores
        </a>

    @elseif($user->isCoordenador())
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-fill"></i> Dashboard
        </a>
        <div class="sidebar-divider"></div>
        <a href="{{ route('ordens.index') }}" class="{{ request()->routeIs('ordens.*') ? 'active' : '' }}">
            <i class="bi bi-card-checklist"></i> Ordens
        </a>
        <a href="{{ route('setores.index') }}" class="{{ request()->routeIs('setores.*') ? 'active' : '' }}">
            <i class="bi bi-diagram-3-fill"></i> Setor
        </a>

    @elseif($user->isExecutor())
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-fill"></i> Dashboard
        </a>
        <div class="sidebar-divider"></div>
        <a href="{{ route('ordens.index') }}" class="{{ request()->routeIs('ordens.*') ? 'active' : '' }}">
            <i class="bi bi-card-checklist"></i> Ordens
        </a>

    @elseif($user->isColaborador())
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-card-checklist"></i> Ordens
        </a>
    @endif

</nav>

<!-- MAIN -->
<div class="main-content">

    <!-- TOPBAR -->
    <div class="topbar">
        <div>
            <div class="topbar-title">CoordenaTask</div>
            <div class="topbar-code">Código da Empresa: {{ $user->empresa->codigo_empresa ?? '' }}</div>
        </div>
        <div class="topbar-right">
            <span style="font-size:0.85rem; color:#555">{{ $user->name }}</span>
            <div class="avatar"><i class="bi bi-person-fill"></i></div>
            <form action="{{ route('auth.logout') }}" method="POST" style="margin:0">
                @csrf
                <button type="submit" class="btn-logout">Sair</button>
            </form>
        </div>
    </div>

    <!-- CONTEÚDO -->
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

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
