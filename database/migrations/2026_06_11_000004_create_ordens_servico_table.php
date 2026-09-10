<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ordens_servico')) {
            Schema::create('ordens_servico', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')
                      ->constrained('empresas')
                      ->cascadeOnDelete();
                $table->foreignId('setor_id')
                      ->constrained('setores')
                      ->restrictOnDelete();
                $table->foreignId('executor_id')
                      ->nullable()
                      ->constrained('users')
                      ->nullOnDelete();
                $table->foreignId('criado_por')
                      ->constrained('users')
                      ->restrictOnDelete();
                $table->foreignId('atualizado_por')
                      ->nullable()
                      ->constrained('users')
                      ->nullOnDelete();
                $table->string('titulo');
                $table->text('descricao');
                $table->enum('status', ['ABERTA', 'EM_ANDAMENTO', 'FINALIZADA', 'CANCELADA'])
                      ->default('ABERTA');
                $table->enum('urgencia', ['BAIXA', 'MEDIA', 'ALTA', 'URGENTE'])
                      ->default('BAIXA');
                $table->text('devolutiva')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ordens_servico');
    }
};
