<?php

namespace App\Controllers;

use App\Core\Abstract\AbstractController;
use App\Core\Auth;
use App\Core\Logger;
use App\Core\Response;
use App\Exceptions\RequireLoginException;
use App\Services\RenderService;
use App\Services\ReservationService;

/**
 * UserController
 * 
 * - loginClient()
 * - loginAdmin()
 * - logout()
 */
class UserController extends AbstractController
{
    public function __construct(private Auth $auth, 
                                private ReservationService $reservationService, 
                                RenderService $renderService, 
                                Logger $logger)
    {
        parent::__construct($renderService, $logger);
    }
    
    /**
     * loginClient Si le client avait remplir le formulaire de réservation avant de se connecter, il est redirigé vers la confirmation. Sinon affiche directement son profil.
     *
     * @param  array $params
     * @return Response
     * @throws RequireLoginException
     */
    public function loginClient(array $params): Response
    {
        if ((int)$params['id'] !== (int)$_SESSION['id']) {
            throw new RequireLoginException(UIMessage:"Votre session a expiré, veuillez vous reconnecter.");
        }
        # Si l'utilisateur a rempli le formulaire de réservation avant de s'authentifier
        if (isset($_SESSION['reservation_pending_confirmation']) && $_SESSION['reservation_pending_confirmation'] && !empty($_SESSION['reservation_data'])) {
            return $this->redirect('/reserver/confirmation');
        }
        $forthcomingReservations = $this->reservationService->getUserReservations($params['id'], true);

        $content = $this->renderService->render("user.profile", ['reservations' => $forthcomingReservations] ,'user');
        return $this->html($content);
    }
    
    /**
     * loginAdmin Si l'administrateur avait remplir le formulaire de réservation avant de se connecter, il est redirigé vers la confirmation. Sinon affiche directement son planning des réservations.
     *
     * @param  array $params
     * @return Response
     * @throws RequireLoginException
     */
    public function loginAdmin(array $params): Response
    {
        if ((int)$params['id'] !== (int)$_SESSION['id']) {
            throw new RequireLoginException(UIMessage:"Votre session a expiré, veuillez vous reconnecter.");
        }
        # Si l'utilisateur a rempli le formulaire de réservation avant de s'authentifier
        if (isset($_SESSION['reservation_pending_confirmation']) && $_SESSION['reservation_pending_confirmation'] && !empty($_SESSION['reservation_data'])) {
            return $this->redirect('/reserver/confirmation');
        }
        return $this->redirect('/admin/'.$_SESSION['id'].'/reservations');
    }
    
    /**
     * logout
     *
     * @return Response
     */
    public function logout(): Response
    {
        $this->auth->logout();
        return $this->redirect('/');
    }
}