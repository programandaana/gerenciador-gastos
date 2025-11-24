<?php

namespace App\Gateways;

use App\DTOs\OcrResultDTO;
use App\Exceptions\NotaJaExistente;
use App\Models\Estabelecimento;
use App\Models\NotaFiscal;
use App\Models\Categoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; // Necessário para Str::slug()

class SaveReceiptDataGateway
{
    public function execute(OcrResultDTO $dto): NotaFiscal
    {
        return DB::transaction(function () use ($dto) {

            if (NotaFiscal::where('chave_acesso', $dto->chaveAcesso)->exists()) {
                throw new NotaJaExistente("Esta nota fiscal já foi processada. Chave de Acesso ({$dto->chaveAcesso}) duplicada.");
            }

            // 1. Busca ou Cria o Estabelecimento
            $estabelecimento = Estabelecimento::firstOrCreate(
                [
                    'cnpj' => preg_replace('/\D/', '', $dto->estabelecimentoCnpj)
                ],
                [
                    'nome' => $dto->estabelecimentoNome,
                    'endereco' => $dto->estabelecimentoEndereco
                ]
            );

            // 2. Cria a Nota Fiscal (incluindo campos legais)
            $notaFiscal = NotaFiscal::create([
                'estabelecimento_id' => $estabelecimento->id,
                'chave_acesso'       => preg_replace(
                    '/\D/',
                    '',
                    $dto->chaveAcesso
                ),
                'data_emissao'       => $dto->dataEmissao,
                'hora_emissao'       => $dto->horaEmissao,
                'total_bruto'        => $dto->totalBruto,
                'descontos'          => $dto->descontos,
                'valor_pago'         => $dto->valorPago,
            ]);

            // 3. Prepara e Cria os Itens da Compra
            $itens = $dto->itens->map(function ($item) use ($notaFiscal) {

                // Lógica de Fallback de Preço/Quantidade
                $total = (float)($item['total_item'] ?? 0.0);
                $quantidade = (float)($item['quantidade'] ?? 0.0);

                if ($quantidade <= 0 && $total > 0) {
                    $quantidade = 1.0;
                }
                $preco_unitario = ($quantidade > 0) ? ($total / $quantidade) : 0.0;

                $nomeCategoria = $item['categoria_nome_gemini'] ?? 'OUTROS';

                $categoria = Categoria::firstOrCreate(
                    ['nome' => $nomeCategoria],
                    ['slug' => Str::slug($nomeCategoria)]
                );

                $data = [
                    'codigo_produto' => $item['codigo_produto'] ?? null,
                    'descricao' => $item['descricao'] ?? 'Produto sem descrição',
                    'quantidade' => $quantidade,
                    'preco_unitario' => round($preco_unitario, 4),
                    'total_item' => $total,
                    'nota_fiscal_id' => $notaFiscal->id,
                    'categoria_id' => $categoria->id,
                ];

                return $data;
            })->toArray();

            $notaFiscal->itens()->createMany($itens);

            return $notaFiscal;
        });
    }
}
