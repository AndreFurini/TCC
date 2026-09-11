<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\OrdemServico;
use App\Models\Setor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Base de dados para testes manuais do CoordenaTask.
 *
 * Cria 2 empresas isoladas (multi-tenant), com setores, usuários de todos
 * os papéis e ordens de serviço cobrindo todos os status/urgências.
 *
 * Rodar:   php artisan db:seed --class=TestDataSeeder
 * Ou:      php artisan migrate:fresh --seed   (via DatabaseSeeder)
 *
 * É idempotente: remove e recria apenas as 2 empresas de teste
 * (códigos HVIDA1 e BETA02). Nenhuma outra empresa é tocada.
 *
 * Senha de TODOS os usuários: Senha@2026
 */
class TestDataSeeder extends Seeder
{
    private const SENHA = 'Senha@2026';

    public function run(): void
    {
        $this->limparEmpresa('HVIDA1');
        $this->limparEmpresa('BETA02');

        $this->seedHospitalVida();
        $this->seedBetaServicos();

        $this->command->newLine();
        $this->command->info('Base de teste criada. Senha de todos: '.self::SENHA);
        $this->command->table(
            ['Empresa', 'Código', 'Usuário', 'Papel', 'Cadastrado por', 'Obs'],
            [
                ['Hospital Vida', 'HVIDA1', 'admin',          'admin',       '—',      '—'],
                ['Hospital Vida', 'HVIDA1', 'coord',          'coordenador', 'admin',  'setor TI'],
                ['Hospital Vida', 'HVIDA1', 'exec.manut',     'executor',    'admin',  'setor Manutenção'],
                ['Hospital Vida', 'HVIDA1', 'exec.ti',        'executor',    'coord',  'setor TI'],
                ['Hospital Vida', 'HVIDA1', 'exec.inativo',   'executor',    'admin',  'ativo = false'],
                ['Hospital Vida', 'HVIDA1', 'colab.enf',      'colaborador', 'admin',  'setor Enfermagem'],
                ['Hospital Vida', 'HVIDA1', 'colab.limp',     'colaborador', 'coord',  'setor Limpeza'],
                ['Hospital Vida', 'HVIDA1', 'colab.semsetor', 'colaborador', 'admin',  'sem setor'],
                ['Beta Serviços', 'BETA02', 'beta.admin',     'admin',       '—',      '—'],
                ['Beta Serviços', 'BETA02', 'beta.coord',     'coordenador', 'beta.admin', 'setor Operações'],
                ['Beta Serviços', 'BETA02', 'beta.exec',      'executor',    'beta.admin', 'setor Operações'],
                ['Beta Serviços', 'BETA02', 'beta.colab',     'colaborador', 'beta.coord', 'setor Operações'],
            ]
        );
    }

    /**
     * Remove a empresa de teste e toda a sua árvore, em ordem segura
     * para as FKs RESTRICT (ordens antes de usuários/setores).
     */
    private function limparEmpresa(string $codigo): void
    {
        $empresa = Empresa::where('codigo_empresa', $codigo)->first();
        if (!$empresa) {
            return;
        }

        OrdemServico::where('empresa_id', $empresa->id)->delete();
        Setor::where('empresa_id', $empresa->id)->update(['responsavel_id' => null]);
        User::where('empresa_id', $empresa->id)->delete();
        Setor::where('empresa_id', $empresa->id)->delete();
        $empresa->delete();
    }

    private function seedHospitalVida(): void
    {
        $empresa = Empresa::create([
            'nome'           => 'Hospital Vida',
            'cnpj'           => '12.345.678/0001-90',
            'codigo_empresa' => 'HVIDA1',
        ]);

        // Setores (responsável definido depois que os usuários existirem)
        $manutencao = $this->setor($empresa, 'Manutenção');
        $ti         = $this->setor($empresa, 'TI');
        $enfermagem = $this->setor($empresa, 'Enfermagem');
        $limpeza    = $this->setor($empresa, 'Limpeza');

        // Usuários — mistura quem cadastrou cada um (admin/coordenador) para
        // demonstrar a regra "só quem cadastrou gerencia".
        $admin = $this->user($empresa, 'Ana Admin',            'admin',          'admin'); // auto-registro, sem criador
        $coord = $this->user($empresa, 'Carlos Coordenador',   'coord',          'coordenador', $ti, criadoPor: $admin);

        $execManut   = $this->user($empresa, 'Eduardo Manutenção', 'exec.manut',   'executor', $manutencao, criadoPor: $admin);
        $execTi      = $this->user($empresa, 'Elaine TI',          'exec.ti',      'executor', $ti, criadoPor: $coord);
        $execInativo = $this->user($empresa, 'Ex-funcionário',     'exec.inativo', 'executor', $manutencao, ativo: false, criadoPor: $admin);

        $colabEnf      = $this->user($empresa, 'Camila Enfermagem', 'colab.enf',      'colaborador', $enfermagem, criadoPor: $admin);
        $colabLimp     = $this->user($empresa, 'Bruno Limpeza',     'colab.limp',     'colaborador', $limpeza, criadoPor: $coord);
        $colabSemSetor = $this->user($empresa, 'Beatriz Sem Setor', 'colab.semsetor', 'colaborador', criadoPor: $admin);

        // Responsáveis dos setores
        $manutencao->update(['responsavel_id' => $execManut->id]);
        $ti->update(['responsavel_id' => $coord->id]);
        $enfermagem->update(['responsavel_id' => $colabEnf->id]);
        // Limpeza fica sem responsável de propósito

        // Ordens de serviço — cobre todos os status e urgências
        $this->os($empresa, $manutencao, 'Ar-condicionado da recepção pingando', 'ABERTA', 'ALTA',
            criadoPor: $colabEnf, dias: 2, alteradaPeloCriador: true);

        $this->os($empresa, $manutencao, 'Porta automática da emergência travada', 'EM_ANDAMENTO', 'URGENTE',
            criadoPor: $coord, executor: $execManut, atualizadoPor: $coord,
            devolutiva: 'Equipe a caminho, peça de reposição solicitada.', dias: 1);

        $this->os($empresa, $ti, 'Impressora do 3º andar sem conexão', 'ABERTA', 'MEDIA',
            criadoPor: $colabLimp, dias: 5);

        $this->os($empresa, $ti, 'Sistema de prontuário lento', 'EM_ANDAMENTO', 'ALTA',
            criadoPor: $coord, executor: $execTi, atualizadoPor: $coord, dias: 3);

        $this->os($empresa, $ti, 'Trocar cabo de rede da sala de reuniões', 'FINALIZADA', 'BAIXA',
            criadoPor: $coord, executor: $execTi, atualizadoPor: $execTi,
            devolutiva: 'Cabo trocado e testado. OK.', dias: 12);

        $this->os($empresa, $enfermagem, 'Falta de material no posto 2', 'ABERTA', 'URGENTE',
            criadoPor: $colabEnf, dias: 1);

        $this->os($empresa, $enfermagem, 'Solicitação duplicada de insumos', 'CANCELADA', 'BAIXA',
            criadoPor: $colabEnf, atualizadoPor: $coord,
            devolutiva: 'Cancelada — já existe a OS #anterior.', dias: 8);

        $this->os($empresa, $limpeza, 'Reposição de sabonete nos banheiros', 'ABERTA', 'BAIXA',
            criadoPor: $colabLimp, dias: 4, alteradaPeloCriador: true);

        $this->os($empresa, $limpeza, 'Limpeza pesada do refeitório', 'EM_ANDAMENTO', 'MEDIA',
            criadoPor: $colabLimp, executor: $execManut, atualizadoPor: $coord, dias: 2);

        $this->os($empresa, $manutencao, 'Troca de lâmpadas do corredor B', 'FINALIZADA', 'MEDIA',
            criadoPor: $colabSemSetor, executor: $execManut, atualizadoPor: $execManut,
            devolutiva: 'Todas as lâmpadas trocadas por LED.', dias: 20);

        $this->os($empresa, $ti, 'Novo colaborador precisa de acesso ao e-mail', 'ABERTA', 'ALTA',
            criadoPor: $colabSemSetor, dias: 1);

        // OS com executor INATIVO — testa o dropdown do detalhe (mantém o já atribuído)
        $this->os($empresa, $manutencao, 'Gerador não iniciou no teste semanal', 'EM_ANDAMENTO', 'URGENTE',
            criadoPor: $coord, executor: $execInativo, atualizadoPor: $coord,
            devolutiva: 'Aguardando técnico externo.', dias: 6);
    }

    private function seedBetaServicos(): void
    {
        $empresa = Empresa::create([
            'nome'           => 'Beta Serviços',
            'cnpj'           => '98.765.432/0001-10',
            'codigo_empresa' => 'BETA02',
        ]);

        $operacoes = $this->setor($empresa, 'Operações');

        $betaAdmin = $this->user($empresa, 'Beto Admin',        'beta.admin', 'admin');
        $coord = $this->user($empresa, 'Bianca Coord', 'beta.coord', 'coordenador', $operacoes, criadoPor: $betaAdmin);
        $exec  = $this->user($empresa, 'Bento Executor', 'beta.exec', 'executor', $operacoes, criadoPor: $betaAdmin);
        $colab = $this->user($empresa, 'Baltazar Colab', 'beta.colab', 'colaborador', $operacoes, criadoPor: $coord);

        $operacoes->update(['responsavel_id' => $coord->id]);

        $this->os($empresa, $operacoes, 'Vazamento no estacionamento', 'ABERTA', 'MEDIA',
            criadoPor: $colab, dias: 3);

        $this->os($empresa, $operacoes, 'Manutenção preventiva dos elevadores', 'EM_ANDAMENTO', 'ALTA',
            criadoPor: $coord, executor: $exec, atualizadoPor: $coord, dias: 2);
    }

    // ---------------------------------------------------------------------

    private function setor(Empresa $empresa, string $nome): Setor
    {
        return Setor::create([
            'empresa_id' => $empresa->id,
            'nome'       => $nome,
        ]);
    }

    private function user(
        Empresa $empresa,
        string $name,
        string $username,
        string $role,
        ?Setor $setor = null,
        bool $ativo = true,
        ?User $criadoPor = null,
    ): User {
        return User::create([
            'empresa_id' => $empresa->id,
            'name'       => $name,
            'username'   => $username,
            'email'      => $username.'@'.strtolower(explode(' ', $empresa->nome)[0]).'.test',
            'password'   => Hash::make(self::SENHA),
            'role'       => $role,
            'setor_id'   => $setor?->id,
            'ativo'      => $ativo,
            'criado_por' => $criadoPor?->id,
        ]);
    }

    private function os(
        Empresa $empresa,
        Setor $setor,
        string $titulo,
        string $status,
        string $urgencia,
        User $criadoPor,
        ?User $executor = null,
        ?User $atualizadoPor = null,
        ?string $devolutiva = null,
        int $dias = 0,
        bool $alteradaPeloCriador = false,
    ): OrdemServico {
        $quando = now()->subDays($dias)->subHours(random_int(0, 20));

        // Data de entrega prevista: só para OS em aberto/andamento, prazo por urgência.
        $prazoPorUrgencia = ['URGENTE' => 2, 'ALTA' => 5, 'MEDIA' => 10, 'BAIXA' => 20];
        $dataEntrega = in_array($status, ['ABERTA', 'EM_ANDAMENTO'], true)
            ? $quando->copy()->addDays($prazoPorUrgencia[$urgencia] ?? 7)->startOfDay()
            : null;

        return OrdemServico::create([
            'empresa_id'     => $empresa->id,
            'setor_id'       => $setor->id,
            'titulo'         => $titulo,
            'descricao'      => $titulo.'. (descrição gerada para teste)',
            'status'         => $status,
            'urgencia'       => $urgencia,
            'executor_id'    => $executor?->id,
            'criado_por'     => $criadoPor->id,
            'atualizado_por' => ($atualizadoPor ?? $criadoPor)->id,
            'data_entrega'   => $dataEntrega,
            'alterada_pelo_criador_em' => $alteradaPeloCriador ? $quando->copy()->addHours(random_int(2, 40)) : null,
            'devolutiva'     => $devolutiva,
            'created_at'     => $quando,
            'updated_at'     => $quando,
        ]);
    }
}
