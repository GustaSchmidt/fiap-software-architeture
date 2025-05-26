<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    protected $table = 'api_keys';

    protected $fillable = [
        'name',
        'key',
        'role',
        'role_id_loja_cliente',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
