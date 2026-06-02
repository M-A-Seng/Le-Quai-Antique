<?php

namespace App\Enums;

enum ServiceType: string
{
    case Breakfast = 'BREAKFAST';
    case Brunch = 'BRUNCH';
    case Lunch = 'LUNCH';
    case Snack = 'SNACK';
    case Dinner = 'DINNER';
}