<?php

namespace App\Http\Controllers\NotaFiscal;

use App\Http\Controllers\Controller;
use App\Models\NotaFiscal;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class ListarNotasFiscaisController extends Controller
{
    public function __invoke(): Factory|View
    {
        $notasFiscais = NotaFiscal::with('estabelecimento')
            ->orderBy('data_emissao', 'desc')
            ->orderBy('hora_emissao', 'desc')
            ->paginate(15);

        // 2. Retorna a View, passando o objeto paginado
        return view('receipt.list', compact('notasFiscais'));
    }
}
