@extends('layouts.app')

@section('content')

<h2 class="mb-4">Novo Setor</h2>

<div class="card p-4">

<form action="/setores" method="POST">
    @csrf

    <div class="mb-3">
        <label>Nome</label>
        <input type="text" name="nome" class="form-control" required>
    </div>

    <button class="btn btn-success">Salvar</button>
    <a href="/setores" class="btn btn-secondary">Cancelar</a>

</form>

</div>

@endsection