<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordens_servico', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')
                  ->constrained('empresas')
                  ->cascadeOnDelete();

            $table->foreignId('setor_id')
                  ->nullable()
                  ->constrained('setores')
                  ->nullOnDelete();

            $table->foreignId('executor_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->foreignId('criado_por')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->foreignId('atualizado_por')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->string('titulo');
            $table->text('descricao')->nullable();

            $table->string('status')->default('ABERTA');
            $table->string('urgencia')->default('MEDIA');

            $table->text('devolutiva')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordens_servico');
    }
};