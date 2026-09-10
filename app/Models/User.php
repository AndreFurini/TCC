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

    // Helpers de role
    public function isAdmin()        { return $this->role === 'admin'; }
    public function isCoordenador()  { return $this->role === 'coordenador'; }
    public function isExecutor()     { return $this->role === 'executor'; }
    public function isColaborador()  { return $this->role === 'colaborador'; }

    /**
     * Indica se o usuário está referenciado em outras tabelas
     * (ordens de serviço ou como responsável de setor).
     * Enquanto houver vínculo, ele só pode ser inativado, nunca excluído.
     */
    public function possuiVinculos(): bool
    {
        $emOrdens = OrdemServico::where('criado_por', $this->id)
            ->orWhere('executor_id', $this->id)
            ->orWhere('atualizado_por', $this->id)
            ->exists();

        $responsavelSetor = Setor::where('responsavel_id', $this->id)->exists();

        return $emOrdens || $responsavelSetor;
    }
}
