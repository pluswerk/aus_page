<?php

namespace AUS\AusPage\Database;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 Markus Hölzle <m.hoelzle@andersundsehr.com>, anders und sehr GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class DatabaseSchemaService
 *
 * @author Markus Hölzle <m.hoelzle@andersundsehr.com>
 * @package AUS\AusPage\Database
 */
class DatabaseSchemaService implements SingletonInterface
{

    /**
     * @var array
     */
    protected $processingFields = [];


    /**
     * @param string $table
     * @param string $fieldName
     * @return void
     */
    public function addProcessingField($table, $fieldName)
    {
        if (isset($this->processingFields[$table]) === false) {
            $this->processingFields[$table] = [];
        }
        $this->processingFields[$table][$fieldName] = $fieldName;
    }

    /**
     * Get schema SQL
     *
     * This method needs ext_localconf and TCA/Overrides loaded!
     *
     * @return string Cache framework SQL
     */
    public function getAusPageRequiredDatabaseSchema()
    {
        /** @var DatabaseSchemaGenerator $databaseSchemaGenerator */
        $databaseSchemaGenerator = GeneralUtility::makeInstance(DatabaseSchemaGenerator::class);
        return $databaseSchemaGenerator->getDatabaseSchemaForFields($this->processingFields);
    }

    /**
     * A slot method to inject the required caching framework database tables to the
     * tables definitions string
     *
     * @param array $sqlString
     * @param string $extensionKey
     * @return array
     */
    public function addAusPageRequiredDatabaseSchemaForInstallUtility(array $sqlString, $extensionKey)
    {
        $sqlString[] = $this->getAusPageRequiredDatabaseSchema();
        return [$sqlString, $extensionKey];
    }

    /**
     * A slot method to inject the required caching framework database tables to the
     * tables definitions string
     *
     * @param array $sqlString
     * @return array
     */
    public function addAusPageRequiredDatabaseSchemaForSqlExpectedSchemaService(array $sqlString)
    {
        $sqlString[] = $this->getAusPageRequiredDatabaseSchema();
        return [$sqlString];
    }
}
