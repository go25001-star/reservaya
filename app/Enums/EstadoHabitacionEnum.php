<?php

namespace App\Enums;

enum EstadoHabitacionEnum: string
{
    case DISPONIBLE = 'DISPONIBLE';
    case MANTENIMIENTO = 'MANTENIMIENTO';
    case REMODELACION = 'REMODELACION';
    case FUERA_DE_SERVICIO = 'FUERA_DE_SERVICIO';
}