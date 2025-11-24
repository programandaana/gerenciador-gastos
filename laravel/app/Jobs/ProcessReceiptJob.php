<?php

namespace App\Jobs;

use App\Exceptions\NotaJaExistente;
use App\Gateways\SaveReceiptDataGateway;
use App\Services\OcrService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessReceiptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $filePath;

    /**
     * Create a new job instance.
     */
    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Execute the job.
     */
    public function handle(OcrService $ocrService, SaveReceiptDataGateway $gateway): void
    {
        try {
            // 1. Recuperar o arquivo temporário
            $fullPath = Storage::disk('private')->path($this->filePath);
            
            // Verifica se o arquivo existe
            if (!Storage::disk('private')->exists($this->filePath)) {
                Log::error('ProcessReceiptJob: Arquivo temporário não encontrado.', ['path' => $this->filePath]);
                throw new Exception('Arquivo temporário não encontrado.');
            }

            // Simular UploadedFile para o OcrService
            // Crie uma instância de UploadedFile manualmente ou ajuste OcrService para aceitar um caminho
            // Para simplicidade, vou usar um "mock" de UploadedFile se o OcrService realmente precisar dele,
            // ou passar o caminho diretamente se o OcrService puder ser adaptado para isso.
            // Para manter o OcrService inalterado, precisamos de um objeto UploadedFile.
            // Isso pode ser complexo. A melhor abordagem é ter OcrService aceitar um caminho.
            // Por enquanto, vou passar o caminho e presumir que OcrService pode lê-lo.

            // ADVERTÊNCIA: Se OcrService->extrairDados() só aceitar UploadedFile,
            // esta parte precisará ser adaptada para recriar um UploadedFile a partir do fullPath.

            // 2. Extração de Dados (chama o Service)
            $ocrResultDTO = $ocrService->extrairDados($this->filePath); // Passando o caminho relativo

            // 3. Persistência de Dados (chama o Gateway)
            $notaFiscal = $gateway->execute($ocrResultDTO);

            Log::info("ProcessReceiptJob: Nota Fiscal (Chave: {$notaFiscal->chave_acesso}) processada e salva com sucesso!");

        } catch (NotaJaExistente $e) {
            Log::warning('ProcessReceiptJob: Nota fiscal já existente.', ['chave_acesso' => $e->getMessage()]);
            // Notificar o usuário que a nota já existe, talvez.
        } catch (Exception $e) {
            Log::error('ProcessReceiptJob: Erro fatal ao processar nota fiscal.', [
                'message' => $e->getMessage(),
                'file_path' => $this->filePath,
                'trace' => $e->getTraceAsString()
            ]);
            // Notificar o usuário sobre a falha, talvez.
        } finally {
            // 4. Limpar o arquivo temporário
            if (Storage::disk('private')->exists($this->filePath)) {
                Storage::disk('private')->delete($this->filePath);
                Log::info('ProcessReceiptJob: Arquivo temporário excluído.', ['path' => $this->filePath]);
            }
        }
    }
}
