<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'empresa_id',
        'name',
        'username',
        'email',
        'password',
        'role',
        'setor_id',
        'ativo',
        'criado_por',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'ativo'             => 'boolean',
        ];
    }

    // Roles disponíveis
    const ROLES = [
        'admin'        => 'Administrador',
        'coordenador'  => 'Coordenador',
        'executor'     => 'Executor',
        'colaborador'  => 'Colaborador',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function setor()
    {
        return $this->belongsTo(Setor::class);
    }

    // Quem cadastrou este usuário (admin ou coordenador). Nulo = cadastro da empresa.
    public function criador()
    {
        return $this->belongsTo(User::class, 'criado_por');
    }

    // Usuários que este usuário cadastrou.
    public function criados()
    {
        return $this->hasMany(User::class, 'criado_por');
    }

    // Helpers de role
    public function isAdmin()        { return $this->role === 'admin'; }
    public function isCoordenador()  { return $this->role === 'coordenador'; }
    public function isExecutor()     { return $this->role === 'executor'; }
    public function isColaborador()  { return $this->role === 'colaborador'; }

    /**
     * Há alguma ordem de serviço ligada a este usuário (criou, executa ou
     * atualizou por último)? Se sim, o usuário só pode ser inativado, nunca excluído.
     */
    public function temOrdensVinculadas(): bool
    {
        return OrdemServico::where('criado_por', $this->id)
            ->orWhere('executor_id', $this->id)
            ->orWhere('atualizado_por', $this->id)
            ->exists();
    }

    /**
     * Regra de gerenciamento (editar/inativar/reativar/excluir):
     *  - Admin gerencia qualquer usuário, sem restrição.
     *  - Coordenador nunca gerencia um admin.
     *  - Coordenador só gerencia OUTRO COORDENADOR se foi ele quem o cadastrou
     *    (a restrição de posse vale só entre coordenadores).
     *  - Coordenador gerencia livremente executores e colaboradores,
     *    independente de quem os cadastrou.
     */
    public function podeSerGerenciadoPor(User $ator): bool
    {
        if (!$ator->isAdmin() && !$ator->isCoordenador()) {
            return false;
        }

        if ($ator->isAdmin()) {
            return true;
        }

        // A partir daqui, $ator é coordenador.
        if ($this->isAdmin()) {
            return false;
        }

        if ($this->isCoordenador()) {
            return $this->criado_por === $ator->id;
        }

        return true; // executor ou colaborador
    }
}
