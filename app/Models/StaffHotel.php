<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;//por lo que sale modificado es por qeu avia un error de sintaxis
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Traits\HasRoles;

class StaffHotel extends Model{
   
    protected $table = 'staff_hotel';

    protected $fillable = [
        'rol',
        'fecha_asignacion',
        'estado',
        'hotel_id',
        'user_id',
    ];
    protected $casts = [
        'estado' => 'boolean',
        'fecha_asignacion' => 'date',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
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
