<?php

namespace App\Http\Controllers;

use App\Models\ItemDaCompra;
use App\Models\NotaFiscal;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __invoke(Request $request): View|Factory
    {
        $dataInicio = $request->input('data_inicio', now()->startOfMonth()->toDateString());
        $dataFim = $request->input('data_fim', now()->endOfMonth()->toDateString());

        return view('home',
            [
                'relatorio_categoria_pizza' => $this->dadosGraficoCategoriaPizza($dataInicio, $dataFim),
                'itensRecentes' => $this->extratoRecente($dataInicio, $dataFim),
                'dataInicio' => $dataInicio, // Passa as datas de volta para manter o estado no formulário
                'dataFim' => $dataFim,
            ]
        );

    }

    private function extratoRecente($dataInicio, $dataFim): LengthAwarePaginator
    {
        return ItemDaCompra::with(['notaFiscal', 'categoria'])
            ->join(
                'notas_fiscais',
                'itens_da_compra.nota_fiscal_id',
                '=',
                'notas_fiscais.id'
            )
            ->select('itens_da_compra.*') // Seleciona todas as colunas de item_da_compra
            ->whereBetween('notas_fiscais.data_emissao', [$dataInicio, $dataFim])
            ->orderBy('notas_fiscais.data_emissao', 'desc')
            ->orderBy('notas_fiscais.hora_emissao', 'desc')
            ->paginate(10)
            ->withQueryString();
    }

    private function dadosGraficoCategoriaPizza($dataInicio, $dataFim): array
    {
        // 1. Agrega o valor total dos itens por categoria
        $gastosPorCategoria = ItemDaCompra::select('categorias.nome', DB::raw('SUM(itens_da_compra.total_item) as total_gasto'))
            ->join('categorias', 'itens_da_compra.categoria_id', '=', 'categorias.id')
            ->join('notas_fiscais', 'itens_da_compra.nota_fiscal_id', '=', 'notas_fiscais.id')
            ->whereBetween('notas_fiscais.data_emissao', [$dataInicio, $dataFim])
            ->groupBy('categorias.nome')
            ->orderByDesc('total_gasto')
            ->get();

        // 2. Formata os dados para o Chart.js
        $dadosGrafico = [
            'labels' => $gastosPorCategoria->pluck('nome')->toArray(),
            'data' => $gastosPorCategoria->pluck('total_gasto')->toArray(),
        ];

        // Exemplo de outros dados de dashboard
        $totalGastoMes = NotaFiscal::whereBetween('data_emissao', [$dataInicio, $dataFim])
            ->sum('valor_pago');

        return [
            'dadosGrafico' => $dadosGrafico,
            'totalGastoMes' => $totalGastoMes
        ];
    }
}
