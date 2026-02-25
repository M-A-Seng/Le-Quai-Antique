<?php

namespace App\Core;

use InvalidArgumentException;
use Exception;
use App\Services\ConstantsCheckerService;

/**
 * AbstractCheckersModel implémente la vérification des tables et des colonnes autorisées.
 */
abstract class AbstractCheckersModel
{
    protected ConstantsCheckerService $constantsCheckerService;

    protected const ALLOWED_TABLES=[];
    protected const ALLOWED_COLUMNS=[];

    public function __construct(ConstantsCheckerService $constantsCheckerService)
    {
        $this->constantsCheckerService = $constantsCheckerService;
    }
    
    /**
     * getBaseConstants retourne un tableau associatif des constantes de la classe AbstractCheckersModel. Le tableau est structuré selon la syntaxe attendue par le service ConstantsCheckerService.
     * 
     * Structure attendue: `'NOM_DE_LA_CONSTANTE' => 'validateur'`  
     * 
     * Où "validateur" est une fonction (php ou créée), sans les parenthèses, qui retourne un booléen. Par exemple `['MY_CONSTANT' => 'is_string']`.
     *
     * @return array
     */
    protected function getBaseConstants(): array
    {
        return [
            'ALLOWED_TABLES' => 'is_array',
            'ALLOWED_COLUMNS' => 'is_array',
        ];
    }

    /**
     * filterAllowedTables vérifie que les données envoyées figurent dans la whitelist des tables.
     *
     * Assurez-vous que la constante ALLOWED_TABLES est correctement définie dans la classe où filterAllowedTables est appelée.
     *
     * @param  array|string $table
     * @return array|string
     */
    protected function filterAllowedTables(array|string $table): array|string
    {
        $tables = is_string($table) ? [$table] : $table;

        $tables = array_map('strtolower', $tables);
        $allowedTables = array_map('strtolower', static::ALLOWED_TABLES);

        $unknownTables = array_diff($tables, $allowedTables);

        if (!empty($unknownTables)) {
            throw new InvalidArgumentException(
                'Tables inconnues ou invalides: ' . implode(', ', $unknownTables)
            );
        }

        return $table;
    }

    /**
     * filterAllowedColumns vérifie que les données envoyées figurent dans la whitelist des colonnes.
     * 
     * Assurez-vous que la constante ALLOWED_COLUMNS est correctement définie dans la classe où filterAllowedColumns est appelée.
     *
     * @param  array|string $data
     * @return array|string
     */
    protected function filterAllowedColumns(array|string $data): array|string
    {
        $columns = is_string($data) ? [$data] : (array_is_list($data) ? $data : array_keys($data));

        $columns = array_map('strtolower', $columns);
        $allowedColumns = array_map('strtolower', static::ALLOWED_COLUMNS);

        $unknownColumns = array_diff($columns, $allowedColumns);

        if (!empty($unknownColumns)) {
            throw new InvalidArgumentException(
                'Colonnes inconnues ou invalides: ' . implode(', ', $unknownColumns)
            );
        }

        return $data;
    }
    
    /**
     * checkProtectedColumns vérifie que les données envoyées ne touchent pas aux colonnes spécifiées. Par exemple pour les colonnes accessibles uniquement en "read only".
     *
     * Lève une exception si une colonne protégée est détectée, sinon RAS.
     * 
     * @param  array $data
     * @param  array $protectedColumns
     * @return void
     */
    protected function checkProtectedColumns(array $data, array $protectedColumns): void
    {
        $forbiddenColumns = array_intersect(array_keys($data), $protectedColumns);

        if (!empty($forbiddenColumns)) {
            throw new Exception("Accès refusé à ces colonnes : " . implode(", ", $forbiddenColumns));
        }
    }
    
}

