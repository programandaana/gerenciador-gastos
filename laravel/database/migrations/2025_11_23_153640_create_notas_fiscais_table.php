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
        Schema::create('notas_fiscais', function (Blueprint $table) {
            $table->id();

            // Chave estrangeira para o Estabelecimento
            $table
                ->foreignId('estabelecimento_id')
                ->constrained('estabelecimentos')
                ->onDelete('cascade');

            // Dados da transação
            $table->string('chave_acesso', 44)->unique();
            $table->date('data_emissao'); // Ex: 30/10/2025
            $table->time('hora_emissao'); // Ex: 18:25:41

            // Valores financeiros
            $table->decimal('total_bruto', 10, 2); // Valor antes de descontos
            $table->decimal('descontos', 10, 2)->default(0.00);
            $table->decimal('valor_pago', 10, 2); // VALOR A PAGAR

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas_fiscais');
    }
};
