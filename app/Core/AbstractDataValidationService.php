<?php

namespace App\Core;

use InvalidArgumentException;

abstract class AbstractDataValidationService
{
    protected const NOT_NULL_COLUMNS=[];

    /**
     * validateNotNullKeys vérifie que les clés obligatoires (représentant les colonnes not null dans la db) ne sont pas vides ou nulles.
     * 
     * Assurez-vous que la constante NOT_NULL_COLUMNS est correctement définie dans classe où validateNotNullKeys est appelée.
     * 
     * Premier paramètre -> tableau associatif.  
     * 
     * Deuxième paramètre -> `false` si vous voulez contrôler uniquement les clés présentes dans le premier paramètre (UPDATE).
     * -> `true` si vous voulez strictement vérifier que toutes les colonnes not null sont remplies (INSERT).
     *
     * @param  array $data
     * @param  bool $checkAllRequiredKeys
     * @return void
     */
    protected function validateNotNullKeys(array $data, bool $checkAllRequiredKeys = false) : void 
    {
        $invalidKeys = [];

        foreach (static::NOT_NULL_COLUMNS as $key) {
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