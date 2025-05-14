<?php

namespace App\Adapters\Gateways;

class MercadoPagoClient
{
    public function criarPagamentoPix(float $valor, string $descricao): array
    {
        // Mock do QR code e ID da transação
        return [
            'id' => 'pix_fake_id_'.uniqid(),
            'qr_code_base64' => 'data:image/png;base64,fakebase64qrcode=='
        ];
    }
}
