<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutSacolaRequest extends FormRequest
{
    public function rules(): array
    {
        return ['client_id' => 'required|exists:clients,id'];
    }

    public function authorize(): bool
    {
        return true;
    }
}
