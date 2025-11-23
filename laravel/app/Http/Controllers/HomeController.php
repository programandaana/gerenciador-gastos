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
                'relatorio_categoria_pizza' => $this->dadosGraficoCategoriaPizza()
            ]
        );

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
