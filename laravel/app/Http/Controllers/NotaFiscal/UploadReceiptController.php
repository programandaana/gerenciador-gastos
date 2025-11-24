<?php

namespace App\Http\Controllers\NotaFiscal;

use App\Exceptions\NotaJaExistente;
use App\Gateways\SaveReceiptDataGateway;
use App\Http\Controllers\Controller;
use App\Services\OcrService;
use Exception;
use App\Jobs\ProcessReceiptJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage; // Adicionado para manipulação de arquivos

class UploadReceiptController extends Controller
{
    /**
     * Processa a requisição de upload da imagem.
     * @param Request $request
     * @return RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function __invoke(
        Request $request
    ): RedirectResponse|\Illuminate\Http\JsonResponse {
        try {
            // A validação de Illuminate\Http\Request lança ValidationException.
            $request->validate(['nota_imagem' => 'required|file|mimes:jpeg,png,webp|max:5120']);
        } catch (ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
            // Se falhar a validação, redireciona de volta com os erros.
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        $imageFile = $request->file('nota_imagem');
        
        try {
            // Salva o arquivo temporariamente e obtém o caminho
            $path = $imageFile->store('receipt_uploads', 'private'); // 'private' disk para arquivos temporários

            // Despacha o job para processar em segundo plano
            ProcessReceiptJob::dispatch($path);

            $mensagemSucesso = "Arquivo enviado para processamento em segundo plano. Você será notificado sobre o resultado.";

            if ($request->wantsJson()) {
                return response()->json(['success' => $mensagemSucesso]);
            }

            return redirect()->route('view.receipt.upload')->with('success', $mensagemSucesso);

        } catch (Exception $e) {
            // 5. Tratamento de Erro
            Log::error('Erro ao despachar job de nota fiscal:', ['message' => $e->getMessage()]);

            $mensagemErro = "Falha ao enviar o arquivo para processamento. Detalhes: " . substr($e->getMessage(), 0, 150) . '...';

            if ($request->wantsJson()) {
                return response()->json(['error' => $mensagemErro], 500);
            }
            return redirect()->route('view.receipt.upload')->with('error', $mensagemErro);
        }
    }
}
