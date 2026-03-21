<?php 

namespace App\Services;

use App\Exceptions\DataProcessingException;

/**
 * ConstantsCheckerService vérifie la définition des constantes.
 * 
 * - validateConstants()
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
                throw new DataProcessingException($constantId . " : Constante non définie");
            }

            $value = constant($constantId);

            if ($value === null) {
                throw new DataProcessingException($constantId . " : Constante ne peut pas être null");
            }
            if (empty($value)) {
                throw new DataProcessingException($constantId . " : Constante ne peut pas être vide");
            }
            if (!$validator($value)) {
                $validatorName = is_string($validator) ? $validator : 'Closure';
                throw new DataProcessingException(
                    $constantId . " : Constante invalide. \n
                    Valeur : " . var_export($value, true) .
                    "\nEchec du test : $validatorName()."
                );
            }
        }
    }
}