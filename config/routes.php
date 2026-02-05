<?php

# méthodeHttp, chemin, NomDuController@MéthodeÀAppeler

// return [
//     ['GET',  '/', 'HomeController@index'],
//     ['GET',  '/carte', 'MenuController@index'],
//     ['GET',  '/galerie', 'GalleryController@index'],
//     ['GET',  '/connexion', 'AuthController@login'],
//     ['POST', '/connexion', 'AuthController@authenticate'],
//     ['GET',  '/inscription', 'SignupController@login'],
//     ['POST', '/inscription', 'SignupController@authenticate'],
//     ['GET',  '/reserver', 'ReservationController@create'],
//     ['POST', '/reserver', 'ReservationController@store'],
// ];

return [
    # ------|------------------|--------------------------|-----------------------|
    ['HTTP', 'PATH',            'CONTROLLER NAME',          'CONTROLLER METHOD'],
    # ------|------------------|--------------------------|-----------------------|
    ['GET',  '/',               'HomeController',           'index'],
    ['GET',  '/carte',          'MenuController',           'index'],
    ['GET',  '/galerie',        'GalleryController',        'index'],
    ['GET',  '/connexion',      'AuthController',           'login'],
    ['POST', '/connexion',      'AuthController',           'authenticate'],
    ['GET',  '/inscription',    'SignupController',         'signup'],
    ['POST', '/inscription',    'SignupController',         'authenticate'],
    ['GET',  '/reserver',       'ReservationController',    'create'],
    ['POST', '/reserver',       'ReservationController',    'store'],
];