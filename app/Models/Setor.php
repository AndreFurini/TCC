<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setor extends Model
{
    protected $table = 'setores'; // 👈 ESSA LINHA RESOLVE

    protected $fillable = ['nome'];
}