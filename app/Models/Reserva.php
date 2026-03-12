<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reserva extends Model
{
    protected $fillable = [
        'total_precio',
        'fecha_reserva',
        'fecha_entrada',
        'fecha_salida',
        'estado',
        'user_id',//en esta parte hacia falta el id de user
    ];

    protected $casts = [
        'total_precio' => 'decimal:2',
        'fecha_reserva' => 'datetime',
        'fecha_entrada' => 'date',
        'fecha_salida' => 'date',
        'estado' => 'string'//aqui basandome en la base de datos lo canbie por string ya que en la base de datos tiene varchar"50"
    ];
    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleReserva ::class,'reserva_id');//agregaue el puente qeu permite la comunicacion de las tablas
    }

    public function user():BelongsTo
    {
       return $this->belongsTo(User::class);
    }


    public function pago():BelongsTo
    {
          return $this->belongsTo(Pago::class);
    }
}
