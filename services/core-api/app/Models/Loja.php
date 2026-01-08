<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loja extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'endereco',
    ];

    /**
     * Defina o relacionamento com os produtos (1:N)
     */
    public function produtos()
    {
        return $this->hasMany(Product::class, 'loja_id');
    }
}
