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
    ['GET',  '/reserver',                   'ReservationController',        'index'],
    
    ## Routes réservées
    ['GET',  '/reserver/confirmation',      'ReservationController',        'validateReservation',  ['requireLogin']],
    ['POST', '/reserver',                   'ReservationController',        'reserve',              ['requireLogin', 'requirePost&Csrf']],
    ['GET',  '/profil/{id}',                'UserController',               'loginClient',          ['requireLogin', 'requireClient']],
    ['GET',  '/admin/{id}',                 'UserController',               'loginAdmin',           ['requireLogin', 'requireAdmin']],
    ['POST', '/deconnexion',                'UserController',               'logout',               ['requireLogin', 'requirePost&Csrf']],
    # Client
    ['GET',  '/profil/{id}/mes-reservations',                       'UserReservationController',    'index',                ['requireLogin', 'requireClient']],
    ['POST', '/profil/{id}/reservation/{reservation_id}/modifier',  'ReservationController',        'validateReservation',  ['requireLogin', 'requireClient', 'requirePost&Csrf']],
    ['POST', '/profil/{id}/reservation/{reservation_id}/update',    'UserReservationController',    'update',               ['requireLogin', 'requireClient', 'requirePost&Csrf']],
    ['POST', '/profil/{id}/reservation/{reservation_id}/annuler',   'UserReservationController',    'cancel',               ['requireLogin', 'requireClient', 'requirePost&Csrf']],
    # Admin
    ['GET',  '/admin/{id}/settings/services',                       'RestaurantServiceController',  'index',    ['requireLogin', 'requireAdmin']],
    ['POST', '/admin/{id}/settings/services',                       'RestaurantServiceController',  'update',   ['requireLogin', 'requireAdmin', 'requirePost&Csrf']],
    ['GET',  '/admin/{id}/reservations',                            'AdminReservationController',   'index',    ['requireLogin', 'requireAdmin']],
    ['GET',  '/admin/{id}/reservations/{date}',                     'AdminReservationController',   'index',    ['requireLogin', 'requireAdmin']],
    ['POST', '/admin/{id}/reservation/{reservation_id}/update',     'UserReservationController',    'update',   ['requireLogin', 'requireAdmin', 'requirePost&Csrf']],
    ['POST', '/admin/{id}/reservation/{reservation_id}/annuler',    'UserReservationController',    'cancel',   ['requireLogin', 'requireAdmin', 'requirePost&Csrf']],
    
    # routes utilisées par AJAX
    ['POST', '/check/email',                'RegistrationController',       'checkEmail',           ['requirePost&Csrf']],
    ['POST', '/get/restaurant-services',    'RestaurantServiceController',  'getTimeSlots',         ['requirePost&Csrf']],
    ['POST', '/check/availability',         'ReservationController',        'canReserve',           ['requirePost&Csrf']],
    ['POST', '/prepare/reservation',        'ReservationController',        'checkAndPreserveData', ['requirePost&Csrf']],
    ['POST', '/check/reservation',          'ReservationController',        'validateReservation',  ['requirePost&Csrf']],
    ['POST', '/get/reservation',            'UserReservationController',    'edit',                 ['requireLogin', 'requirePost&Csrf']],
    ['POST', '/get/capacity',               'AdminReservationController',   'getServiceCapacity',   ['requireLogin', 'requireAdmin', 'requirePost&Csrf']],
    
    # Redirection
    ['GET',  '/accueil',                    'RedirectController',           'home'],
    ['GET',  '/welcome',                    'RedirectController',           'home'],
    ['GET',  '/menu',                       'RedirectController',           'menu'],
    ['GET',  '/gallery',                    'RedirectController',           'gallery'],
    ['GET',  '/se-connecter',               'RedirectController',           'login'],
    ['GET',  '/login',                      'RedirectController',           'login'],
    ['GET',  '/s-inscrire',                 'RedirectController',           'signup'],
    ['GET',  '/signup',                     'RedirectController',           'signup'],
    ['GET',  '/réserver',                   'RedirectController',           'reserve'],
    ['GET',  '/book',                       'RedirectController',           'reserve'],
];