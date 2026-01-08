<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ou lógica de autorização se necessário
    }

    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:products,id',
            'nome' => 'required|string|max:255',
            'preco' => 'required|numeric|min:0',
            'categoria' => 'required|string|max:255',
            'ingredientes' => 'required|array',
            'ingredientes.*' => 'string|max:255',
            'porcao' => 'required|string|max:255',
            'informacoes_nutricionais' => 'required|array',
            'informacoes_nutricionais.calorias' => 'required|integer|min:0',
            'informacoes_nutricionais.proteinas' => 'required|numeric|min:0',
            'informacoes_nutricionais.gorduras' => 'required|numeric|min:0',
            'informacoes_nutricionais.carboidratos' => 'required|numeric|min:0',
            'alergenicos' => 'nullable|string',
            'loja_id' => 'required|integer|exists:lojas,id',
        ];
    }
}
