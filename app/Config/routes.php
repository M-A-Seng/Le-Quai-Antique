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
    ['POST', '/connexion',                  'AuthenticationController',     'authenticate',         ['requirePost&Csrf']],
    ['GET',  '/inscription',                'RegistrationController',       'index'],
    ['POST', '/inscription',                'RegistrationController',       'register',             ['requirePost&Csrf']],
    ['POST', '/inscription/check-email',    'RegistrationController',       'checkEmail',           ['requirePost&Csrf']],
    ['GET',  '/reserver',                   'ReservationController',        'index'],
    ['POST', '/reserver',                   'ReservationController',        'reserve',              ['requirePost&Csrf']],

    # Routes réservées
    ['GET',  '/profil',                     'UserController',               'loginClient',          ['requireLogin', 'requireClient']],
    ['GET',  '/admin',                      'UserController',               'loginAdmin',           ['requireLogin', 'requireAdmin']],
    ['POST', '/deconnexion',                'UserController',               'logout',               ['requireLogin', 'requirePost&Csrf']],
    # Admin
    ['GET',  '/admin/settings/services',    'RestaurantServiceController',  'index',                ['requireLogin', 'requireAdmin']],
    ['POST', '/admin/settings/services',    'RestaurantServiceController',  'updateRestaurantService',['requireLogin', 'requireAdmin', 'requirePost&Csrf']],

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