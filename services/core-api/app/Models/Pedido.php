<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pedido extends Model
{
    protected $fillable = [
        'client_id',
        'sacola_id',
        'status',
        'total',
        'mercado_pago_id',
    ];

    public function sacola(): BelongsTo
    {
        return $this->belongsTo(Sacola::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
