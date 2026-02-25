<?php 

namespace App\Services;

use InvalidArgumentException;
use Exception;

/**
 * ConstantsCheckerService vérifie la définition des constantes
 */
class ConstantsCheckerService
{
    /**
     * validateConstants vérifie que les constantes de classe sont définies, non null et non vides.
     * 
     * ---
     * 
     * Prend en paramètre le nom de la classe courante et un tableau associatif dont la structure doit être `'NOM_DE_LA_CONSTANTE' => 'validateur'`.
     * 
     * ATTENTION: Le "validateur" doit être une fonction (php ou créée), sans les parenthèses, qui retourne un booléen. Par exemple `['MY_CONSTANT' => 'is_string']`.
     * 
     * @param  array $constantsToCheck
     * @return void
     */
    public function validateConstants(string $className, array $constantsToCheck): void
    {        
        foreach ($constantsToCheck as $constName => $validator)
        {
            $constantId = $className . '::' . $constName;

            if (!defined($constantId)) {
                throw new Exception($constantId . " : Constante non définie");
            }

            $value = constant($constantId);

            if ($value === null) {
                throw new Exception($constantId . " : Constante ne peut pas être null");
            }
            if (empty($value)) {
                throw new Exception($constantId . " : Constante vide");
            }
            if (!$validator($value)) {
                $validatorName = is_string($validator) ? $validator : 'Closure';
                throw new InvalidArgumentException(
                    $constantId . " : Constante invalide. \n
                    Valeur : " . var_export($value, true) .
                    "\nEchec du test : $validatorName()."
                );
            }
        }
    }
}