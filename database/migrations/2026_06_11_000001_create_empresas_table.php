<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('empresas')) {
            Schema::create('empresas', function (Blueprint $table) {
                $table->id();
                $table->string('nome');
                $table->string('cnpj')->nullable();
                $table->string('codigo_empresa', 6)->unique();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
