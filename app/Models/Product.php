<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'nome',
        'descricao',
        'preco',
        'loja_id',
        'ingredientes',
        'informacoes_nutricionais',
        'porcao',
    ];

    protected $casts = [
        'informacoes_nutricionais' => 'array',
        'preco' => 'decimal:2',
    ];
}
