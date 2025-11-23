<?php

namespace App\Services;

use App\DTOs\OcrResultDTO;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class OcrService
{
    private string $apiKey;
    private string $model = 'gemini-2.5-flash';
    private string $url;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
        if (empty($this->apiKey)) {
            throw new Exception("A chave GEMINI_API_KEY não está configurada no arquivo .env.");
        }
        $this->url = "https://generativelanguage.googleapis.com/v1/models/{$this->model}:generateContent";
    }

    /**
     * @throws Exception
     */
    public function extrairDados(UploadedFile $imageFile): OcrResultDTO
    {
        $imageData = base64_encode(file_get_contents($imageFile->getRealPath()));
        $mimeType = $imageFile->getMimeType();

        // 1. Schema JSON string (simplificado para o prompt)
        // app/Services/OcrService.php (Substituir o array que gera o $schemaJsonString)

        // 1. Define o Schema JSON que será injetado no prompt
        $schemaArray = [
            'company_name'        => ['type' => 'string'],
            'cnpj'                => ['type' => 'string'],
            'company_address'     => ['type' => 'string'],
            'access_key'          => ['type' => 'string'],
            'transaction_date'    => ['type' => 'string', 'description' => 'YYYY-MM-DD'],
            'transaction_time'    => ['type' => 'string', 'description' => 'HH:MM:SS'],
            'total_item_discount' => ['type' => 'number'],

            // Bloco opcional para dados NFCe (necessário para o DTO)
            'nfce_data' => [
                'type' => 'object',
                'properties' => [
                    'issuance_date' => ['type' => 'string', 'description' => 'YYYY-MM-DD'],
                    'issuance_time' => ['type' => 'string', 'description' => 'HH:MM:SS'],
                    'access_key'    => ['type' => 'string']
                ],
            ],

            'total_value' => ['type' => 'number'],
            'amount_paid' => ['type' => 'number'],

            'items' => [
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'properties' => [
                        'code'             => ['type' => 'string'],
                        'description'      => ['type' => 'string'],
                        'quantity'         => ['type' => 'number'],
                        'unit_price'       => ['type' => 'number'],
                        'total_item_value' => ['type' => 'number'],
                        'category_name' => [
                            'type'        => 'string',
                            'description' => 'Classifique o produto usando ALIMENTOS, BEBIDAS, LIMPEZA, HIGIENE ou OUTROS; se nenhuma categoria servir, crie uma nova categoria clara e geral, no mesmo formato.'
                        ],
                    ]
                ]
            ]
        ];

        // Converte o array PHP do schema para uma string JSON (necessária para o prompt)
        $schemaJsonString = json_encode($schemaArray, JSON_UNESCAPED_SLASHES);

        // 2. Monta o corpo da requisição
        $requestBody = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => "Você é um extrator de dados de notas fiscais. **Sua única resposta deve ser um array JSON contendo um único objeto**. Analise a imagem e retorne APENAS o JSON que adere estritamente à estrutura: {$schemaJsonString}. Inclua todas as chaves solicitadas e todos os itens. Extraia datas para YYYY-MM-DD e horas para HH:MM:SS.",
                        ],
                        [
                            'inlineData' => ['mimeType' => $mimeType, 'data' => $imageData,],
                        ],
                    ],
                ],
            ],
            // 'config' removido para evitar o erro HTTP 400.
        ];

        try {
            // 3. Envia a requisição com TIMEOUT AUMENTADO (90 segundos)
            $response = Http::timeout(90)->post("{$this->url}?key={$this->apiKey}", $requestBody);

            if ($response->failed()) {
                throw new Exception("Erro HTTP da API Gemini: Status {$response->status()} - " . $response->body());
            }

            // 4. Extração do texto JSON e decodificação
            $responseText = $response->json('candidates')[0]['content']['parts'][0]['text'] ?? '{}';
            $cleanedJsonText = trim(str_replace(['```json', '```'], '', $responseText));

            //dd($cleanedJsonText);
            $jsonResponseRaw = json_decode($cleanedJsonText, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("A API Gemini não retornou um JSON válido. Retorno: " . $responseText);
            }

            // 5. CORREÇÃO: Pega o primeiro objeto do array e passa para o DTO
            if (is_array($jsonResponseRaw) && isset($jsonResponseRaw[0]) && is_array($jsonResponseRaw[0])) {
                $jsonResponse = $jsonResponseRaw[0];
            } else {
                $jsonResponse = $jsonResponseRaw;
            }

            return new OcrResultDTO($jsonResponse);

        } catch (Exception $e) {
            throw new Exception("Falha na extração OCR via Gemini: " . $e->getMessage());
        }
    }
}
