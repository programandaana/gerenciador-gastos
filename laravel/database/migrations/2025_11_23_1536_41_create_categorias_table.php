<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            // Ex: 'Alimentos', 'Limpeza', 'Higiene Pessoal', 'Bebidas'

            $table->string('nome')->unique();

            $table->string('slug')->unique();
            // Para URLs ou relatórios (Ex: massas)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
