<?php

namespace App\Http\Controllers\NotaFiscal;

use App\Http\Controllers\Controller;
use App\Models\NotaFiscal;
use Illuminate\Http\Request;

class DeleteNotaFiscalController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $nota = NotaFiscal::find($id);

        if (!$nota) {
            return redirect()->route('view.receipt.list')->with('error', 'Nota fiscal não encontrada.');
        }

        $nota->delete();

        return redirect()->route('view.receipt.list')->with('success', 'Nota fiscal removida com sucesso.');
    }
}
