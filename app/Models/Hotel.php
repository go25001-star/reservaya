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

    public function staffHotels ():HasMany
    {
        return $this->hasMany(StaffHotel::class);
    }
}
