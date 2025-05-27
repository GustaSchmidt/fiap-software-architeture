<?php

namespace App\Adapters\Gateways;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str; // Para a chave de idempotência
use Exception;

class MercadoPagoClient
{
    private string $accessToken;
    private string $baseApiUrl = 'https://api.mercadopago.com/v1';

    public function __construct()
    {
        $this->accessToken = config('services.mercadopago.access_token');
        if (empty($this->accessToken)) {
            throw new Exception('Access Token do Mercado Pago não configurado.');
        }
    }

    /**
     * Cria um pagamento PIX no Mercado Pago via API HTTP direta.
     *
     * @param float $valor O valor do pagamento.
     * @param string $descricao A descrição do pagamento.
     * @param array $payerInfo Informações do pagador (email, first_name, last_name, identification_type, identification_number).
     * @param string|null $externalReference Uma referência externa para o pagamento (opcional).
     * @param string|null $notificationUrl URL para receber notificações de status do pagamento (opcional).
     * @return array Retorna um array com 'id' do pagamento, 'status', 'qr_code_base64' e 'qr_code_text'.
     * @throws Exception Se ocorrer um erro na criação do pagamento.
     */
    public function criarPagamentoPix(
        float $valor,
        string $descricao,
        array $payerInfo,
        ?string $externalReference = null,
        ?string $notificationUrl = null
    ): array
    {
        $apiUrl = $this->baseApiUrl . '/payments';
        $idempotencyKey = (string) Str::uuid();

        // Validar informações do pagador
        if (empty($payerInfo['email'])) {
            throw new Exception('O e-mail do pagador é obrigatório para pagamento PIX.');
        }
        // É recomendado que CPF/CNPJ também sejam obrigatórios para PIX.
        if (empty($payerInfo['identification_type']) || empty($payerInfo['identification_number'])) {
            Log::warning('Tentativa de criar PIX sem tipo/número de identificação do pagador.', ['payerInfo' => $payerInfo]);
            // Dependendo da sua política, você pode querer lançar uma exceção aqui também.
            // throw new Exception('Tipo e número de identificação do pagador são obrigatórios.');
        }


        $payload = [
            'transaction_amount' => round($valor, 2),
            'description' => $descricao,
            'payment_method_id' => 'pix',
            'payer' => [
                'email' => $payerInfo['email'],
            ],
            // Data de expiração do QR Code (ex: 1 hora a partir de agora)
            // Formato: ISO 8601 (YYYY-MM-DDThh:mm:ss.SSSZ)
            // Exemplo: "2023-01-01T12:00:00.000-03:00" (com timezone) ou "2023-01-01T15:00:00.000Z" (UTC)
            'date_of_expiration' => now()->addHour()->setTimezone('UTC')->format('Y-m-d\TH:i:s.vP'),
        ];

        if (!empty($payerInfo['first_name'])) {
            $payload['payer']['first_name'] = $payerInfo['first_name'];
        }
        if (!empty($payerInfo['last_name'])) {
            $payload['payer']['last_name'] = $payerInfo['last_name'];
        }
        if (!empty($payerInfo['identification_type']) && !empty($payerInfo['identification_number'])) {
            $payload['payer']['identification'] = [
                'type' => $payerInfo['identification_type'],
                'number' => preg_replace('/\D/', '', $payerInfo['identification_number']), // Apenas números
            ];
        }

        if ($externalReference) {
            $payload['external_reference'] = $externalReference;
        }
        if ($notificationUrl) {
            $payload['notification_url'] = $notificationUrl;
        }
        // Adicionando um campo de metadados para rastreabilidade, se necessário
        $payload['metadata'] = [
            'origin' => 'food_delivery_backend',
            'internal_reference' => $externalReference ?? ('sacola_' . uniqid())
        ];


        Log::info('Enviando requisição de pagamento PIX para Mercado Pago', [
            'url' => $apiUrl,
            'payload' => $payload, // Cuidado ao logar dados sensíveis como PII em produção.
            'idempotency_key' => $idempotencyKey
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
                'X-Idempotency-Key' => $idempotencyKey,
            ])->timeout(30) // Definir um timeout para a requisição (em segundos)
              ->post($apiUrl, $payload);

            Log::info('Resposta recebida do Mercado Pago', [
                'status_code' => $response->status(),
                'body' => $response->json() // Logar o corpo da resposta pode ser útil para debug
            ]);

            if ($response->failed()) {
                $errorData = $response->json();
                $errorMessage = 'Erro na API do Mercado Pago: ' . ($errorData['message'] ?? 'Erro desconhecido');
                if (isset($errorData['causes']) && is_array($errorData['causes'])) {
                    foreach ($errorData['causes'] as $cause) {
                        $errorMessage .= ' | Causa: ' . ($cause['description'] ?? json_encode($cause));
                    }
                }
                Log::error($errorMessage, [
                    'status_code' => $response->status(),
                    'response_body' => $errorData,
                    'request_payload' => $payload // Cuidado com dados sensíveis
                ]);
                throw new Exception($errorMessage, $response->status());
            }

            $responseData = $response->json();

            if (empty($responseData['id']) || empty($responseData['point_of_interaction']['transaction_data']['qr_code_base64']) || empty($responseData['point_of_interaction']['transaction_data']['qr_code'])) {
                Log::error('Resposta da API do Mercado Pago não contém os dados PIX esperados.', ['response_data' => $responseData]);
                throw new Exception('Dados PIX não encontrados na resposta do Mercado Pago.');
            }

            return [
                'id' => $responseData['id'],
                'status' => $responseData['status'],
                'qr_code_base64' => $responseData['point_of_interaction']['transaction_data']['qr_code_base64'],
                'qr_code_text' => $responseData['point_of_interaction']['transaction_data']['qr_code'],
                'transaction_amount' => $responseData['transaction_amount'],
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Erro de conexão com a API do Mercado Pago: ' . $e->getMessage(), ['exception' => $e]);
            throw new Exception('Erro de comunicação com o gateway de pagamento. Tente novamente mais tarde.', 0, $e);
        } catch (Exception $e) {
            // Se já não for uma exceção com mensagem específica, relança.
            // As exceções de 'Erro na API do Mercado Pago' e 'Dados PIX não encontrados' já têm mensagens boas.
            // A exceção de 'Erro de conexão' também.
            // Este catch é mais para exceções inesperadas durante o processamento da resposta.
            if ($e->getMessage() !== 'Erro de comunicação com o gateway de pagamento. Tente novamente mais tarde.' &&
                !str_starts_with($e->getMessage(), 'Erro na API do Mercado Pago:') &&
                $e->getMessage() !== 'Dados PIX não encontrados na resposta do Mercado Pago.') {
                Log::error('Exceção inesperada no MercadoPagoClient: ' . $e->getMessage(), ['exception_trace' => $e->getTraceAsString()]);
                throw new Exception('Ocorreu um erro inesperado ao processar o pagamento: ' . $e->getMessage(), $e->getCode(), $e);
            }
            throw $e; // Relança a exceção original se já for específica
        }
    }

    public function getPaymentDetails(string $paymentId): array
    {
        $apiUrl = $this->baseApiUrl . '/payments/' . $paymentId;

        Log::info('Buscando detalhes do pagamento no Mercado Pago', ['url' => $apiUrl, 'payment_id' => $paymentId]);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
            ])->timeout(20)->get($apiUrl); // Timeout em segundos

            Log::info('Resposta de getPaymentDetails do Mercado Pago', ['status_code' => $response->status(), 'body' => $response->json()]);

            if ($response->failed()) {
                $errorData = $response->json();
                $errorMessage = 'Erro ao buscar detalhes do pagamento MP: ' . ($errorData['message'] ?? 'Desconhecido');
                Log::error($errorMessage, ['status' => $response->status(), 'response' => $errorData, 'payment_id' => $paymentId]);
                throw new Exception($errorMessage, $response->status());
            }
            return $response->json();
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Erro de conexão MP (getPaymentDetails): ' . $e->getMessage(), ['exception' => $e]);
            throw new Exception('Erro de comunicação com gateway (detalhes).', 0, $e);
        } catch (Exception $e) {
            // Relança se não for uma das exceções já tratadas acima
            if ($e->getMessage() !== 'Erro de comunicação com gateway (detalhes).' && !str_starts_with($e->getMessage(), 'Erro ao buscar detalhes do pagamento MP:')) {
            Log::error('Exceção inesperada MP (getPaymentDetails): ' . $e->getMessage());
            }
            throw $e;
        }
    }
}