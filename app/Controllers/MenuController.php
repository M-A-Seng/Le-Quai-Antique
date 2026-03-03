<?php 

namespace App\Controllers;

use App\Core\AbstractController;

class MenuController extends AbstractController
{
    public function index()
    {
        $this -> render("menu");
    }
}