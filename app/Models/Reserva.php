<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reserva extends Model
{
    protected $fillable = [
        'total_precio',
        'fecha_reserva',
        'fecha_entrada',
        'fecha_salida',
        'estado',
    ]; 
     
    protected $casts = [
        'total_precio' => 'decimal:2',
        'fecha_reserva' => 'datetime',
        'fecha_entrada' => 'date',
        'fecha_salida' => 'date',
        'estado' => 'bool'
    ];

    public function user():BelongsTo
    {
       return $this->belongsTo(User::class);
    }


    public function pago():BelongsTo
    {
          return $this->belongsTo(Pago::class);
    }
}
