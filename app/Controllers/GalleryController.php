<?php 

namespace App\Controllers;

use App\Core\Controller;

class GalleryController extends Controller
{
    public function index()
    {
        $this -> render("gallery");
    }
}