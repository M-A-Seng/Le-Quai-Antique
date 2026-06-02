<?php

namespace App\Services;

use App\Enums\Regex;
use App\Exceptions\DataProcessingException;
use App\Exceptions\InvalidFieldException;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;

/**
 * DatetimeService traite les données de type date et heure.
 * 
 * - getDaysOfWeekTranslation()
 * - getMonthsTranslation()
 * - getLocalTimezone()
 * - validateTimeFormat()
 * - validateDateYmdFormat()
 * - validateTimeInterval()
 * - formatTimeToHHMM()
 * - formatDateTimeToDatetimeTzOrISO()
 * - formatDatetimeTzOrISOToLocal()
 * - toSeconds()
 * - toMinutes()
 */
class DatetimeService
{
    protected array $months = [
        'January' => 'Janvier',
        'February' => 'Février',
        'March' => 'Mars',
        'April' => 'Avril',
        'May' => 'Mai',
        'June' => 'Juin',
        'July' => 'Juillet',
        'August' => 'Août',
        'September' => 'Septembre',
        'October' => 'Octobre',
        'November' => 'Novembre',
        'December' => 'Décembre',
    ];
    protected array $dayOfWeek = [
        'Monday' => 'Lundi',
        'Tuesday' => 'Mardi',
        'Wednesday' => 'Mercredi',
        'Thursday' => 'Jeudi',
        'Friday' => 'Vendredi',
        'Saturday' => 'Samedi',
        'Sunday' => 'Dimanche'
    ];
    private DateTimeZone $localTz;
    private DateTimeZone $UTCtz;

    public function __construct()
    {
        $this->localTz = new DateTimeZone('Europe/Paris');
        $this->UTCtz = new DateTimeZone('UTC');
    }
    
    /**
     * getdayOfWeekTranslation retourne un tableau des jours de la semaine 'anglais' => 'Français'
     *
     * @return array
     */
    public function getDaysOfWeekTranslation(): array
    {
        return $this->dayOfWeek;
    }
    
    /**
     * getMonthsTranslation retourne un tableau des mois de l'années 'Anglais' => 'Français'
     *
     * @return array
     */
    public function getMonthsTranslation(): array
    {
        return $this->months;
    }
    
    /**
     * getLocalTimezone retourne la timezone française.
     *
     * @return DateTimeZone
     */
    public function getLocalTimezone(): DateTimeZone
    {
        return $this->localTz;
    }

        /**
     * validateTimeFormat vérifie qu'un string contient une heure de format "hh:mm". À utiliser pour valider l'entrée utilisateur.
     *
     * @param   string $time | 'H:i' accepté
     * @param   bool $strict | Obligatoirement 'H:i:s'
     * @param   bool $return | true pour ne pas lancer d'exception.
     * @return  bool si $return = true
     * @throws DataProcessingException si $strict = true && $return = false
     * @throws InvalidFieldException si $strict = false && $return = false
     */
    public function validateTimeFormat(string $time, bool $strict = false, bool $return = false): bool
    {
        $time = trim($time);
        $regex = $strict ? Regex::TimeStrict->value : Regex::Time->value;

        if (!preg_match($regex, $time)) {
            if ($return) {
                return false;
            }
            # strict = backend, normal = frontend
            $strict ?
                throw new DataProcessingException(__METHOD__ . ": '$time' est invalide, un format 'hh:mm:ss' est attendu.")
                : throw new InvalidFieldException("'$time' est invalide. Veuillez sélectionner une heure valide.");
        }
        return true;
    }
    
    /**
     * validateDateYmdFormat vérifie que la date donnée correspond au format Y-m-d.
     *
     * @param  string $date | Y:m:d
     * @return void
     * @throws InvalidFieldException
     */
    public function validateDateYmdFormat(string $date): void
    {
        $date = trim($date);
        if (!preg_match(Regex::Date->value, $date)) {
            throw new InvalidFieldException("'$date' est invalide, veuillez sélectionner une date valide.");
        }
    }

    /**
     * validateTimeInterval vérifie l'interval de temps entre 2 horraire.
     * 
     * @param  string $startTime format "HH:MM"
     * @param  string $endTime format "HH:MM"
     * @param  int $minutesInterval nombre en minutes
     * @return void
     * @throws InvalidFieldException
     */
    public function validateTimeInterval(string $startTime, string $endTime, int $minutesInterval): void
    {
        // Convertir les heures en minutes
        list($startHours, $startMinutes) = explode(":", $startTime);
        $startMinutesTotal = $startHours * 60 + $startMinutes;
        list($endHours, $endMinutes) = explode(":", $endTime);
        $endMinutesTotal = $endHours * 60 + $endMinutes;
        
        // Si l'heure de fin est plus petite que l'heure de début, ajouter 24h à l'heure de fin
        if ($endMinutesTotal < $startMinutesTotal) {
            $endMinutesTotal += 24 * 60; // Ajouter 24 heures en minutes
        }

        $difference = $endMinutesTotal - $startMinutesTotal;
        if ($difference !== $minutesInterval) {
            throw new InvalidFieldException("L'intervalle entre l'heure de début et l'heure de fin est invalide. L'intervalle attendu est de {$minutesInterval} minutes.");
        }
    }
    
    /**
     * formatTimeToHHMM transforme les données de type time ou datetime en string time de format hh:mm. À utiliser avant affichage d'une heure dans une view.
     * 
     * @param  string $time
     * @param bool $strict | true pour le format H:i:s
     * @return ?string
     * @throws DataProcessingException
     */
    public function formatTimeToHHMM(string $time, bool $strict = false): string
    {
        $time = trim($time);
        if ($time === null || $time === '') {
            throw new DataProcessingException(__METHOD__ . ": Une heure est attendue en argument.");
        }
        $formats = [
            'H:i:s',
            'H:i:s.u',
            'H:i',
            'Y-m-d H:i:s',
            'Y-m-d H:i:s.u',
            'Y-m-d H:i',
            'H\hi',
            'H\hi:s',
            'H\hi:s.u',
        ];
        foreach ($formats as $format) {
            $date = DateTime::createFromFormat($format, $time);
            $errors = DateTime::getLastErrors();
            if ($date !== false && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                return $strict ? $date->format('H:i:s') : $date->format('H:i');
            }
        }
        throw new DataProcessingException(__METHOD__ . ": Impossible de formater l'heure passée en argument: '$time' est invalide.");
    }

    /**
     * formatDateTimeToDatetimeTzOrISO retourne une date formatée ATOM/ISO 8601 ou Y-m-d H:i:sP.
     *
     * @param  string $date
     * @param  string $time
     * @param  bool $local | true = Europe/Paris | false = UTC
     * @param  bool $ISO | true = ATOM/ISO 8601 | false = Y-m-d H:i:sP
     * @return string
     * @throws DataProcessingException
     */
    public function formatDateTimeToDatetimeTzOrISO(string $date, string $time, bool $local = false, bool $ISO = true): string
    {
        $date = trim($date);
        $time = trim($time);
        if (!preg_match(Regex::Date->value, $date) || !preg_match(Regex::Time->value, $time)) {
            throw new DataProcessingException(__METHOD__ . ": Veuillez entrer une date et une heure valide en paramètre.");
        }
        $time = !preg_match(Regex::TimeStrict->value, $time) ? $time .= ':00' : $time;
        
        $datetime = DateTime::createFromFormat('Y-m-d H:i:s', "$date $time", $this->localTz);
        if (!$datetime) {
            throw new DataProcessingException(__METHOD__ . ": Date/Heure invalide en paramètre.");
        }
        $errors = DateTime::getLastErrors();
        if ($errors && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
            throw new DataProcessingException(__METHOD__ . ": '" . $datetime->format('Y-m-d H:i:sP' . "' est invalide."));
        }
        
        $datetime = $local ? $datetime : $datetime->setTimezone($this->UTCtz);
        return $ISO ? $datetime->format(DateTime::ATOM) : $datetime->format('Y-m-d H:i:sP');
    }
    
    /**
     * formatDatetimeTzOrISOToLocal formate une date complète en date prête à l'affichage UI (timezone française).
     *
     * @param  string $DatetimeTzOrISO | ATOM/ISO 8601 ou 'Y-m-d H:i:sP'
     * @return array
     * 
     * __Tableau retourné__: 
     * - 'universal' => Y-m-d H:i:sP,
     * - 'ISO' => ATOM/ISO 8601,
     * - 'Y-m-d' => Y-m-d,
     * - 'H:i:s' => H:i:s,
     * - 'datetime' => d/m/Y H:i, 
     * - 'date' => d/m/Y, 
     * - 'time' => H:i, 
     * - 'french_format' => [jour] [nn] [mois] [année]
     * - 'full_french_format' => [jour] [nn] [mois] [année] à [heure]
     * 
     * @throws DataProcessingException
     **/
    public function formatDatetimeTzOrISOToLocal(string $DatetimeTzOrISO, bool $timestamptzFromDb = false): array
    {
        if (!$timestamptzFromDb && !preg_match(Regex::IsoAtom->value, $DatetimeTzOrISO)) {
            if (!preg_match(Regex::DateTimeTz->value, $DatetimeTzOrISO)) {
                throw new DataProcessingException(__METHOD__ . ": Format ATOM/ISO 8601 ou 'Y-m-d H:i:sP' attendue en paramètre.");
            }
        }
        $datetime = new DateTimeImmutable($DatetimeTzOrISO)->setTimezone($this->localTz);
        $formated = $this->dayOfWeek[$datetime->format('l')] ." ". $datetime->format('j') ." ". $this->months[$datetime->format('F')] ." ". $datetime->format('Y');

        return [
            'universal' => $datetime->format('Y-m-d H:i:sP'),
            'ISO' => $datetime->format(DateTime::ATOM),
            'Y-m-d' => $datetime->format('Y-m-d'),
            'H:i:s' => $datetime->format('H:i:s'),
            'datetime' => $datetime->format('d/m/Y H:i'),
            'date' => $datetime->format('d/m/Y'),
            'time' => $datetime->format('H:i'),
            'french_format' => $formated,
            'full_french_format' => $formated . " à " . $datetime->format('H\hi'),
        ];
    }


    
    /**
     * toSeconds converti H:i(:s) en secondes.
     *
     * @param  string $time
     * @return int
     */
    public function toSeconds(string $time): int
    {
        $time = $this->formatTimeToHHMM($time) . ":00";
        [$h, $m, $s] = array_map('intval', explode(':', $time));
        return $h * 3600 + $m * 60 + $s;
    }
    
    /**
     * toMinutes converti H:i(:s) en minutes.
     *
     * @param  string $time
     * @return int
     */
    public function toMinutes(string $time): int
    {
        $time = $this->formatTimeToHHMM($time) . ":00";
        [$h, $m, $s] = array_map('intval', explode(':', $time));
        return ($h * 60) + $m;
    }
}