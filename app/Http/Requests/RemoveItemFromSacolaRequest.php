<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RemoveItemFromSacolaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => 'required|integer|exists:clients,id',
            'produto_id' => 'required|integer|exists:products,id',
        ];
    }
}
