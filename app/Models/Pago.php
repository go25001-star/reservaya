<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
   protected $fillable = [
      'fecha_pago',
      'cantidad',
      'tipo_pago',
      'reserva_id'
   ];

   protected $casts = [
       'fecha_pago' => 'datetime',
       'cantidad'=> 'decimal:2',
   ];

    public function reserva():BelongsTo
    {
       return $this->belongsTo(Reserva::class);
    }
}
