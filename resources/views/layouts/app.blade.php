<!DOCTYPE html>
<html>
<head>
    <title>Sistema OS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }

        .menu {
            background-color: #1f2a44;
            color: white;
            height: 100vh;
        }

        .menu a {
            background-color: #1f2a44;
            color: white;
            border: none;
        }

        .menu a:hover {
            background-color: #162033;
        }

        .btn-primary {
            background-color: #1f2a44;
            border: none;
        }

        .btn-primary:hover {
            background-color: #162033;
        }

        .btn-success {
            background-color: #f4a261;
            border: none;
            color: #000;
        }

        .btn-success:hover {
            background-color: #e8954f;
        }

        .card {
            border-radius: 12px;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
        }

        .status-aberta {
            color: #f4a261;
            font-weight: bold;
        }

        .status-andamento {
            color: #0d6efd;
            font-weight: bold;
        }

        .status-finalizada {
            color: green;
            font-weight: bold;
        }
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <!-- MENU -->
        <div class="col-3 menu p-4">

            <h3 class="mb-4">Coordena Task</h3>

            <a href="/" class="btn w-100 mb-3">Dashboard</a>
            <a href="/setores" class="btn w-100 mb-3">Setores</a>
            <a href="/ordens/create" class="btn btn-success w-100">Criar OS</a>

        </div>

        <!-- CONTEÚDO -->
        <div class="col-9 p-4">
            @yield('content')
        </div>

    </div>
</div>

</body>
</html>