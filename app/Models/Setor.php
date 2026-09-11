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
     * Há alguma ordem de serviço ligada a este setor? Se sim, o setor só
     * pode ser inativado, nunca excluído.
     */
    public function temOrdensVinculadas(): bool
    {
        return OrdemServico::where('setor_id', $this->id)->exists();
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

    // Ordens de serviço abertas para este setor
    public function ordensServico()
    {
        return $this->hasMany(OrdemServico::class);
    }
}
