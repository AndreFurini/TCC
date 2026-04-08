@extends('layout')

@section('conteudo')
    <h1>Consultar Categoria</h1>
    <form method="post" action="/categorias/{{ $categoria->id }}">
        @CSRF
        @METHOD('DELETE')
        <div class="mb-3">
            <p>Nome: <strong> {{ $categoria->nome }} </strong> </p>
        </div>
        <div class="mb-3">
            <p>Descrição: <strong> {{ $categoria->descricao }} </strong> </p>
        </div>
        <button type="submit" class="btn btn-danger">Excluir o registro</button>
        <a href="/categorias" class="btn btn-secondary">Voltar</a>
    </form>
@endsection