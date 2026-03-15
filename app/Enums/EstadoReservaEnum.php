<?php

namespace App\Enums;

enum EstadoReservaEnum: string
{
    case EN_PROCESO  = 'EN_PROCESO';
    case CANCELADA   = 'CANCELADA';
    case FINALIZADA  = 'FINALIZADA';
} 