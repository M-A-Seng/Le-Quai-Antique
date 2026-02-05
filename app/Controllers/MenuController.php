<?php 

namespace App\Controllers;

use App\Core\Controller;

class MenuController extends Controller
{
    public function index()
    {
        $this -> render("menu");
    }
}