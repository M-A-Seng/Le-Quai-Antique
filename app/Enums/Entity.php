<?php

namespace App\Enums;

enum Entity: string
{
  case RestaurantService = 'restaurant_service';
  case Gallery = 'image_gallery';
  case Service = 'service';
  case Reservation = 'reservation';
  case Dish = 'dish';
  case Menu = 'set_menu';
  case Category = 'dish_category';
  case User = 'users';
}