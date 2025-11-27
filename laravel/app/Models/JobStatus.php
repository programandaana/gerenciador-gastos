<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class JobStatus extends Model
{
    protected $table = 'job_statuses';
    protected $primaryKey = 'uuid'; // Define 'uuid' como chave primária
    public $incrementing = false; // Indica que a chave primária não é auto-incrementável
    protected $keyType = 'string'; // Define o tipo da chave primária como string

    protected $fillable = [
        'uuid',
        'status',
        'message',
        'result',
    ];

    protected $casts = [
        'result' => 'array',
    ];

    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn($value) => \Carbon\Carbon::parse($value)->format('d/m/Y H:i:s'),
        );
    }}
