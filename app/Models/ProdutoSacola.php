<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdutoSacola extends Model
{
    protected $table = 'produto_sacola';

    protected $fillable = [
        'sacola_id',
        'produto_id',
        'quantidade',
    ];

    // Relacionamentos (opcional, mas recomendado)
    public function sacola()
    {
        return $this->belongsTo(Sacola::class);
    }

    public function produto()
    {
        return $this->belongsTo(Product::class);
    }
}
