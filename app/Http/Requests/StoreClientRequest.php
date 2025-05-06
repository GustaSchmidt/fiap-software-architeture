<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string',
            'sobrenome' => 'required|string',
            'email' => 'required|email|unique:clients,email',
            'cpf' => 'required|string|unique:clients,cpf',
            'senha' => 'required|string|size:32', // md5 hash
        ];
    }
}
