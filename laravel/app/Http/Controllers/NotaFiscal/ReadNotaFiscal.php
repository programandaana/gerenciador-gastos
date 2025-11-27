<?php

namespace App\Http\Controllers\NotaFiscal;

use App\Models\NotaFiscal;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class ReadNotaFiscal
{
    public function __invoke($id): Factory|View
    {
        $notaFiscal = NotaFiscal::findOrFail($id);
        return view('receipt.read')->with('notaFiscal', $notaFiscal);
    }
}
