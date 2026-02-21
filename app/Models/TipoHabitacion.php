<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoHabitacion extends Model
{
    protected $table = 'tipo_habitaciones';

    protected $fillable = [
        'nombre',
        'descripcion',
        'hotel_id',
    ];

    public function habitaciones(): HasMany
    {
        return $this->hasMany(Habitacion::class);
    }
}