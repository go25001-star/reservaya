<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hotel extends Model
{
    
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

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    
    public function staffHotels()
    {
        return $this->hasMany(StaffHotel::class);
    }

    public function Habitaciones()
    {
        return $this->hasMany(Habitacion::class);
    }
}
