<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estabelecimento extends Model
{
    use HasFactory;

    protected $fillable = ['nome', 'cnpj', 'endereco'];

    public function notasFiscais(): HasMany
    {
        // Um Estabelecimento tem muitas Notas Fiscais (One-to-Many)
        return $this->hasMany(NotaFiscal::class);
    }
}
