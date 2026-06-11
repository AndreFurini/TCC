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
                $table->string('username')->unique();
                $table->string('email')->unique();
                $table->string('role')->default('colaborador');
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
