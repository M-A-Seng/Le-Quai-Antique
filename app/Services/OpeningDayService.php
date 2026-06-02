<?php

namespace App\Services;

use App\Core\Abstract\AbstractService;
use App\Enums\DayOfWeek;
use App\Models\OpeningDayModel;
use DateTimeImmutable;

/**
 * OpeningDayService
 * 
 * - isServiceOpenThatDay()
 */
class OpeningDayService extends AbstractService
{
    public function __construct(private OpeningDayModel $openingDayModel,
                                private DatetimeService $datetimeService)
    {}
    
    /**
     * isServiceOpenThatDay returne true/false si le service de restauration est ouvert pour la date donnée.
     *
     * @param  int $restaurantServiceId
     * @param  string $date | Y-m-d
     * @return bool
     */
    public function isServiceOpenThatDay(int $restaurantServiceId, string $date): bool
    {
        $this->validatePositiveInteger($restaurantServiceId);
        $this->datetimeService->validateDateYmdFormat($date);

        $dayEnum = DayOfWeek::tryFrom(
            strtoupper((new DateTimeImmutable($date))->format('l')) # Jour semaine en uppercase
        );
        if ($dayEnum === null) {
            return false;
        }
        $result = $this->openingDayModel->findOpeningDaysByRestaurantServiceId($restaurantServiceId, $dayEnum);
        return !empty($result);
    }
}