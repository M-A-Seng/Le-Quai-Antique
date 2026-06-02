<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case CONFIRMED = 'CONFIRMED';
    case COMPLETED = 'COMPLETED';
    case CANCELED = 'CANCELED';
}