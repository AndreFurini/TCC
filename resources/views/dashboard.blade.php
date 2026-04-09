@extends('layouts.app')

@section('content')

<h2 class="mb-4">Ordens de Serviço</h2>

<div class="card p-3">

<table class="table">
    <thead>
        <tr>
            <th>Título</th>
            <th>Setor</th>
            <th>Status</th>
            <th>Ação</th>
        </tr>
    </thead>

    <tbody>
        @foreach($ordens as $ordem)
        <tr>
            <td>{{ $ordem->titulo }}</td>
            <td>{{ $ordem->setor->nome }}</td>

            <td>
                <span class="
                    @if($ordem->status == 'ABERTA') status-aberta
                    @elseif($ordem->status == 'EM_ANDAMENTO') status-andamento
                    @else status-finalizada
                    @endif
                ">
                    {{ $ordem->status }}
                </span>
            </td>

            <td>
                <a href="/ordens/{{ $ordem->id }}/edit" class="btn btn-primary btn-sm">
                    Editar
                </a>
            </td>
        </tr>
        @endforeach
    </tbody>

</table>

</div>

@endsection