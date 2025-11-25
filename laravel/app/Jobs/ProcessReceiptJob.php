<?php

namespace App\Jobs;

use App\Gateways\SaveReceiptDataGateway;
use App\Services\OcrService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\JobStatus;
use App\Exceptions\GeminiQuotaExceededException; // Import the custom exception

class ProcessReceiptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $imagePath;
    protected string $jobStatusUuid;

    /**
     * Create a new job instance.
     */
    public function __construct(string $imagePath, string $jobStatusUuid)
    {
        $this->imagePath = $imagePath;
        $this->jobStatusUuid = $jobStatusUuid;
    }

    /**
     * Execute the job.
     */
    public function handle(OcrService $ocrService, SaveReceiptDataGateway $saveReceiptDataGateway): void
    {
        Log::info("ProcessReceiptJob iniciado para UUID: {$this->jobStatusUuid}"); // Log de início

        $jobStatus = JobStatus::where('uuid', $this->jobStatusUuid)->first();

        if (!$jobStatus) {
            // Este é um erro interno grave, unlikely to be shown to the user
            Log::error("Registro de status de processamento (UUID: {$this->jobStatusUuid}) não encontrado. Caminho da Imagem: {$this->imagePath}");
            $this->fail(new \Exception("Erro interno: O status da sua solicitação não pôde ser encontrado."));
            return;
        }

        try {
            $jobStatus->update(['status' => 'processing', 'message' => 'Lendo informações da imagem da nota fiscal...']);

            // 1. Read the image from storage (Extração do conteúdo do arquivo)
            // Removido, pois extrairDados já lê o caminho
            // $imageContent = Storage::disk('private')->get($this->imagePath);

            // 2. Process with OCR Service
            $ocrResultDTO = $ocrService->extrairDados($this->imagePath);

            // 3. Save to Database
            $notaFiscal = $saveReceiptDataGateway->execute($ocrResultDTO);

            // 4. Update Job Status
            $jobStatus->update([
                'status' => 'completed',
                'message' => 'Nota fiscal processada e salva com sucesso!',
                'result' => $notaFiscal->toArray(),
            ]);
            Log::info("ProcessReceiptJob concluído com sucesso para UUID: {$this->jobStatusUuid}");

        } catch (GeminiQuotaExceededException $e) {
            Log::warning("Quota Gemini excedida para UUID: {$this->jobStatusUuid}. Erro: {$e->getMessage()}");
            $jobStatus->update([
                'status' => 'failed',
                'message' => $e->getMessage(), // User-friendly message from the exception
                'result' => ['error' => $e->getMessage()],
            ]);
            $this->fail($e); // Mark the job as failed
        } catch (\App\Exceptions\NotaJaExistente $e) {
            Log::warning("Nota Fiscal já existente para UUID: {$this->jobStatusUuid}. Erro: {$e->getMessage()}");
            $jobStatus->update([
                'status' => 'failed',
                'message' => $e->getMessage(),
                'result' => ['error' => $e->getMessage()],
            ]);
            $this->fail($e); // Mark the job as failed
        } catch (\Exception $e) {
            Log::error('Erro inesperado no processamento da nota fiscal na fila:', [ // Alterado 'job' para 'fila'
                'message' => $e->getMessage(),
                'job_status_uuid' => $this->jobStatusUuid,
                'exception' => $e,
            ]);
            $jobStatus->update([
                'status' => 'failed',
                // Mensagem mais amigável para o usuário
                'message' => 'Ocorreu um erro inesperado ao processar sua nota fiscal. Por favor, tente novamente mais tarde.',
                'result' => ['error' => $e->getMessage()], // Manter o detalhe técnico no result para debug
            ]);
            $this->fail($e); // Mark the job as failed
        } finally {
            // Clean up: Delete the temporary image file
            if (Storage::disk('private')->exists($this->imagePath)) {
                 Storage::disk('private')->delete($this->imagePath);
                 Log::info("Arquivo temporário {$this->imagePath} excluído.");
            }
        }
    }
}