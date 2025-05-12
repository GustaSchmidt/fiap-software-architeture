<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    // Nome da tabela no banco de dados
    protected $table = 'clients';

    // Atributos que podem ser preenchidos em massa
    protected $fillable = [
        'nome', 'sobrenome', 'email', 'cpf', 'senha'
    ];

    /**
     * Relacionamento de um para muitos entre Cliente e Sacola.
     * Um cliente pode ter muitas sacolas.
     */
    public function sacolas()
    {
        return $this->hasMany(Sacola::class);
    }
}
