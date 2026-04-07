<?php

namespace App\Core\Abstract;

use App\Exceptions\DataProcessingException;
use App\Exceptions\InvalidArrayForDbException;
use App\Exceptions\InvalidFieldException;
use App\Services\ConstantsCheckerService;
use DateTime;

/**
 * AbstractDataValidationService implémente la validation de données et étend ConstantsCheckerService.
 * 
 * - validateNotNullKeys()
 * - validateTimeFormat()
 * - validateTimeInterval()
 * - formatTimeToHHMM()
 * - validatePositiveInteger()
 * - trimStringValuesInArray()
 */
abstract class AbstractDataProcessingService extends ConstantsCheckerService
{
    protected const NOT_NULL_COLUMNS=[];
    protected const REGEX = [
        'time' => '/^(?:[01]\d|2[0-3]):[0-5]\d$/',
        'password' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/',
        'phone' => '/^(\+?[1-9]{1}[0-9\s\-]{6,15}|0[0-9\s\-]{6,15})$/'
    ];

    public function __construct()
    {
        $constantsToCheck = ['NOT_NULL_COLUMNS' => 'array'];
        $this->validateConstants($constantsToCheck);
    }

    /**
     * validateNotNullKeys vérifie que les clés obligatoires (représentant les colonnes not null dans la db) ne sont pas vides ou nulles.
     * 
     * Assurez-vous que la constante NOT_NULL_COLUMNS est correctement définie dans la classe où validateNotNullKeys est appelée.
     * 
     * 1er paramètre -> Nom de la classe courante où la méthode est appelée.  
     *
     * 2e paramètre -> tableau associatif.  
     * 
     * 3e paramètre -> `false` si vous voulez contrôler uniquement les clés présentes dans le 2e paramètre (UPDATE).
     * -> `true` si vous voulez strictement vérifier que toutes les colonnes not null sont remplies (INSERT).
     *
     * @param  string $className
     * @param  array $data
     * @param  bool $checkAllRequiredKeys
     * @return void
     */    
    protected function validateNotNullKeys(string $className, array $data, bool $checkAllRequiredKeys = false) : void 
    {
        $invalidKeys = [];

        foreach ($className::NOT_NULL_COLUMNS as $key) {
            if (!$checkAllRequiredKeys && (array_key_exists($key, $data) && ($data[$key] === NULL || $data[$key] === "")))
            {
                $invalidKeys[] = $key;
            }
            elseif ($checkAllRequiredKeys && (!array_key_exists($key, $data) || $data[$key] === NULL || $data[$key] === ""))
            {
                $invalidKeys[] = $key;
            }
        }
        if (!empty($invalidKeys)) {
            throw new InvalidArrayForDbException 
            (
                ($checkAllRequiredKeys ?
                    "Clés obligatoires manquantes ou vides: " :
                    "Ces clés ne peuvent pas être vides ou null: ") . implode(', ', $invalidKeys)
            );
        }
    }
    
    /**
     * validateTimeFormat vérifie qu'un string contient une heure de format "hh:mm". À utiliser pour valider l'entrée utilisateur.
     *
     * @param  string $time
     * @return void
     */
    protected function validateTimeFormat(string $stringTime): void
    {
        $stringTime = trim($stringTime);
        if (!preg_match(self::REGEX['time'], $stringTime)) {
            throw new InvalidFieldException("'$stringTime' est invalide, veuillez sélectionner une heure valide.");
        }
    }

    /**
     * validateTimeInterval vérifie l'interval de temps entre 2 horraire.
     *
     * @param  string $startTime format "HH:MM"
     * @param  string $endTime format "HH:MM"
     * @param  int $minutesInterval nombre en minutes
     * @return void
     */
    protected function validateTimeInterval(string $startTime, string $endTime, int $minutesInterval): void
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
     * @param  ?string $time
     * @return string
     */
    public function formatTimeToHHMM(string $time): string
    {
        $time = trim($time);
        if ($time === null || $time === '') {
            throw new DataProcessingException("Une heure est attendue en argument.");
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
                return $date->format('H:i');
            }
        }
        throw new DataProcessingException("Impossible de formater l'heure passée en argument: '$time' est invalide.");
    }
    
    /**
     * validateGuestsNumber vérifie qu'un string contient un nombre entier positif.
     *
     * @param  string $guests
     * @return void
     */
    protected function validatePositiveInteger(string $stringInteger): string
    {
        $stringInteger = trim($stringInteger);
        if (!ctype_digit($stringInteger) || $stringInteger === '0') {
            throw new InvalidFieldException("'$stringInteger' n'est pas valide. Un nombre entier positif est attendu.");
        }
        return $stringInteger;
    }
    
    /**
     * trimAllValuesInArray retire les espaces au début et à la fin de toutes les chaînes de caractères dans un tableau.
     *
     * @param  array $data
     * @return array
     */
    protected function trimStringValuesInArray(array $data): array
    {
        foreach ($data as $key => $value)
        {
            if (is_string($value)) {
                $data[$key] = trim($value);
            } else {
                $data[$key] = $value;
            }
        }
        return $data;
    }
}