<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleReserva extends Model
{
   protected $table = 'detalle_reservas';

   protected $fillable = [
     'precio',
     'reserva_id',
     'habitacion_id'
   ];

   protected $casts = [
      'precio' => 'decimal:2',
   ];

    public function habitacion():BelongsTo
    {
        return $this->belongsTo(habitacion::class);
    }

    public function reserva():BelongsTo{
        return  $this->belongsTo(Reserva::class);
    }

 }
