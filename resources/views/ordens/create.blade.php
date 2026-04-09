@extends('layouts.app')

@section('content')

<h2 class="mb-4">Criar Ordem de Serviço</h2>

<div class="card p-4">

<form action="/ordens" method="POST">
    @csrf

    <div class="mb-3">
        <label>Título</label>
        <input type="text" name="titulo" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Descrição</label>
        <textarea name="descricao" class="form-control" required></textarea>
    </div>

    <div class="mb-3">
        <label>Setor</label>
        <select name="setor_id" class="form-control" required>
            @foreach($setores as $setor)
                <option value="{{ $setor->id }}">{{ $setor->nome }}</option>
            @endforeach
        </select>
    </div>

    <button class="btn btn-success">Salvar</button>
    <a href="/" class="btn btn-secondary">Cancelar</a>

</form>

</div>

@endsection