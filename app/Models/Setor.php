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
    ];

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
