<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $fillable = [
        'client_id',
        'sacola_id',
        'status',
        'total',
        'mercado_pago_id',
    ];
}
