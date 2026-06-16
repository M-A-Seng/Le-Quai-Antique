<?php

namespace App\Controllers;

use App\Core\Abstract\AbstractController;
use App\Services\RenderService;
use App\Core\Logger;
use App\Core\Response;
use App\Exceptions\AbstractBackendException;
use App\Exceptions\AbstractFrontendException;
use App\Exceptions\DbFailureException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Services\ReservationService;
use App\Services\ServiceService;
use DateTimeImmutable;
use DateTimeZone;

/**
 * AdminReservationController
 * 
 * - index()
 * - getServiceCapacity()
 */
class AdminReservationController extends AbstractController
{
    private DateTimeZone $timezone;

    public function __construct(private ReservationService $reservationService,
                                private ServiceService $serviceService,
                                RenderService $renderService, 
                                Logger $logger)
    {
        parent::__construct($renderService, $logger);
        $this->timezone = new DateTimeZone('Europe/Paris');
    }
    
    /**
     * index page des réservations (administrateur)
     *
     * @param  array $param
     * @return Response
     * 
     * @throws ForbiddenException
     */
    public function index(array $param = []): Response
    {
        if ((int)$param['id'] !== (int)$_SESSION['id']) {
            throw new ForbiddenException(__METHOD__ . ": Utilisateur non reconnu.");
        }
        $data = null;
        $http = 200;
        $date = isset($param['date']) ? 
                DateTimeImmutable::createFromFormat('d-m-Y', $param['date'], $this->timezone)
                : new DateTimeImmutable(timezone:$this->timezone);
        try {
            $data = $this->reservationService->getReservationsByDate(1, $date->format('Y-m-d')) ?? ["french_formated_date" => $date->format('d/m/Y'), "day_before" => $date->modify('-1 day')->format('d-m-Y'), "day_after" => $date->modify('+1 day')->format('d-m-Y')];
            $data['today'] = isset($param['date']) ? false : true;
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
        $data['page'] = 'reservations';
        $content = $this->renderService->render('admin.reservations', $data, 'user');
        return $this->html($content, $http);
    }
    
    /**
     * getServiceCapacity retourne la somme restante de la capacité disponible à AJAX
     *
     * @return Response
     */
    public function getServiceCapacity(): Response
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!is_array($data)) {
            return $this->json(['error_message' => 'Requête invalide'], 400);
        }
        if (!isset($data['serviceId'])) {
            return $this->json(['error_message' => 'Une erreur interne est survenue, veuillez réessayer.'], 400);
        }
        try {
            $remaining = $this->serviceService->getRemainingPlacesInService($data['serviceId']);
            return $this->json(['remaining_places' => $remaining]);
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
}