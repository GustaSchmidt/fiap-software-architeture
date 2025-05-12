<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddItemToSacolaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'cliente_id' => 'required|integer|exists:clients,id',
            'produto_id' => 'required|integer|exists:products,id',
            'quantidade' => 'required|integer|min:1',
        ];
    }

    public function authorize(): bool
    {
        // Se necessário, insira a lógica de autorização aqui.
        return true;
    }
}
