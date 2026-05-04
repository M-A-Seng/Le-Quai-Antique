<?php

namespace App\Controllers;

use App\Core\Abstract\AbstractController;
use App\Services\RenderService;
use App\Core\Logger;
use App\Core\Response;
use App\Exceptions\AbstractBackendException;
use App\Exceptions\AbstractFrontendException;
use App\Exceptions\DbFailureException;
use App\Exceptions\NotFoundException;
use App\Services\ReservationService;
use App\Services\UserService;

/**
 * ReservationController
 * 
 * - index()
 * - validateReservation()
 * - reserve()
 * - canReserve()
 */
class ReservationController extends AbstractController
{
    public function __construct(private ReservationService $reservationService, 
                                private UserService $userService,
                                RenderService $renderService, 
                                Logger $logger)
    {
        parent::__construct($renderService, $logger);
    }
    
    /**
     * index formulaire de réservation
     *
     * @return Response
     */
    public function index(): Response
    {
        $http = 200;
        $error_message = null;
        $cached = null;
        if (isset($_SESSION['reservation_data']) && !empty($_SESSION['reservation_data'])) {
            $cached = $_SESSION['reservation_data'];
        }
        $userParam = null;
        if (isset($_SESSION['id']) && isset($_SESSION['role'])) {
            try {
                $userParam = $this->userService->getUserParameters($_SESSION['id']);
            }
            catch (AbstractFrontendException | NotFoundException $e) {
                echo $e;
                $error_message = $e->getUIMessage();
            }
            catch (AbstractBackendException $e) {
                echo $e;
                $error_message = $e->getUIMessage();
                $http = $e->getHttpCode();
                if ($e instanceof DbFailureException) {
                    $this->logger->dbError($e->getMessage());
                } else {
                    $this->logger->error($e->getMessage());
                }
            }
        }
        $data = array_merge($userParam ?? [], $cached ?? []);
        $data['error_message'] = $error_message;
        $content = $this->renderService->render('reserve', $data);
        return $this->html($content, $http);
    }
        
    /**
     * validateReservation Vérifie les données et affiche le récapitulatif si ok.
     *
     * @return Response
     */
    public function validateReservation(): Response
    {
        $http = 200;
        unset($_POST['csrf_token']);
        $_SESSION['reservation_data'] = $_POST; // stocker temporairement les données.
        try {
            # Si les données n'ont jamais été vérifiée -> procéder
            if (!isset($_SESSION['reservation_pending_confirmation']) || $_SESSION['reservation_pending_confirmation'] = false || !isset($_SESSION['reservation_data']))
            {
                $verified = $this->reservationService->validateReservationData($_POST, false);
                $this->reservationService->isValidReservationDateTime(1, $verified['date'], $verified['time']); # Actuellement restaurant unique
            }
            # demande de connexion/inscription si utilisateur non identifié
            if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
                $_SESSION['reservation_pending_confirmation'] = true; 
                $_POST['requireLogin'] = true;
                $content = $this->renderService->render('reserve', $_POST);
                return $this->html($content);
            }
            $data = $_SESSION['reservation_data'] ?? $_POST;
            $local = $this->reservationService->getFrenchFormatedDate($data['reservation_date'], $data['reservation_time']);
            $data = !empty($_POST) ? $_POST : $_SESSION['reservation_data'];
            $data['recap'] = [
                'display' => true,
                'date' => $local['full_french_format'],
                'allergy_string' => $data['allergy'] ? implode(", ", $data['allergy']) : '',
            ];
            $content = $this->renderService->render('reserve', $data);
            return $this->html($content);
        }
        catch (AbstractFrontendException | NotFoundException $e) {
            $_POST['error_message'] = $e->getUIMessage();
        }
        catch (AbstractBackendException $e) {
            $_POST['error_message'] = $e->getUIMessage();
            $http = $e->getHttpCode();
            if ($e instanceof DbFailureException) {
                $this->logger->dbError($e->getMessage());
            } else {
                $this->logger->error($e->getMessage());
            }
        }
        $content = $this->renderService->render('reserve', $_POST);
        return $this->html($content, $http);
    }

    /**
     * reserve Confirme le formulaire de réservation en enregistre
     *
     * @return Response
     */
    public function reserve(): Response
    {
        $error_message = null;
        $http = 200;
        unset($_POST['csrf_token']);
        try {
            $_POST['client_id'] = $_SESSION['id'];
            $this->reservationService->addReservation(1, $_POST); # Actuellement restaurant unique
            
            unset($_SESSION['reservation_data'], $_SESSION['reservation_pending_confirmation']);

            if (strtolower($_SESSION['role']->value) === 'admin') {
                $_SESSION['confirmation_message'] = "Réservation enregistrée avec succès !";
                return $this->redirect('/admin/' . $_SESSION['id'] . '/reservations');
            }
            $_SESSION['confirmation_message'] = "Réservation confirmée ! Merci pour votre confiance. Vous pouvez consulter vos réservations directement depuis votre profil ou votre onglet « Mes Réservations ».";
            return $this->redirect('/profil/' . $_SESSION['id']);
        } 
        catch (AbstractFrontendException | NotFoundException $e) {
            echo $e;
            $error_message = $e->getUIMessage();
        }
        catch (AbstractBackendException $e) {
            echo $e;
            $error_message = $e->getUIMessage();
            $http = $e->getHttpCode();
            if ($e instanceof DbFailureException) {
                $this->logger->dbError($e->getMessage());
            } else {
                $this->logger->error($e->getMessage());
            }
        }
        $_POST['error_message'] = $error_message;
        $content = $this->renderService->render('reserve', $_POST);
        return $this->html($content, $http);
    }
    
    /**
     * canReserve AJAX retourne un booléen (ou null si erreur) sur la disponibilité des places.
     *
     * @return Response
     */
    public function canReserve(): Response
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!is_array($data)) {
            return $this->json([
                'error_message' => 'Requête invalide'
            ], 400);
        }
        $http = 200;
        $reservationDate = $data['reservation_date'] ?? '';
        $reservationTime = $data['reservation_time'] ?? '';
        $guestCount = (int)$data['guest_count'] ?? 0;
        try {
            $bool = $this->reservationService->hasCapacityForReservation($reservationDate, $reservationTime, $guestCount);
        }
        catch (AbstractFrontendException | NotFoundException $e) {
            $data['error_message'] = $e->getUIMessage();
        }
        catch (AbstractBackendException $e) {
            $data['error_message'] = $e->getUIMessage();
            $http = $e->getHttpCode();
            if ($e instanceof DbFailureException) {
                $this->logger->dbError($e->getMessage());
            } else {
                $this->logger->error($e->getMessage());
            }
        }
        $data['canReserve'] = $bool ?? null;
        return $this->json($data, $http);
    }
}