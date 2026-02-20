<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Habitacion extends Model
{
   
    protected $table = 'habitaciones';

    protected $fillable = [
        'nombre_habitacion',
        'descripcion',
        'estado',
        'num_habitacion',
        'precio',
        'capacidad',
        'tipo_habitaciones_id',
        'hotel_id',
    ];

    
    public function tipoHabitacion(): BelongsTo
    {
        return $this->belongsTo(TipoHabitacion::class, 'tipo_habitaciones_id');
    }

    
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
}