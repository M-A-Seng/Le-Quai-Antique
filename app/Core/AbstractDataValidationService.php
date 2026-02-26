<?php

namespace App\Core;

use App\Services\ConstantsCheckerService;
use InvalidArgumentException;

abstract class AbstractDataValidationService extends ConstantsCheckerService
{
    protected const NOT_NULL_COLUMNS=[];

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
            throw new InvalidArgumentException
            (
                ($checkAllRequiredKeys ?
                    "Clés obligatoires manquantes ou vides: " :
                    "Ces clés ne peuvent pas être vides ou null: ") . implode(', ', $invalidKeys)
            );
        }
    }
}