<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Habitacion extends Model
{
   
    protected $table = 'habitaciones';

    protected $fillable = [
        'nombre_habitacion',
        'estado',
        'num_habitacion',
        'precio',
        'capacidad',
        'tipo_habitacion_id',
        'hotel_id',
    ];

     public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
    
    public function tipoHabitacion(): BelongsTo
    {
        return $this->belongsTo(TipoHabitacion::class, 'tipo_habitacion_id');
    }

    public function detalleReservas():HasMany{

        return $this->hasMany(DetalleReserva::class);
    }
   

    public  function imagenes():HasMany
    {
        return $this->hasMany(HabitacionImagen::class);
    }

}