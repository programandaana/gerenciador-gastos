<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotaFiscal extends Model
{
    use HasFactory;

    protected $table = 'notas_fiscais';

    protected $fillable = [
        'estabelecimento_id',
        'chave_acesso',
        'data_emissao',
        'hora_emissao',
        'total_bruto',
        'descontos',
        'valor_pago'
    ];

    public function estabelecimento(): BelongsTo
    {
        // Uma Nota Fiscal pertence a um Estabelecimento (Many-to-One)
        return $this->belongsTo(Estabelecimento::class);
    }

    public function itens(): HasMany
    {
        // Uma Nota Fiscal tem muitos Itens (One-to-Many)
        return $this->hasMany(ItemDaCompra::class);
    }
}
