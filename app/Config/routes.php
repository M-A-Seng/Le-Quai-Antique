<?php

return [
#   ['HTTP', 'PATH',            'CONTROLLER NAME',          'CONTROLLER METHOD'],
    ['GET',  '/',               'HomeController',           'index'],
    ['GET',  '/accueil',        'RedirectController',       'home'],
    ['GET',  '/la-carte',       'MenuController',           'index'],
    ['GET',  '/menu',           'RedirectController',       'menu'],
    ['GET',  '/galerie',        'GalleryController',        'index'],
    ['GET',  '/connexion',      'AuthController',           'login'],
    ['POST', '/connexion',      'AuthController',           'authenticate'],
    ['GET',  '/inscription',    'SignupController',         'signup'],
    ['POST', '/inscription',    'SignupController',         'authenticate'],
    ['GET',  '/reserver',       'ReservationController',    'create'],
    ['POST', '/reserver',       'ReservationController',    'store'],
];