<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    use HasFactory;

    protected $table = 'categorias';

    protected $fillable = ['nome', 'slug'];

    public function itens(): HasMany
    {
        // Uma Categoria tem muitos ItensDaCompra
        return $this->hasMany(ItemDaCompra::class);
    }
}
