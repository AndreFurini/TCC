@extends('layouts.app')

@push('styles')
<style>
    .btn-cadastrar {
        background:#1a35a8; color:#fff; border-radius:8px; padding:10px 22px;
        font-size:0.9rem; font-weight:600; text-decoration:none;
        display:inline-flex; align-items:center; gap:8px;
        transition:background 0.2s;
    }
    .btn-cadastrar:hover { background:#142a86; }

    .lista-vazia {
        display:flex; flex-direction:column; align-items:center;
        text-align:center; padding:60px 20px; gap:14px;
    }
    .lista-vazia .icone-vazio { font-size:2.4rem; color:#c5cde8; }
    .lista-vazia p { color:#999; font-size:0.9rem; margin:0; }
</style>
@endpush

@section('content')

@if($setores->isEmpty() && ! $verInativos)

    <div class="lista-vazia">
        <i class="bi bi-diagram-3 icone-vazio"></i>
        <p>Nenhum setor cadastrado ainda.</p>
        <a href="{{ route('setores.create') }}" class="btn-cadastrar">
            <i class="bi bi-plus-lg"></i> Cadastrar Novo Setor
        </a>
        <form method="GET" action="{{ route('setores.index') }}" style="margin:0;">
            <label class="check-inativos">
                <input type="checkbox" name="inativos" value="1" onchange="this.form.submit()">
                Visualizar inativos
            </label>
        </form>
    </div>

@else

    <div class="toolbar-acoes">
        <a href="{{ route('setores.create') }}" class="btn-acao btn-acao--primary">
            <i class="bi bi-plus-lg"></i> Cadastrar
        </a>

        <button type="button" id="btnEditar" class="btn-acao btn-acao--neutro is-disabled" disabled>
            <i class="bi bi-pencil-fill"></i> Editar
        </button>

        <button type="button" id="btnStatus" class="btn-acao btn-acao--warning is-disabled" disabled>
            <i class="bi bi-slash-circle"></i> <span id="btnStatusLabel">Inativar</span>
        </button>

        <button type="button" id="btnExcluir" class="btn-acao btn-acao--danger is-disabled" disabled>
            <i class="bi bi-trash-fill"></i> Excluir
        </button>

        <form method="GET" action="{{ route('setores.index') }}" style="margin:0;">
            <label class="check-inativos">
                <input type="checkbox" name="inativos" value="1"
                       onchange="this.form.submit()" {{ $verInativos ? 'checked' : '' }}>
                Visualizar inativos
            </label>
        </form>
    </div>

    <form id="formStatus" method="POST" hidden>
        @csrf
        @method('PATCH')
        <input type="hidden" name="inativos" value="{{ $verInativos ? 1 : '' }}">
    </form>
    <form id="formExcluir" method="POST" hidden>
        @csrf
        @method('DELETE')
        <input type="hidden" name="inativos" value="{{ $verInativos ? 1 : '' }}">
    </form>

    @if($setores->isEmpty())
        <p style="color:#999; font-size:0.9rem; padding:20px 4px;">Nenhum setor encontrado.</p>
    @else
        <div style="display:flex; flex-direction:column; gap:10px;">
            @foreach($setores as $setor)
                <div class="linha-selecionavel {{ $setor->ativo ? '' : 'linha-inativa' }}"
                     data-nome="{{ $setor->nome }}"
                     data-ativo="{{ $setor->ativo ? 1 : 0 }}"
                     data-tem-ordens="{{ $setor->temOrdensVinculadas() ? 1 : 0 }}"
                     data-edit-url="{{ route('setores.edit', $setor) }}"
                     data-destroy-url="{{ route('setores.destroy', $setor) }}"
                     data-inativar-url="{{ route('setores.inativar', $setor) }}"
                     data-reativar-url="{{ route('setores.reativar', $setor) }}"
                     style="background:white; border-radius:10px; padding:16px 20px;
                            box-shadow:0 2px 8px rgba(0,0,0,0.06);
                            display:flex; align-items:center; justify-content:space-between;">

                    <div>
                        <div style="font-weight:700; font-size:0.97rem; color:#222;">
                            {{ $setor->nome }}
                            @unless($setor->ativo)<span class="badge-inativo">Inativo</span>@endunless
                        </div>
                        <div style="font-size:0.82rem; color:#888; margin-top:2px;">
                            <i class="bi bi-person"></i>
                            Responsável: {{ $setor->responsavel ? $setor->responsavel->name : '—' }}
                        </div>
                    </div>

                    <div style="display:flex; align-items:center; gap:24px;">
                        <span style="font-size:0.85rem; color:#555;">
                            <i class="bi bi-people"></i>
                            {{ $setor->usuarios->count() }}
                            {{ $setor->usuarios->count() === 1 ? 'usuário' : 'usuários' }}
                        </span>
                    </div>

                </div>
            @endforeach
        </div>
    @endif

    <div class="modal-simples" id="modalBloqueio" hidden>
        <div class="modal-simples-box">
            <div class="modal-simples-icone"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <p id="modalBloqueioTexto"></p>
            <button type="button" class="btn-acao btn-acao--primary" id="modalBloqueioOk">OK</button>
        </div>
    </div>

    <div class="modal-simples" id="modalConfirmar" hidden>
        <div class="modal-simples-box">
            <div class="modal-simples-icone perigo"><i class="bi bi-question-circle-fill"></i></div>
            <p id="modalConfirmarTexto"></p>
            <div class="modal-simples-acoes">
                <button type="button" class="btn-acao btn-acao--neutro" id="modalConfirmarNao">Não</button>
                <button type="button" class="btn-acao btn-acao--primary" id="modalConfirmarSim">Sim</button>
            </div>
        </div>
    </div>

@endif

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const linhas      = document.querySelectorAll('.linha-selecionavel');
        const btnEditar   = document.getElementById('btnEditar');
        const btnStatus   = document.getElementById('btnStatus');
        if (!btnStatus) return;

        const btnLabel    = document.getElementById('btnStatusLabel');
        const btnIcon     = btnStatus.querySelector('i');
        const btnExcluir  = document.getElementById('btnExcluir');
        const formStatus  = document.getElementById('formStatus');
        const formExcluir = document.getElementById('formExcluir');
        const modal       = document.getElementById('modalBloqueio');
        const modalTexto  = document.getElementById('modalBloqueioTexto');
        const modalConf   = document.getElementById('modalConfirmar');
        const modalConfTx = document.getElementById('modalConfirmarTexto');
        let selecionada = null;
        let acaoPendente = null;

        function pedirConfirmacao(texto, aoConfirmar) {
            modalConfTx.textContent = texto;
            acaoPendente = aoConfirmar;
            modalConf.hidden = false;
        }
        function fecharConfirmacao() {
            modalConf.hidden = true;
            acaoPendente = null;
        }

        function render() {
            const temSelecao = selecionada !== null;
            [btnEditar, btnStatus, btnExcluir].forEach(function (b) {
                b.disabled = !temSelecao;
                b.classList.toggle('is-disabled', !temSelecao);
            });

            const inativar = !temSelecao || selecionada.dataset.ativo === '1';
            btnStatus.classList.toggle('btn-acao--warning', inativar);
            btnStatus.classList.toggle('btn-acao--success', !inativar);
            btnLabel.textContent = inativar ? 'Inativar' : 'Reativar';
            btnIcon.className = inativar ? 'bi bi-slash-circle' : 'bi bi-check-circle';
        }

        linhas.forEach(function (linha) {
            linha.addEventListener('click', function () {
                if (selecionada === linha) {
                    linha.classList.remove('selecionado');
                    selecionada = null;
                } else {
                    if (selecionada) selecionada.classList.remove('selecionado');
                    linha.classList.add('selecionado');
                    selecionada = linha;
                }
                render();
            });
        });

        btnEditar.addEventListener('click', function () {
            if (selecionada) window.location.href = selecionada.dataset.editUrl;
        });

        btnStatus.addEventListener('click', function () {
            if (!selecionada) return;
            const ativo = selecionada.dataset.ativo === '1';
            const nome  = selecionada.dataset.nome || 'este setor';
            const url   = ativo ? selecionada.dataset.inativarUrl : selecionada.dataset.reativarUrl;
            const verbo = ativo ? 'inativar' : 'reativar';
            pedirConfirmacao('Tem certeza que deseja ' + verbo + ' o setor "' + nome + '"?', function () {
                formStatus.action = url;
                formStatus.submit();
            });
        });

        btnExcluir.addEventListener('click', function () {
            if (!selecionada) return;
            const nome = selecionada.dataset.nome || 'este setor';

            if (selecionada.dataset.temOrdens === '1') {
                modalTexto.textContent =
                    'O setor "' + nome + '" não pode ser excluído porque há ordens de serviço vinculadas a ele. Você pode inativá-lo.';
                modal.hidden = false;
                return;
            }

            const url = selecionada.dataset.destroyUrl;
            pedirConfirmacao('Tem certeza que deseja excluir o setor "' + nome + '"? Esta ação não pode ser desfeita.', function () {
                formExcluir.action = url;
                formExcluir.submit();
            });
        });

        document.getElementById('modalConfirmarSim').addEventListener('click', function () {
            const fn = acaoPendente;
            fecharConfirmacao();
            if (fn) fn();
        });
        document.getElementById('modalConfirmarNao').addEventListener('click', fecharConfirmacao);
        modalConf.addEventListener('click', (e) => { if (e.target === modalConf) fecharConfirmacao(); });

        document.getElementById('modalBloqueioOk').addEventListener('click', () => modal.hidden = true);
        modal.addEventListener('click', (e) => { if (e.target === modal) modal.hidden = true; });

        render();
    });
</script>
@endpush
