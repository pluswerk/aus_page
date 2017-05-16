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
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class DatabaseSchemaGenerator
 *
 * @author Markus Hölzle <m.hoelzle@andersundsehr.com>
 * @package AUS\AusPage\Database
 */
class DatabaseSchemaGenerator implements SingletonInterface
{

    /**
     * @var array
     */
    protected $integerFields = [
        'date',
        'datetime',
        'int',
        'num',
        'time',
        'timesec',
        'year',
    ];

    /**
     * @var array
     */
    protected $floatFields = [
        'double2',
    ];

    /**
     * @param array $tables
     * @return string
     */
    public function getDatabaseSchemaForFields(array $tables)
    {
        foreach ($tables as $table => &$fields) {
            foreach ($fields as &$field) {
                $field = $this->getDatabaseSchemaForField($table, $field);
            }
        }

        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var StandaloneView $view */
        $view = $objectManager->get(StandaloneView::class);
        $view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName('EXT:aus_page/Resources/Private/SqlTemplates/DatabaseSchema.html'));
        $view->assign('tables', $tables);
        return trim($view->render());
    }

    /**
     * @param string $table
     * @param string $field
     * @return string
     */
    protected function getDatabaseSchemaForField($table, $field)
    {
        $databaseSchema = '';
        $configType = $GLOBALS['TCA'][$table]['columns'][$field]['config']['type'];
        switch ($configType) {
            case 'input':
                $evalParts = GeneralUtility::trimExplode(',', $GLOBALS['TCA'][$table]['columns'][$field]['config']['eval'], true);
                $isInteger = false;
                $isFloat = false;
                foreach ($evalParts as $evalPart) {
                    if (in_array($evalPart, $this->integerFields)) {
                        $isInteger = true;
                        break;
                    } else if (in_array($evalPart, $this->floatFields)) {
                        $isFloat = true;
                        break;
                    }
                }
                if ($isInteger) {
                    $databaseSchema = 'int(11) DEFAULT \'0\' NOT NULL,';
                } else if ($isFloat) {
                    $databaseSchema = 'double(6,2) DEFAULT \'0.00\' NOT NULL,';
                } else {
                    $databaseSchema = 'varchar(511) DEFAULT \'\' NOT NULL,';
                }
                break;
            case 'text':
                $databaseSchema = 'text,';
                break;
            case 'check':
                $databaseSchema = 'tinyint(1) unsigned DEFAULT \'0\' NOT NULL,';
                break;
            case 'inline':
            case 'select':
                $databaseSchema = 'int(11) DEFAULT \'0\' NOT NULL,';
                break;
        }
        return $databaseSchema;
    }

}
