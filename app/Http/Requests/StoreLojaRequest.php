<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLojaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'endereco' => 'required|string|max:255',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
