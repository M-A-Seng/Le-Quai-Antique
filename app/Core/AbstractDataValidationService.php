<?php

namespace App\Core;

use App\Exceptions\InvalidArrayForDbException;
use App\Services\ConstantsCheckerService;
use InvalidArgumentException;

/**
 * AbstractDataValidationService implémente la validation de données et étend ConstantsCheckerService.
 * 
 * - validateNotNullKeys()
 * - validateTimeFormat()
 * - validateTimeInterval()
 * - validatePositiveInteger()
 * - trimAllValuesInArray()
 * - sanitizeTextValuesInArray()
 */
abstract class AbstractDataValidationService extends ConstantsCheckerService
{
    protected const NOT_NULL_COLUMNS=[];
    protected const REGEX = [
        'time' => '/^(?:[01]\d|2[0-3]):[0-5]\d$/',
        'password' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/',
        'phone' => '/^(\+?[1-9]{1}[0-9\s\-]{6,15}|0[0-9\s\-]{6,15})$/'
    ];

    public function __construct()
    {
        $constantsToCheck = ['NOT_NULL_COLUMNS' => 'is_array'];
        $this->validateConstants(static::class, $constantsToCheck);
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
     * validateTimeFormat vérifie qu'un string contient une heure de format "hh:mm" 
     *
     * @param  string $time
     * @return void
     */
    protected function validateTimeFormat(string $stringTime): void
    {
        $stringTime = trim($stringTime);
        if (!preg_match(self::REGEX['time'], $stringTime)) {
            throw new InvalidArgumentException("Heure invalide.");
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
            throw new InvalidArgumentException("L'intervalle entre l'heure de début et l'heure de fin est invalide. L'intervalle attendu est de {$minutesInterval} minutes.");
        }
    }
    
    /**
     * validateGuestsNumber vérifie qu'un string contient un nombre entier positif.
     *
     * @param  string $guests
     * @return void
     */
    protected function validatePositiveInteger(string $stringInteger): void
    {
        $stringInteger = trim($stringInteger);
        if (!ctype_digit($stringInteger) || $stringInteger === '0') {
            throw new InvalidArgumentException("'$stringInteger' n'est pas valide. Un nombre entier positif est attendu.");
        }
    }
    
    /**
     * trimAllValuesInArray retire les espaces au début et à la fin de toutes les valeurs qui sont des chaînes.
     *
     * @param  array $data
     * @return array
     */
    protected function trimAllValuesInArray(array $data): array
    {
        foreach ($data as $key => $value){
            $data[$key] = trim($value);
        }
        return $data;
    }
    
    /**
     * sanitizeTextValueInArray applique strip_tags() et htmlspecialchars() à toutes les valeurs spécifiées.
     *
     * @param  array $data
     * @param  array $keysToSanitize
     * @return array
     */
    protected function sanitizeTextValuesInArray(array $data, array $keysToSanitize): array
    {
        foreach ($data as $key => $value){
            if (in_array($key, $keysToSanitize)) {
                $data[$key] = htmlspecialchars(strip_tags($data[$key], '<p><br>'), ENT_QUOTES, 'UTF-8');
            }
        }
        return $data;
    }
}