<?php

 namespace App\Enums;
 
 enum RolEnum:String{
     case USUARIO = 'USUARIO';
     case USUARIOADMIN = 'USUARIOADMIN';
     case PROPIETARIO = 'PROPIETARIO';
     case GERENTE = 'GERENTE';
     case RECEPCIONISTA = 'RECEPCIONISTA';
 }
