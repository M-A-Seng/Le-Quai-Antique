<?php

return [
#   ['HTTP', 'PATH',            'CONTROLLER NAME',              'CONTROLLER METHOD'],
    ['GET',  '/',               'HomeController',               'index'],
    ['GET',  '/la-carte',       'MenuController',               'index'],
    ['GET',  '/galerie',        'GalleryController',            'index'],
    ['GET',  '/connexion',      'AuthenticationController',     'login'],
    ['POST', '/connexion',      'AuthenticationController',     'authenticate'],
    ['GET',  '/inscription',    'RegistrationController',       'signup'],
    ['POST', '/inscription',    'RegistrationController',       'register'],
    ['GET',  '/reserver',       'ReservationController',        'book'],
    ['POST', '/reserver',       'ReservationController',        'reserve'],

    # Redirection
    ['GET',  '/accueil',        'RedirectController',           'home'],
    ['GET',  '/welcome',        'RedirectController',           'home'],
    ['GET',  '/menu',           'RedirectController',           'menu'],
    ['GET',  '/gallery',        'RedirectController',           'gallery'],
    ['GET',  '/se-connecter',   'RedirectController',           'login'],
    ['GET',  '/login',          'RedirectController',           'login'],
    ['GET',  '/s-inscrire',     'RedirectController',           'signup'],
    ['GET',  '/signup',         'RedirectController',           'signup'],
];