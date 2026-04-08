@extends('layout')

@section('conteudo')
    
    <h2>Categorias de Produtos</h2>
        <a href="categorias/create" class="btn btn-success mb-3">Novo Registro</a>
          <table class="table table-hover table-striped">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Descrição</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
                @foreach($categorias as $c)
                <tr>
                  <td> {{ $c->id }}</td>
                  <td> {{ $c->nome }}</td>
                  <td> {{ $c->descricao }}</td>
                  <td class="d-flex gap-2">
                    <a href="/categorias/{{ $c->id }}/edit" class="btn btn-sm btn-warning">Editar</a>
                    <a href="/categorias/{{ $c->id }}" class="btn btn-sm btn-info">Consultar</a>
                  </td>
                </tr>
                @endforeach
            </tbody>
          </table>
@endsection