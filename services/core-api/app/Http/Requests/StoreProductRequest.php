<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Validar API Key aqui se necessário
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string',
            'preco' => 'required|numeric',
            'categoria' => 'required|string',
            'ingredientes' => 'required|array',
            'porcao' => 'required|string',
            'informacoes_nutricionais' => 'required|array',
            'loja_id' => 'required|integer|exists:lojas,id',
            'alergenicos' => 'nullable|string',
        ];
    }
}
