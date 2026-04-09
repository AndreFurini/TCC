@extends('layouts.app')

@section('content')

<h2 class="mb-4">Editar Ordem de Serviço</h2>

<div class="card p-4">

<form action="/ordens/{{ $ordem->id }}" method="POST">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label>Título</label>
        <input type="text" name="titulo" class="form-control" value="{{ $ordem->titulo }}" required>
    </div>

    <div class="mb-3">
        <label>Descrição</label>
        <textarea name="descricao" class="form-control" required>{{ $ordem->descricao }}</textarea>
    </div>

    <div class="mb-3">
        <label>Status</label>
        <select name="status" class="form-control">
            <option value="ABERTA" {{ $ordem->status == 'ABERTA' ? 'selected' : '' }}>ABERTA</option>
            <option value="EM_ANDAMENTO" {{ $ordem->status == 'EM_ANDAMENTO' ? 'selected' : '' }}>EM ANDAMENTO</option>
            <option value="FINALIZADA" {{ $ordem->status == 'FINALIZADA' ? 'selected' : '' }}>FINALIZADA</option>
        </select>
    </div>

    <div class="mb-3">
        <label>Setor</label>
        <select name="setor_id" class="form-control">
            @foreach($setores as $setor)
                <option value="{{ $setor->id }}" {{ $ordem->setor_id == $setor->id ? 'selected' : '' }}>
                    {{ $setor->nome }}
                </option>
            @endforeach
        </select>
    </div>

    <button class="btn btn-primary">Atualizar</button>
    <a href="/" class="btn btn-secondary">Cancelar</a>

</form>

</div>

@endsection