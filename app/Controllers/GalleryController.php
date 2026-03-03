<?php 

namespace App\Controllers;

use App\Core\AbstractController;

class GalleryController extends AbstractController
{
    public function index()
    {
        $this -> render("gallery");
    }
}