<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Facrories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function hotel():BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
