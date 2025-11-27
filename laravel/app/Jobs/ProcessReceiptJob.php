<?php

namespace App\Jobs;

use App\Exceptions\GeminiQuotaExceededException;
use App\Gateways\SaveReceiptDataGateway;
use App\Models\JobStatus;
use App\Services\OcrService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessReceiptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $imagePath,
        public string $jobStatusUuid
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(OcrService $ocrService, SaveReceiptDataGateway $saveReceiptDataGateway): void
    {
        Log::info("ProcessReceiptJob iniciado para UUID: {$this->jobStatusUuid}");

        $jobStatus = JobStatus::where('uuid', $this->jobStatusUuid)->first();

        if (!$jobStatus) {
            Log::error("Registro de status de processamento (UUID: {$this->jobStatusUuid}) não encontrado.");
            $this->fail(new \Exception("Erro interno: O status da sua solicitação não pôde ser encontrado."));
            return;
        }

        try {
            $jobStatus->update(['status' => 'processing', 'message' => 'Lendo informações da imagem da nota fiscal...']);

            $ocrResultDTO = $ocrService->extrairDados($this->imagePath);
            $notaFiscal = $saveReceiptDataGateway->execute($ocrResultDTO);

            $jobStatus->update([
                'status' => 'completed',
                'message' => 'Nota fiscal processada e salva com sucesso!',
                'result' => $notaFiscal->toArray(),
            ]);

            Log::info("ProcessReceiptJob concluído com sucesso para UUID: {$this->jobStatusUuid}");

        } catch (Throwable $e) {
            // Apenas falha o job, a lógica de tratamento de falha fica no método failed()
            $this->fail($e);
        } finally {
            // Limpa o arquivo temporário
            if (Storage::disk('private')->exists($this->imagePath)) {
                Storage::disk('private')->delete($this->imagePath);
                Log::info("Arquivo temporário {$this->imagePath} excluído.");
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Erro no processamento da nota fiscal na fila:', [
            'message' => $exception->getMessage(),
            'job_status_uuid' => $this->jobStatusUuid,
            'exception' => $exception,
        ]);

        $jobStatus = JobStatus::where('uuid', $this->jobStatusUuid)->first();

        if ($jobStatus) {
            $userMessage = 'Ocorreu um erro inesperado. Por favor, tente novamente.';

            if ($exception instanceof GeminiQuotaExceededException || $exception instanceof \App\Exceptions\NotaJaExistente) {
                $userMessage = $exception->getMessage();
            }

            $jobStatus->update([
                'status' => 'failed',
                'message' => $userMessage,
                'result' => ['error' => $exception->getMessage()],
            ]);
        }
    }
}