<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListPedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'loja_id' => 'required|integer|exists:lojas,id',
            'filtro.client_id' => 'nullable|integer|exists:clients,id',
            'filtro.status' => 'nullable|string|in:aguardando_pagamento,pago,em_pagamento,cancelado'
        ];
    }
}
