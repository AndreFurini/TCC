@extends('layouts.app')

@section('content')

<h2 class="mb-4">Setores</h2>

<div class="card p-3">

    <a href="/setores/create" class="btn btn-success mb-3">Novo Setor</a>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Ações</th>
            </tr>
        </thead>

        <tbody>
            @foreach($setores as $setor)
            <tr>
                <td>{{ $setor->id }}</td>
                <td>{{ $setor->nome }}</td>
                <td>
                    <a href="/setores/{{ $setor->id }}/edit" class="btn btn-primary btn-sm">
                        Editar
                    </a>

                    <form action="/setores/{{ $setor->id }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">Excluir</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>

@endsection