<?php

namespace App\Core\Abstract;

use App\Enums\Regex;
use App\Enums\Role;
use App\Exceptions\DataProcessingException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\InvalidArrayForDbException;
use App\Exceptions\InvalidFieldException;
use App\Exceptions\RequireLoginException;
use App\Services\ConstantsCheckerService;

/**
 * AbstractService implémente la validation de données et étend ConstantsCheckerService.
 * 
 * - validateNotNullKeys()
 * - validatePositiveInteger()
 * - trimStringValuesInArray()
 * - checkExpectedKeys()
 * - phoneNumberCheckAndSanitize()
 * - priceCheckAndNormalize()
 * - checkUserLegitimacy()
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
                    __METHOD__ . ": Clés obligatoires manquantes ou vides: " :
                    __METHOD__ . ": Ces clés ne peuvent pas être vides ou null: ") . implode(', ', $invalidKeys)
            );
        }
    }
        
    /**
     * validateGuestsNumber vérifie qu'un string contient un nombre entier positif.
     * 
     * Exception ou bool si entier invalide.
     *
     * @param   string|int $number
     * @param   bool $return | true pour ne pas lancer d'exception
     * @return  bool|string|int
     */
    protected function validatePositiveInteger(string|int $number, bool $return = false): bool|string|int
    {
        $n = is_string($number) ? trim($number) : $number;
        if (filter_var($n, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]) === false || (string)(int)$n !== (string)$n) {
            if ($return) {
                return false;
            }
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
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = trim($value);
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
     * @param bool $strict | true pour vérifier les clés manquantes
     * @return void
     */
    protected function checkExpectedKeys(array $expectedKeys, array $data, bool $strict = false): void
    {
        if (!array_is_list($expectedKeys)) {
            throw new DataProcessingException(__METHOD__ . ": Liste attendue en premier paramètre.");
        }
        if (array_is_list($data)) {
            throw new DataProcessingException(__METHOD__ . ": Tableau associatif est attendu en deuxième paramètre.");
        }
        $keys = array_keys($data);
        $unknownKeys = array_diff($keys, $expectedKeys);
        $missingKeys = array_diff($expectedKeys, $keys);

        if (!empty($unknownKeys)) {
            throw new DataProcessingException(__METHOD__ . ": Clés invalides: " . implode(', ', $unknownKeys));
        }
        if ($strict && !empty($missingKeys)) {
            throw new DataProcessingException(__METHOD__ . ": Clés manquantes: " . implode(', ', $missingKeys));
        }
    }

    /**
     * phoneNumberCheck vérifie la syntaxe du numéro de téléphone.
     * 
     * Exception si vide ou invalide
     *
     * @param  string $phoneNumber
     * @return string|null
     */
    public function phoneNumberCheckAndSanitize(string $phoneNumber): string|null
    {
        $phoneNumber = trim($phoneNumber);
        $phoneNumber = empty($phoneNumber) ? NULL : $phoneNumber;
        if (!empty($phoneNumber)) {
            if (!preg_match(Regex::PhoneN->value, $phoneNumber) || trim($phoneNumber, '0') === '') {
                throw new InvalidFieldException("Numéro de téléphone invalide.");
            }
        }
        return $phoneNumber;
    }
    
    /**
     * priceCheckAndNormalize
     *
     * @param  mixed $price
     * @return string numérique à 2 décimales
     * 
     * @throws DataProcessingException
     */
    function priceCheckAndNormalize(mixed $price): string
    {
        $value = trim((string)$price);
        $value = str_replace(['€', ' '], '', $value);
        $value = str_replace(',', '.', $value);

        if (!is_numeric($value) || !preg_match('/^\d+(?:[.,]\d+)?$/', $value)) {
            throw new DataProcessingException(__METHOD__ . ": Prix invalide en argument: '$value'. ");
        }
        return number_format((float)$value, 2, '.', '');
    }
    
    /**
     * checkUserLegitimacy Vérifie que l'utilisateur de la session est valide id + role.
     *
     * @param  int $userId
     * @param Role[] $roles | liste de Role Enum
     * @return void
     */
    protected function checkUserLegitimacy(?int $userId = null, array $roles = [Role::CLIENT]): void
    {
        # valider user authentifié
        $currentUserId = $_SESSION['id'] 
                         ? $_SESSION['id'] 
                         : throw new RequireLoginException(UIMessage: "Votre session est expirée, veuillez vous connecter.");

        if ($userId !== null && $userId !== $currentUserId) {
            throw new ForbiddenException(UIMessage: "Accès refusé.");
        }
        # valider role user
        $currentUserRole = $_SESSION['role']
                           ? $_SESSION['role'] 
                           : null;

        foreach ($roles as $role) {
            if (!$role instanceof Role) {
                throw new DataProcessingException(__METHOD__ . ': Tous les rôles doivent être des instances de Role enum.');
            }
        }
        if (!$currentUserRole || !in_array($currentUserRole, $roles, true)) {
            throw new ForbiddenException(message:__METHOD__ . ": Accès non autorisé.", UIMessage: "Accès refusé. Vous ne disposez pas des autorisations nécessaires.");
        }
    }
}