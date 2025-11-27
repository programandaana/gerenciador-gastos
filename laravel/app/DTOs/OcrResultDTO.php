<?php

namespace App\DTOs;

use Illuminate\Support\Collection;

class OcrResultDTO
{
    public string $estabelecimentoNome;
    public string $estabelecimentoCnpj;
    public ?string $estabelecimentoEndereco = null;
    public string $chaveAcesso;
    public string $dataEmissao;
    public string $horaEmissao;
    public float $totalBruto;
    public float $descontos;
    public float $valorPago;

    public Collection $itens;

    public function __construct(array $data)
    {
        // Mapeamento de Chaves do JSON do Gemini para as Propriedades do DTO
        $nfceData = $data['nfce_data'] ?? [];

        $this->estabelecimentoNome = $data['company_name'] ?? '';
        $this->estabelecimentoCnpj = $data['cnpj'] ?? '';
        $this->estabelecimentoEndereco = $data['company_address'] ?? null;

        $this->chaveAcesso = $data['access_key'] ?? ($nfceData['access_key'] ?? '');
        $this->dataEmissao = $data['transaction_date'] ?? ($nfceData['issuance_date'] ?? '');
        $this->horaEmissao = $data['transaction_time'] ?? ($nfceData['issuance_time'] ?? '');

        $this->totalBruto = (float)($data['total_value'] ?? 0.0);
        $this->descontos  = (float)($data['total_item_discount'] ?? 0.0);
        $this->valorPago  = (float)($data['amount_paid'] ?? 0.0);

        // Mapeamento dos itens
        $this->itens = collect($data['items'] ?? [])->map(function ($item) {
            return [
                'codigo_produto' => $item['code'] ?? '',
                'descricao' => $item['description'] ?? '',

                // Qtd e Preço unitário podem estar ausentes no retorno Gemini, mas são tratados na Action
                'quantidade' => (float)($item['quantity'] ?? 0.0),
                'preco_unitario' => (float)($item['unit_price'] ?? 0.0),

                'total_item' => (float)($item['total_item_value'] ?? 0.0),
                'categoria_nome_gemini' => $item['category_name'] ?? 'OUTROS',
            ];
        });
    }
}
