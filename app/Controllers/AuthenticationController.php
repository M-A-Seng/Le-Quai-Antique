<?php

namespace App\Controllers;

use App\Core\AbstractController;

class AuthController extends AbstractController
{
    public function login()
    {
        $this->render("login");
    }
}