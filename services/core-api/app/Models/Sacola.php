<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Sacola extends Model
{
    protected $table = 'sacolas';

    protected $fillable = ['client_id', 'status', 'total'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_sacola')
            ->withPivot('quantidade')
            ->withTimestamps();
    }
}
