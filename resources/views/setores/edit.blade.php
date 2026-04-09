@extends('layouts.app')

@section('content')

<h2 class="mb-4">Editar Setor</h2>

<div class="card p-4">

<form action="/setores/{{ $setor->id }}" method="POST">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label>Nome</label>
        <input type="text" name="nome" class="form-control" value="{{ $setor->nome }}" required>
    </div>

    <button class="btn btn-primary">Atualizar</button>
    <a href="/setores" class="btn btn-secondary">Cancelar</a>

</form>

</div>

@endsection