<?php

namespace App\Services;

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
 * - validateTimeFormat()
 * - validateDateYmdFormat()
 * - validateTimeInterval()
 * - formatTimeToHHMM()
 * - formatDateTimeToTimestamptz()
 * - formatTimestamptzToLocal()
 * - toSeconds()
 * - toMinutes()
 */
class DatetimeService
{
    private const REGEX = [
        'date' => '/^\d{4}-\d{2}-\d{2}$/',
        'time' => '/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/',
        'time-strict' => '/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/',
        'timestamptz' => '/^\d{4}-\d{2}-\d{2}[T\s]\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})?$/',
    ];
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
     * validateTimeFormat vérifie qu'un string contient une heure de format "hh:mm". À utiliser pour valider l'entrée utilisateur.
     * 
     * Exception ou bool si heure invalide.
     *
     * @param   string $time | 'H:i' accepté
     * @param   bool $strict | Obligatoirement 'H:i:s'
     * @param   bool $return | true pour ne pas lancer d'exception.
     * @return  ?bool
     */
    public function validateTimeFormat(string $time, bool $strict = false, bool $return = false): ?bool
    {
        $time = trim($time);
        if ($strict) {
            if (!preg_match(self::REGEX['time-strict'], $time)) {
                # Mode strict utilisé côté serveur
                if ($return) { 
                    return false; 
                }
                throw new DataProcessingException(__METHOD__ . ": '$time' est invalide, un format 'H:i:s' est attendu.");
            }
            return true;
        }
        else {
            if (!preg_match(self::REGEX['time'], $time)) {
                # Mode normal utilise côté client
                if ($return) { 
                    return false;
                }
                throw new InvalidFieldException("'$time' est invalide. Veuillez sélectionner une heure valide.");
            }
            return true;
        }
    }
    
    /**
     * validateDateYmdFormat vérifie que la date donnée correspond au format Y-m-d.
     * 
     * Exception si format de date invalide.
     *
     * @param  string $date | Y:m:d
     * @return void
     */
    public function validateDateYmdFormat(string $date): void
    {
        $date = trim($date);
        if (!preg_match(self::REGEX['date'], $date)) {
            throw new InvalidFieldException("'$date' est invalide, veuillez sélectionner une date valide.");
        }
    }

    /**
     * validateTimeInterval vérifie l'interval de temps entre 2 horraire.
     * 
     * Exception si interval invalide.
     *
     * @param  string $startTime format "HH:MM"
     * @param  string $endTime format "HH:MM"
     * @param  int $minutesInterval nombre en minutes
     * @return void
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
     * Exception si impossible à formater.
     * 
     * @param  string $time
     * @param bool $strict | true pour le format H:i:s
     * @return ?string
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
     * formatDateTimeToTimestamptz retourne la date et l'heure au format ISO 8601 en timezone UTC.
     *
     * @param  string $date | YYYY-MM-DD
     * @param  string $time | HH:MM(:SS)
     * @return string
     */
    public function formatDateTimeToTimestamptz(string $date, string $time): string
    {
        $date = trim($date);
        $time = trim($time);
        if (!preg_match(self::REGEX['date'], $date) || !preg_match(self::REGEX['time'], $time)) {
            throw new DataProcessingException(__METHOD__ . ": Veuillez entrer une date et une heure valide en paramètre.");
        }
        if (!preg_match(self::REGEX['time-strict'], $time)) {
            $time .= ':00';
        }

        $datetime = DateTime::createFromFormat('Y-m-d H:i:s', "$date $time", new DateTimeZone('Europe/Paris'));
        if (!$datetime) {
            throw new DataProcessingException(__METHOD__ . ": Date/Heure invalide en paramètre.");
        }
        $errors = DateTime::getLastErrors();
        if ($errors && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
            throw new DataProcessingException(__METHOD__ . ": '" . $datetime->format('Y-m-d H:i:sP' . "' est invalide.")
            );
        }

        $datetime = $datetime->setTimezone(new DateTimeZone('UTC'));
        return $datetime->format('Y-m-d H:i:sP');
    }
    
    /**
     * formatTimestamptzToLocal attend une timestamp(tz optionnel) en paramètre, et la retourne (tableau) convertie en timezone Europe/Paris, présentation française.
     * 
     * @param  string $timestamptz
     * @return array
     * 
     * __Tableau retourné__: 
     * - 'universal' => 'Y-m-d H:i:sP', 
     * - 'Y-m-d' => 'Y-m-d,
     * - 'H:i:s' => 'H:i:s,
     * - 'datetime' => 'd/m/Y H:i', 
     * - 'date' => 'd/m/Y', 
     * - 'time' => 'H:i', 
     * - 'french_format' => '[jour] [nn] [mois] [année]'
     * - 'full_french_format' => '[jour] [nn] [mois] [année] à [heure]'
     */
    public function formatTimestamptzToLocal(string $timestamptz): array
    {
        if (!preg_match(self::REGEX['timestamptz'], $timestamptz)) {
            throw new DataProcessingException(__METHOD__ . ": Une date format ISO 8601 / RFC 3339 est attendue en paramètre.");
        }
        $datetime = new DateTimeImmutable($timestamptz);
        $local = $datetime->setTimezone(new DateTimeZone('Europe/Paris'));
        $formated = $this->dayOfWeek[ucfirst($local->format('l'))] ." ". $local->format('j') ." ". $this->months[ucfirst($local->format('F'))] ." ". $local->format('Y');
        return [
            'universal' => $local->format('Y-m-d H:i:sP'),
            'Y-m-d' => $local->format('Y-m-d'),
            'H:i:s' => $local->format('H:i:s'),
            'datetime' => $local->format('d/m/Y H:i'),
            'date' => $local->format('d/m/Y'),
            'time' => $local->format('H:i'),
            'french_format' => $formated,
            'full_french_format' => $formated . " à " . $local->format('H\hi'),
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