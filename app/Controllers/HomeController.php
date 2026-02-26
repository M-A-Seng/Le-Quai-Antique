<?php 

namespace App\Controllers;

use App\Core\AbstractController;

class HomeController extends AbstractController
{
    public function index()
    {
        $this -> render("home");
    }
}