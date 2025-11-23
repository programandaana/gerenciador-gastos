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
        Schema::create('itens_da_compra', function (Blueprint $table) {
            $table->id();

            // Chave estrangeira para a Nota Fiscal
            $table
                ->foreignId('nota_fiscal_id')
                ->constrained('notas_fiscais')
                ->onDelete('cascade');

            // Detalhes do produto
            $table->string('codigo_produto');
            $table->string('descricao', 255); // Ex: MASSA MOSMANN N 3 500G

            // Valores
            $table->decimal('quantidade', 8, 3); // Qtd
            $table->decimal('preco_unitario', 8, 2); // Un X Preco
            $table->decimal('total_item', 10, 2); // Total

            // Categoria
            $table
                ->foreignId('categoria_id')
                ->nullable()
                ->constrained('categorias')
                ->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itens_da_compra');
    }
};
