<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'nome', 'preco', 'categoria', 'ingredientes', 'porcao', 'informacoes_nutricionais', 'alergenicos', 'loja_id'
    ];

    protected $casts = [
        'ingredientes' => 'array',
        'informacoes_nutricionais' => 'array',
    ];
}