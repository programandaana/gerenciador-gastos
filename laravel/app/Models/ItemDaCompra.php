<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemDaCompra extends Model
{
    use HasFactory;

    protected $table = 'itens_da_compra';

    protected $fillable = [
        'nota_fiscal_id',
        'codigo_produto',
        'descricao',
        'quantidade',
        'preco_unitario',
        'total_item',
        'categoria_id'
    ];

    public function notaFiscal(): BelongsTo
    {
        // Um Item pertence a uma Nota Fiscal (Many-to-One)
        return $this->belongsTo(NotaFiscal::class);
    }

    public function categoria(): BelongsTo
    {
        // Um Item pertence a uma Categoria (Many-to-One)
        return $this->belongsTo(Categoria::class);
    }
}
