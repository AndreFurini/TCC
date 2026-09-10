<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')
                      ->constrained('empresas')
                      ->cascadeOnDelete();
                $table->foreignId('setor_id')
                      ->nullable()
                      ->constrained('setores')
                      ->nullOnDelete();
                $table->string('name');
                $table->string('username', 50)->unique();
                $table->string('email')->unique();
                $table->enum('role', ['admin', 'coordenador', 'executor', 'colaborador'])
                      ->default('colaborador');
                $table->boolean('ativo')->default(true);
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
            });

            // FK adiada: setores.responsavel_id -> users.id
            Schema::table('setores', function (Blueprint $table) {
                $table->foreign('responsavel_id')
                      ->references('id')->on('users')
                      ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('setores')) {
            Schema::table('setores', function (Blueprint $table) {
                $table->dropForeign(['responsavel_id']);
            });
        }

        Schema::dropIfExists('users');
    }
};
