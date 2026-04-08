@extends('layout')

@section('conteudo')

<h1>Primeiro exemplo usando Laravel</h1>
<form method="post" action="/resposta">
    @CSRF
<div class="mb-3">
              <label for="valor" class="form-label">Informe o primeiro valor</label>
              <input type="number" id="valor1" name="valor1" class="form-control" required="">
            </div><div class="mb-3">
              <label for="valor2" class="form-label">Informe o segundo valor:</label>
              <input type="number" id="valor2" name="valor2" class="form-control" required="">
            </div>
<button type="submit" class="btn btn-primary">Enviar</button>
</form>

@endsection