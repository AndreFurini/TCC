<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $table = 'empresas';

    protected $fillable = [
        'nome',
        'cnpj',
        'codigo_empresa',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function setores()
    {
        return $this->hasMany(Setor::class);
    }
}
