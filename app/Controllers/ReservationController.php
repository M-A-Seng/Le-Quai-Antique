<?php

namespace App\Controllers;

use App\Core\Abstract\AbstractController;
use App\Services\RenderService;
use App\Core\Logger;
use App\Core\Response;
use App\Exceptions\AbstractBackendException;
use App\Exceptions\AbstractFrontendException;
use App\Exceptions\DataProcessingException;
use App\Exceptions\DbFailureException;
use App\Exceptions\NotFoundException;
use App\Services\ReservationService;
use App\Services\UserService;

/**
 * ReservationController
 * 
 * - index()
 * - validateReservation()
 * - checkAndPreserveData()
 * - reserve()
 * - canReserve()
 */
class ReservationController extends AbstractController
{
    private string $baseUrl;
    private string $pageUrl;

    public function __construct(private ReservationService $reservationService, 
                                private UserService $userService,
                                RenderService $renderService, 
                                Logger $logger)
    {
        parent::__construct($renderService, $logger);
        if (isset($_SESSION['role'])) {
            $this->baseUrl = ($_SESSION['role']->value === 'ADMIN' ? '/admin/' : '/profil/') . $_SESSION['id'];
            $this->pageUrl = $this->baseUrl . ($_SESSION['role']->value === 'ADMIN' ? '/reservations' : '/mes-reservations');
        }
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
                if ($_SESSION['role']->value !== 'ADMIN') {
                    $userParam = $this->userService->getUserParameters($_SESSION['id']);
                }
            }
            catch (AbstractFrontendException | NotFoundException $e) {
                $error_message = $e->getUIMessage();
            }
            catch (AbstractBackendException $e) {
                $error_message = $e->getUIMessage();
                $http = $e->getHttpCode();
                if ($e instanceof DbFailureException) {
                    $this->logger->dbError($e->getMessage());
                } else {
                    $this->logger->error($e->getMessage());
                }
            }
        }
        $page = [
            'page' => 'reserver',
            'error_message' => $error_message
        ];
        $data = array_merge($page, $userParam ?? [], $cached ?? []);
        $content = $this->renderService->render('reserve', $data);
        return $this->html($content, $http);
    }
        
    /**
     * validateReservation Vérifie les entrées et affiche le récapitulatif si ok.
     *
     * @return Response
     */
    public function validateReservation(): Response
    {
        unset($_POST['csrf_token']);
        $http = 200;
        try {
            # Si les données n'ont jamais été vérifiées/stockées -> procéder
            if (!isset($_SESSION['reservation_pending_confirmation']) || $_SESSION['reservation_pending_confirmation'] = false || !isset($_SESSION['reservation_data']))
            {
                unset($_POST['beenModified']);
                $verified = $this->reservationService->validateReservationData($_POST, false);
                $this->reservationService->isValidReservationDateTime(1, $verified['date'], $verified['time']); # Actuellement restaurant unique
                $_SESSION['reservation_data'] = $_POST;
            }
            $_SESSION['reservation_data'] = isset($_POST['beenModified']) && $_POST['beenModified'] ? $_POST : $_SESSION['reservation_data'] ?? ''; # Si l'utilisateur a re-modiffié sa réservation, réattribuer $_post
            unset($_SESSION['reservation_data']['beenModified']);
            $data = $_SESSION['reservation_data'] ?? $_POST;
            $local = $this->reservationService->getFrenchFormatedDate($data['reservation_date'], $data['reservation_time']);
            $allergy = isset($data['allergy']) ?
                       (is_array($data['allergy']) ? implode(", ", $data['allergy']) : $data['allergy'])
                       : '';
            $formaction = $data['action'] === 'update' ? 
                          $this->baseUrl . '/reservation/'.$data['id'].'/update'
                          : '/reserver';
            $data['recap'] = [
                'display' => true,
                'date' => $local['full_french_format'],
                'name' => $data['client_name'],
                'guest' => $data['guest_count'],
                'tel' => $data['client_tel'] ?? '',
                'allergy' => $allergy,
                'formaction' => $formaction
            ]; 
            // après redirection (user non identifié)
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $content = $this->renderService->render('reserve', $data, 'user');
                return $this->html($content);
            }
            // ajax (user authentifié)
            return $this->json($data);
        }
        catch (AbstractFrontendException | NotFoundException $e) {
            $_POST['error_message'] = $e;//->getUIMessage();
        }
        catch (AbstractBackendException $e) {
            $_POST['error_message'] = $e;//->getUIMessage();
            $http = $e->getHttpCode();
            if ($e instanceof DbFailureException) {
                $this->logger->dbError($e->getMessage());
            } else {
                $this->logger->error($e->getMessage());
            }
        }
        if ($_POST['action'] && $_POST['action'] === 'update') {
            $content = $this->renderService->render('user.reservations', $_POST, 'user');
            return $this->html($content, $http);
        }
        $content = $this->renderService->render('reserve', $_POST);
        return $this->html($content, $http);
    }
    
    /** 
     * checkAndPreserveData méthode appelée lorsque l'utilisateur essai de réserver sans être authentifiée.
     *
     * @return Response
     */
    public function checkAndPreserveData(): Response
    {
        $error_message = null;
        unset($_POST['csrf_token']);
        $http = 200;
        try {
            $verified = $this->reservationService->validateReservationData($_POST, false);
            $this->reservationService->isValidReservationDateTime(1, $verified['date'], $verified['time']); # Actuellement restaurant unique
            $_SESSION['reservation_data'] = $_POST;
            $_SESSION['reservation_pending_confirmation'] = true;
            return $this->json(['success' => true]);
        } 
        catch (AbstractFrontendException | NotFoundException $e) {
            $error_message = $e->getUIMessage();
        }
        catch (AbstractBackendException $e) {
            $error_message = $e->getUIMessage();
            $http = $e->getHttpCode();
            if ($e instanceof DbFailureException) {
                $this->logger->dbError($e->getMessage());
            } else {
                $this->logger->error($e->getMessage());
            }
        }
        return $this->json(['error_message' => $error_message]);
    }

    /**
     * reserve Confirme le formulaire de réservation en enregistre
     *
     * @return Response
     */
    public function reserve(): Response
    {
        if ($_POST['action'] && $_POST['action'] === 'reserve') {
            unset($_POST['action'], $_POST['csrf_token']);
        } else {
            throw new DataProcessingException(__METHOD__ . ": Champ 'action' invalide ou indéfinit.");
        }
        $error_message = null;
        $http = 200;
        try {
            $_POST['client_id'] = $_SESSION['id'];
            $this->reservationService->addReservation(1, $_POST); # Actuellement restaurant unique
            unset($_SESSION['reservation_data'], $_SESSION['reservation_pending_confirmation']);

            $_SESSION['confirmation_message'] = $_SESSION['role']->value === 'ADMIN' ? 
                                                "Réservation enregistrée avec succès !" 
                                                : "Réservation confirmée ! Merci pour votre confiance. Vous pouvez consulter vos réservations directement depuis votre profil ou votre onglet « Mes Réservations ».";
            return $this->redirect($this->pageUrl);
        } 
        catch (AbstractFrontendException | NotFoundException $e) {
            $error_message = $e->getUIMessage();
        }
        catch (AbstractBackendException $e) {
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