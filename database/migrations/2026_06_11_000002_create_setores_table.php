<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('setores')) {
            Schema::create('setores', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')
                      ->constrained('empresas')
                      ->cascadeOnDelete();
                $table->string('nome');
                $table->unsignedBigInteger('responsavel_id')->nullable();
                $table->boolean('ativo')->default(true);
                $table->timestamps();

                // FK de responsavel_id -> users é adicionada na migration de users
                // (000003), pois a tabela users ainda não existe neste ponto.
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('setores');
    }
};
