<?php

namespace App\Controllers;

use App\Core\Abstract\AbstractController;
use App\Core\Logger;
use App\Core\Response;
use App\Enums\ReservationStatus;
use App\Exceptions\AbstractBackendException;
use App\Exceptions\AbstractFrontendException;
use App\Exceptions\DbFailureException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\InvalidFieldException;
use App\Exceptions\InvalidReservationException;
use App\Exceptions\NotFoundException;
use App\Services\RenderService;
use App\Services\ReservationService;

/**
 * UserReservationController
 * 
 * - index()
 * - edit()
 * - update()
 * - cancel()
 */
class UserReservationController extends AbstractController
{
    private string $baseUrl;
    private string $pageUrl;

    public function __construct(private ReservationService $reservationService,
                                RenderService $renderService, 
                                Logger $logger)
    {
        parent::__construct($renderService, $logger);
        $this->baseUrl = ($_SESSION['role']->value === 'ADMIN' ? '/admin/' : '/profil/') . $_SESSION['id'];
        $this->pageUrl = $this->baseUrl . ($_SESSION['role']->value === 'ADMIN' ? '/reservations' : '/mes-reservations');
    }
    
    /**
     * index page "mes réservations"
     *
     * @param  array $param
     * @return Response
     * 
     * @throws ForbiddenException
     */
    public function index(array $param = [], array $extraData = []): Response
    {
        $error_message = null;
        $http = 200;
        $data = [];
        if ($param['id'] && (int)$param['id'] !== (int)$_SESSION['id']) {
            throw new ForbiddenException(__METHOD__ . ": Utilisateur non reconnu.");
        }
        try {
            $userReservations = $this->reservationService->getUserReservations($param['id']);
            $data['reservations'] = $userReservations ?? null;
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
        $page = [
            'page' => 'mes-reservations',
            'error_message' => $error_message
        ];
        $data = array_merge($page, $data, $extraData);
        $content = $this->renderService->render('user.reservations', $data, 'user');
        return $this->html($content, $http);
    }
    
    /**
     * edit Prérempli le formulaire de modification de la réservation avec AJAX.
     *
     * @return Response
     * 
     * @throws InvalidReservationException
     */
    public function edit(): Response
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!is_array($data)) {
            return $this->json([
                'error_message' => 'Requête invalide'
            ], 400);
        }
        unset($data['csrf_token']);
        $http = 200;
        try {
            $reservationId = $data['id'] ?? throw new InvalidReservationException();
            $reservation = $this->reservationService->getReservationById($reservationId, true);
            if (empty($reservation)) {
                throw new InvalidReservationException("Impossible de charger les données, veuillez réessayer.");
            }
            $reservation['url'] = $this->baseUrl . "/reservation/" . $reservation['id'] . "/modifier";
            return $this->json($reservation);
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
        return $this->json(['success' => false]);
    }
    
    /**
     * update met à jour une réservation
     *
     * @param  array $param
     * @return Response
     * 
     * @throws ForbiddenException
     * @throws InvalidReservationException
     */
    public function update(array $param = []): Response
    {
        unset($_POST['csrf_token']);
        $error_message = null;
        if ((int)$param['id'] !== (int)$_SESSION['id']) {
            throw new ForbiddenException(__METHOD__ . ": Utilisateur non reconnu.");
        }
        $url = $this->pageUrl;
        try {
            if (!$_POST['reservation_date']) {
                throw new InvalidFieldException("Veuillez fournir une date de réservation.");
            }
            [$year, $month, $day] = explode('-', $_POST['reservation_date']);
            $url = $this->pageUrl . ($_SESSION['role']->value === 'ADMIN' ? "/{$day}-{$month}-{$year}" : '');

            $reservation = $this->reservationService->getReservationById($_POST['id']);
            if (empty($reservation)) {
                throw new InvalidReservationException("Impossible de mettre à jour votre réservation, veuillez réessayer.");
            }
            $_POST['user_id'] = $param['id'];
            $this->reservationService->modifyReservation($reservation['id'], $_POST);

            $_SESSION['confirmation_message'] = "✔️ Réservation modifiée avec succès !";
            return $this->redirect($url);
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
        $_SESSION['error_message'] = $error_message;
        return $this->redirect($url);
    }
    
    /**
     * cancel annule une réservation
     *
     * @param  array $param
     * @return Response
     * 
     * @throws ForbiddenException
     * @throws InvalidReservationException
     */
    public function cancel(array $param = []): Response
    {
        unset($_POST['csrf_token']);
        $error_message = null;
        if ((int)$param['id'] !== (int)$_SESSION['id']) {
            throw new ForbiddenException(__METHOD__ . ": Utilisateur non reconnu.");
        }
        try {
            $reservation = $this->reservationService->getReservationById($_POST['id']);
            if (empty($reservation)) {
                throw new InvalidReservationException("Une erreur est survenue, veuillez réessayer.");
            }
            $_POST['user_id'] = $param['id'];
            $this->reservationService->changeReservationStatus($param['id'], $reservation['id'], ReservationStatus::CANCELED);

            $message = $_SESSION['role']->value === 'ADMIN' ? 
                       "Réservation du ".$_POST['reservation_datetime']." annulée avec succès! ✔️ "
                       : "✔️ Annulation réussie : votre table pour le ".$_POST['reservation_datetime']." n'est plus réservée. À très vite au restaurant!";
            $_SESSION['confirmation_message'] = $message;
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
        $_SESSION['error_message'] = $error_message;
        return $this->redirect($this->pageUrl);
    }
}