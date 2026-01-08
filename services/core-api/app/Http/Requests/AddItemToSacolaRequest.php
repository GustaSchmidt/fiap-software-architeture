<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddItemToSacolaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => 'required|integer|exists:clients,id',  // Verifica se o cliente existe
            'produto_id' => 'required|integer|exists:products,id',  // Verifica se o produto existe
            'quantidade' => 'required|integer|min:1',  // Verifica se a quantidade é válida
        ];
    }
}
