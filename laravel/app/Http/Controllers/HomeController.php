<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ItemDaCompra;
use App\Models\NotaFiscal;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __invoke(): View|Factory
    {
        return view('home',
            [
                'relatorio_categoria_pizza' => $this->dadosGraficoCategoriaPizza(),
                'itensRecentes' => $this->extratoRecente(),
            ]
        );

    }

    private function extratoRecente()
    {
        return ItemDaCompra::with(['notaFiscal', 'categoria'])
            ->join(
                'notas_fiscais',
                'itens_da_compra.nota_fiscal_id',
                '=',
                'notas_fiscais.id'
            )
            ->select('itens_da_compra.*') // Seleciona todas as colunas de item_da_compra
            ->orderBy('notas_fiscais.data_emissao', 'desc')
            ->orderBy('notas_fiscais.hora_emissao', 'desc')
            ->paginate(10);

    }

    private function dadosGraficoCategoriaPizza(): array
    {
        // 1. Agrega o valor total dos itens por categoria
        $gastosPorCategoria = ItemDaCompra::select('categorias.nome', DB::raw('SUM(itens_da_compra.total_item) as total_gasto'))
            ->join('categorias', 'itens_da_compra.categoria_id', '=', 'categorias.id')
            ->groupBy('categorias.nome')
            ->orderByDesc('total_gasto')
            ->get();

        // 2. Formata os dados para o Chart.js
        $dadosGrafico = [
            'labels' => $gastosPorCategoria->pluck('nome')->toArray(),
            'data' => $gastosPorCategoria->pluck('total_gasto')->toArray(),
        ];

        // Exemplo de outros dados de dashboard
        $totalGastoMes = NotaFiscal::whereMonth('data_emissao', now()->month)
            ->sum('valor_pago');

        return [
            'dadosGrafico' => $dadosGrafico,
            'totalGastoMes' => $totalGastoMes
        ];
    }
}
