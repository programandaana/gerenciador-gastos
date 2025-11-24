<?php

namespace App\Http\Controllers\NotaFiscal;

use App\Exceptions\NotaJaExistente;
use App\Gateways\SaveReceiptDataGateway;
use App\Http\Controllers\Controller;
use App\Services\OcrService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UploadReceiptController extends Controller
{
    /**
     * Processa a requisição de upload da imagem.
     * @param Request $request
     * @param OcrService $ocrService
     * @param SaveReceiptDataGateway $gateway
     * @return RedirectResponse
     */
    public function __invoke(
        Request $request,
        OcrService $ocrService,
        SaveReceiptDataGateway $gateway
    ): RedirectResponse {
        // 1. Validação do Upload
        try {
            // A validação de Illuminate\Http\Request lança ValidationException.
            $request->validate(['nota_imagem' => 'required|file|mimes:jpeg,png,webp|max:5120']);
        } catch (ValidationException $e) {
            // Se falhar a validação, redireciona de volta com os erros.
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        $imageFile = $request->file('nota_imagem');

        try {
            // 2. Extração de Dados (chama o Service)
            $ocrResultDTO = $ocrService->extrairDados($imageFile);

            if (strlen($ocrResultDTO->chaveAcesso) != 44) {
                return redirect()->back()->withErrors(
                    ['Não foi possível extrair a chave de acesso da nota.']
                )->withInput();
            }

            // 3. Persistência de Dados (chama o Gateway)
            $notaFiscal = $gateway->execute($ocrResultDTO);

            // 4. Retorno de Sucesso (Redireciona para uma rota de sucesso)
            $mensagemSucesso = "Nota Fiscal (Chave: {$notaFiscal->chave_acesso}) processada e salva com sucesso! Total: R$ " . number_format($notaFiscal->valor_pago, 2, ',', '.');

            return redirect()->route('view.receipt.upload')->with('success', $mensagemSucesso);

        } catch (NotaJaExistente $e) {
            return redirect()->route('view.receipt.upload')->with('error', $e->getMessage());
        } catch (Exception $e) {
            // 5. Tratamento de Erro (Se o Gemini falhar ou o DB falhar)
            Log::error('Erro fatal ao processar nota fiscal:', ['message' => $e->getMessage()]);

            // Redireciona de volta com a mensagem de erro detalhada.
            $mensagemErro = "Falha ao processar a nota fiscal. Detalhes: " . substr($e->getMessage(), 0, 150) . '...';

            return redirect()->route('view.receipt.upload')->with('error', $mensagemErro);
        }
    }
}
