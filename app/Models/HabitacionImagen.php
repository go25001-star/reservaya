<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HabitacionImagen extends Model
{
    protected $table = 'habitaciones_imagen';

    protected $fillable = [
        'url',
        'habitacion_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];


    public function habitacion():BelongsTo
    {
      return $this->belongsTo(Habitacion::class);   
    }

}
