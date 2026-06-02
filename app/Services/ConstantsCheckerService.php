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
    private array $types = [
        'bool' => 'is_bool',
        'int' => 'is_int',
        'integer' => 'is_int',
        'float' => 'is_float',
        'double' => 'is_float',
        'real' => 'is_float',
        'string' => 'is_string',
        'array' => 'is_array',
        'list' => 'array_is_list',
        'numeric' => 'is_numeric',
        'iterable' => 'is_iterable',
        'countable' => 'is_countable',
    ];  
    /**
     * validateConstants vérifie que les constantes de classe sont définies, correctement typés, non null et non vides.
     *
     * - 1er paramètre: Le tableau de structure ['NOM_DE_LA_CONSTANTE' => 'type']; les types acceptés sont: bool, int, integer, float, double, real, string, array, list, numeric, iterable et countable.
     * 
     * - 2nd paramètre: Nom de la classe où est appelée la méthode. Laissez vide si la classe est héritée. Mettez static::class si injection de dépendance ou instance d'objet.
     * 
     * @param  array $constantsToCheck
     * @param  string $className
     * @return void
     */
    public function validateConstants(array $constantsToCheck, string $className = '')
    {
        $className = empty($className) ? static::class : $className;
        if (empty($constantsToCheck)) {
            throw new DataProcessingException("Vous devez fournir au moins un tableau associatif en argument de validateConstants().");
        }

        foreach ($constantsToCheck as $constName => $constTypes)
        {
            # Vérification du nom de la constante
            if (!is_string($constName) || is_numeric($constName) || ($constName !== strtoupper($constName))) {
                throw new DataProcessingException("Le nom de la constante '$constName' est invalide. Une chaîne de caractères en majuscule est attendue.");
            }

            # Vérification de la définition de la constante
            $constantId = $className . '::' . $constName;
            if (!defined($constantId)) {
                throw new DataProcessingException("La constante '$constantId' n'est pas définie.");
            }

            # Vérification du type de la constante
            $constTypes = is_array($constTypes) ? $constTypes : [$constTypes];
            if (!array_is_list($constTypes)) {
                throw new DataProcessingException("Vous ne pouvez pas mettre de tableau associatif comme valeur de '$constName'. Vous devez indiquer le(s) type(s) de '$constName' au format d'une chaîne de caractères ou d'une liste.");
            }
            $constTypes = array_map(fn($value) => strtolower(trim($value)), $constTypes);

            foreach ($constTypes as $constType) {
                if (!is_string($constType) || is_numeric($constType) || !array_key_exists($constType, $this->types)) {
                    throw new DataProcessingException("'$constType' est une valeur invalide. Vous devez indiquer le type de '$constName'. Les types acceptés sont: bool, int, integer, float, double, real, string, array, list, numeric, iterable et countable. Le type null n'est pas accepté.");
                }

                $constValue = constant($constantId);
                # Pas empty() pour laisser passer les types booléens
                if ($constValue === '' || 
                    $constValue === ' ' || 
                    $constValue === '0' || 
                    $constValue === 0 || 
                    $constValue === [] || 
                    $constValue === [''] || 
                    $constValue === [' '] || 
                    $constValue === [0] || 
                    $constValue === [null] || 
                    $constValue === null) {
                    throw new DataProcessingException("La constante '$constantId' ne peut pas être vide.");
                }

                # Validation du type
                foreach ($this->types as $type => $validator) {
                    if ($constType === $type && !$validator($constValue)) 
                    {
                        throw new DataProcessingException(
                            "La constante '$constantId' est invalide.\n" .
                            "Valeur : " . var_export($constValue, true) . "\n" .
                            "Échec du test : $validator($constValue)."
                        );
                    }
                }
            }
        }
    }
}