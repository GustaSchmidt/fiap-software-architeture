<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchLojaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|min:2'
        ];
    }
}
