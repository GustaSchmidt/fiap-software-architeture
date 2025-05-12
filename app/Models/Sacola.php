<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sacola extends Model
{
    use HasFactory;

    // Nome da tabela no banco de dados
    protected $table = 'sacolas';

    // Atributos que podem ser preenchidos em massa
    protected $fillable = [
        'cliente_id',
        'status',
        'total',
    ];

    // Defina as relações de Eloquent

    /**
     * Relacionamento de muitos para muitos entre Sacola e Produto.
     * A sacola pode ter muitos produtos.
     */
    public function produtos()
    {
        return $this->belongsToMany(Produto::class, 'produto_sacola')
                    ->withPivot('quantidade') // Adiciona a quantidade na tabela de relacionamento
                    ->withTimestamps(); // Se necessário para manter os timestamps na tabela de relacionamento
    }

    /**
     * Relacionamento com a tabela cliente (se existir).
     * Uma sacola pertence a um cliente.
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // Se necessário, você pode adicionar métodos personalizados para cálculos ou lógica da sacola
    public function calcularTotal()
    {
        $total = 0;
        foreach ($this->produtos as $produto) {
            $total += $produto->preco * $produto->pivot->quantidade; // Multiplica o preço pelo número de unidades
        }
        return $total;
    }
}
