<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Sacola extends Model
{
    protected $table = 'sacolas';

    protected $fillable = ['usuario_id'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withPivot('quantidade');
    }
}
