<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setor extends Model
{
    use HasFactory;

    protected $table = 'setores';

    protected $fillable = [
        'empresa_id',
        'nome',
        'responsavel_id',
    ];

    // Empresa à qual o setor pertence
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    // Responsável pelo setor (um usuário)
    public function responsavel()
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    // Todos os usuários vinculados a este setor
    public function usuarios()
    {
        return $this->hasMany(User::class, 'setor_id');
    }

    // Ordens de serviço deste setor
    public function ordensServico()
    {
        return $this->hasMany(OrdemServico::class, 'setor_id');
    }
}
