<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setor extends Model
{
    protected $table = 'setores';

    protected $fillable = [
        'empresa_id',
        'nome',
        'responsavel_id',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    /**
     * Indica se o setor está referenciado em outras tabelas
     * (usuários lotados no setor ou ordens de serviço).
     * Enquanto houver vínculo, ele só pode ser inativado, nunca excluído.
     */
    public function possuiVinculos(): bool
    {
        return User::where('setor_id', $this->id)->exists()
            || OrdemServico::where('setor_id', $this->id)->exists();
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function responsavel()
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    // Usuários que pertencem a este setor
    public function usuarios()
    {
        return $this->hasMany(User::class);
    }
}
