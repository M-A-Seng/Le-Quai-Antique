<?php

namespace App\Enums;

enum ServiceType: string
{
    case BREAKFAST = 'BREAKFAST';
    case BRUNCH = 'BRUNCH';
    case LUNCH = 'LUNCH';
    case SNACK = 'SNACK';
    case DINNER = 'DINNER';
}