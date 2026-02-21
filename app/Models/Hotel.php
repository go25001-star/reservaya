<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    use HasFactory;

    protected $table = 'hoteles';
    protected $fillable = [
    'nombre',
    'descripcion',
    'direccion',
    'imagen',
    'email',
    'telefono',
    'telefono2',
    'telefono3',
    ];

    public function staff_hotel ()
    {
        return $this->hasMany(staff_hotel::class, 'hotel_id');
    }
}
