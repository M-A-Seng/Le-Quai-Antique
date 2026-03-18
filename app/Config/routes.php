<?php

/* MIDDLEWARS:
 * - requireLogin: Utilisateur connecté
 * - requireClient: userRole client
 * - requireAdmin: userRole administrateur
*/

return [
#   ['HTTP', 'PATH',                        'CONTROLLER NAME',              'CONTROLLER METHOD',    ['MIDDLEWARE']],

    # Routes publiques
    ['GET',  '/',                           'HomeController',               'index'],
    ['GET',  '/la-carte',                   'MenuController',               'index'],
    ['GET',  '/galerie',                    'GalleryController',            'index'],
    ['GET',  '/connexion',                  'AuthenticationController',     'index'],
    ['POST', '/connexion',                  'AuthenticationController',     'authenticate'],
    ['GET',  '/inscription',                'RegistrationController',       'index'],
    ['POST', '/inscription',                'RegistrationController',       'register'],
    ['POST', '/inscription/check-email',    'RegistrationController',       'checkEmail'],
    ['GET',  '/reserver',                   'ReservationController',        'index'],
    ['POST', '/reserver',                   'ReservationController',        'reserve'],

    # Routes réservées
    ['GET',  '/profil',                     'UserController',               'loginClient',          ['requireLogin', 'requireClient']],
    ['GET',  '/admin',                      'UserController',               'loginAdmin',           ['requireLogin', 'requireAdmin']],
    ['POST', '/deconnexion',                'UserController',               'logout',               ['requireLogin']],
    # Admin
    ['GET',  '/admin/settings/services',    'RestaurantController',         'index',                ['requireLogin', 'requireAdmin']],
    ['POST', '/admin/settings/services',    'RestaurantController',         'updateRestaurant',     ['requireLogin', 'requireAdmin']],

    # Redirection
    ['GET',  '/accueil',                    'RedirectController',           'home'],
    ['GET',  '/welcome',                    'RedirectController',           'home'],
    ['GET',  '/menu',                       'RedirectController',           'menu'],
    ['GET',  '/gallery',                    'RedirectController',           'gallery'],
    ['GET',  '/se-connecter',               'RedirectController',           'login'],
    ['GET',  '/login',                      'RedirectController',           'login'],
    ['GET',  '/s-inscrire',                 'RedirectController',           'signup'],
    ['GET',  '/signup',                     'RedirectController',           'signup'],
];