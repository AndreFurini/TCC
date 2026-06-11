<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdemServico extends Model
{
    protected $table = 'ordens_servico';

    protected $fillable = [
        'empresa_id',
        'titulo',
        'descricao',
        'status',
        'urgencia',
        'setor_id',
        'executor_id',
        'criado_por',
        'atualizado_por',
        'devolutiva',
    ];

    const STATUS = [
        'ABERTA'       => 'Aberta',
        'EM_ANDAMENTO' => 'Em Andamento',
        'FINALIZADA'   => 'Finalizada',
        'CANCELADA'    => 'Cancelada',
    ];

    const URGENCIA = [
        'BAIXA'   => 'Baixa',
        'MEDIA'   => 'Média',
        'ALTA'    => 'Alta',
        'URGENTE' => 'Urgente',
    ];

    // Cor da bolinha por status
    const STATUS_CORES = [
        'ABERTA'       => '#f5a623', // amarelo
        'EM_ANDAMENTO' => '#1a35a8', // azul
        'FINALIZADA'   => '#27ae60', // verde
        'CANCELADA'    => '#e74c3c', // vermelho
    ];

    public function setor()
    {
        return $this->belongsTo(Setor::class);
    }

    public function executor()
    {
        return $this->belongsTo(User::class, 'executor_id');
    }

    public function criadoPor()
    {
        return $this->belongsTo(User::class, 'criado_por');
    }

    public function atualizadoPor()
    {
        return $this->belongsTo(User::class, 'atualizado_por');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
