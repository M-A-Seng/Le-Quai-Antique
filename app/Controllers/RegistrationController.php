<?php

namespace App\Controllers;

use App\Core\AbstractController;

class RegistrationController extends AbstractController
{
    public function signup()
    {
        $this->render("signup");
    }
}