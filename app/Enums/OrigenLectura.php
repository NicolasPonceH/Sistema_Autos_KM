<?php

namespace App\Enums;

enum OrigenLectura: string
{
    case Manual = 'MANUAL';
    case Import = 'IMPORT';
    case Correccion = 'CORRECCION';
}
