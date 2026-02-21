<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Facrories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffHotel extends Model
{
    use HasFactory;

    protected $table = 'staff_hotel';

    protected $fillable = [
        'rol',
        'fecha_asignacion',
        'estado',
        'hotel_id',
        'usuario_id',
    ];
    protected $casts = [
        'estado' => 'boolean',
        'fecha_asignacion' => 'date',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'hotel_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
