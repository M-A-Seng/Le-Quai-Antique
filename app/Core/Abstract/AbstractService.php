<?php

namespace App\Core\Abstract;

use App\Exceptions\DataProcessingException;
use App\Exceptions\InvalidArrayForDbException;
use App\Exceptions\InvalidFieldException;
use App\Services\ConstantsCheckerService;

/**
 * AbstractService implémente la validation de données et étend ConstantsCheckerService.
 * 
 * - validateNotNullKeys()
 * - validatePositiveInteger()
 * - trimStringValuesInArray()
 */
abstract class AbstractService extends ConstantsCheckerService
{
    protected const NOT_NULL_COLUMNS=[];

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
     * Exception si une clé obligatoire est nulle ou vide.
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
                    __METHOD__ . "Clés obligatoires manquantes ou vides: " :
                    __METHOD__ . "Ces clés ne peuvent pas être vides ou null: ") . implode(', ', $invalidKeys)
            );
        }
    }
        
    /**
     * validateGuestsNumber vérifie qu'un string contient un nombre entier positif.
     * 
     * Exception si entier invalide.
     *
     * @param  string|int $number
     * @return string|int
     */
    protected function validatePositiveInteger(string|int $number): string|int
    {
        $n = is_string($number) ? trim($number) : $number;
        if (filter_var($n, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]) === false || (string)(int)$n !== (string)$n) {
            throw new InvalidFieldException(__METHOD__ . ": '$number' est invalide. Un nombre entier positif est attendu.");
        }
        return $n;
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
    
    /**
     * checkExpectedKeys vérifie les clés des données (parm2) sont strictement présente dans la liste autorisées (param1).
     * 
     * Utile pour valider le inputs des formulaires.
     *
     * @param  array $expectedKeys | liste
     * @param  array $data | tableau associatif
     * @return void
     */
    protected function checkExpectedKeys(array $expectedKeys, array $data): void
    {
        if (!array_is_list($expectedKeys)) {
            throw new DataProcessingException(__METHOD__ . "Une liste est attendue en premier paramètre de checkExpectedInputs().");
        } else {
            sort($expectedKeys);
        }

        if (array_is_list($data)) {
            throw new DataProcessingException(__METHOD__ . "Un tableau associatif est attendu en deuxième paramètre de checkExpectedInputs().");
        } else {
            $inputs = array_keys($data);
            sort($inputs);
        }

        $unlknownInputs = array_diff($inputs, $expectedKeys);
        if (!empty($unlknownInputs)) {
            $debug = print_r($unlknownInputs, true);
            throw new DataProcessingException(__METHOD__ . ": Clés invalides: " . $debug);
        }
    }
}