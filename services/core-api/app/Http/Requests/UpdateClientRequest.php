<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Pode ser false se houver regras de autorização
    }

    public function rules(): array
    {
        return [
            'id' => 'required|exists:clients,id', // Verifica se o cliente existe
            'nome' => 'nullable|string',
            'sobrenome' => 'nullable|string',
            'email' => 'nullable|email|unique:clients,email,' . $this->id, // Mantém o email único
            'cpf' => 'nullable|string|unique:clients,cpf,' . $this->id, // Mantém o CPF único
            'senha' => 'nullable|string|size:32', // md5 hash
        ];
    }
}
