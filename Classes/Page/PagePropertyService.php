<?php
namespace AUS\AusPage\Page;

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

use AUS\AusPage\Database\DatabaseSchemaService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class PagePropertyService
 *
 * @author Markus Hölzle <m.hoelzle@andersundsehr.com>
 * @package AUS\AusPage\Page
 */
class PagePropertyService implements SingletonInterface
{

    /**
     * @var DatabaseSchemaService
     */
    protected $databaseSchemaService = null;

    /**
     * @var array
     * Example:
     * [
     *      $dokType => [
     *          'title' => 'my title',
     *          'pageProperties' => 'field1,field2,field3',
     *      ]
     * ]
     */
    protected $tcaFields = [];

    /**
     * @var array
     */
    protected $localizationFields = [];

    /**
     * PagePropertyService constructor.
     */
    public function __construct()
    {
        $this->databaseSchemaService = GeneralUtility::makeInstance(DatabaseSchemaService::class);
    }

    /**
     * @param int $dokType
     * @param string $title
     * @param array $fields
     * @return void
     */
    public function addPageProperties($dokType, $title, array $fields)
    {
        // Add columns section to TCA
        $this->addTcaColumns($fields);

        // Prepare fields for localization
        $this->addFieldsToLocalizationIfRequired($dokType, $fields);

        // Prepare fields to show them in TCA
        $fieldNames = array_keys($fields);
        $this->moveOrAddExistingPagePropertiesToCurrentDokTypeTab($dokType, $title, $fieldNames);

        // Prepare fields for SQL database schema
        foreach ($fields as $fieldName => $config) {
            $this->addFieldToDatabase($fieldName, $config);
        }
    }

    /**
     * @param array $fields
     * @param array|null $pagesLanguageOverlayOverwrite
     * @return void
     */
    public function addTcaColumns(array $fields, array $pagesLanguageOverlayOverwrite = null)
    {
        ExtensionManagementUtility::addTCAcolumns('pages', $fields);

        if ($pagesLanguageOverlayOverwrite !== null) {
            ArrayUtility::mergeRecursiveWithOverrule($fields, $pagesLanguageOverlayOverwrite);
        }
        $overlayFields = $fields;
        foreach ($overlayFields as $fieldName => &$fieldConfig) {
            if (isset($fieldConfig['excludeFromLanguageOverlay']) && $fieldConfig['excludeFromLanguageOverlay'] === true) {
                unset($overlayFields[$fieldName]);
            }
        }
        if (count($overlayFields) !== 0) {
            ExtensionManagementUtility::addTCAcolumns('pages_language_overlay', $overlayFields);
        }
    }

    /**
     * @param string $fieldName
     * @param array $config
     * @return void
     */
    public function addFieldToDatabase($fieldName, $config)
    {
        $this->databaseSchemaService->addProcessingField('pages', $fieldName);
        if (!isset($config['excludeFromLanguageOverlay']) || $config['excludeFromLanguageOverlay'] !== true) {
            $this->databaseSchemaService->addProcessingField('pages_language_overlay', $fieldName);
        }
    }

    /**
     * @param int $dokType
     * @param string $title
     * @param array $pageProperties
     * @return void
     */
    public function moveOrAddExistingPagePropertiesToCurrentDokTypeTab($dokType, $title, array $pageProperties)
    {
        if (isset($this->tcaFields[$dokType]) === false) {
            $this->tcaFields[$dokType] = [
                'title' => $title,
                'pageProperties' => $pageProperties,
            ];
        } else {
            $this->tcaFields[$dokType]['pageProperties'] = array_merge($this->tcaFields[$dokType]['pageProperties'], $pageProperties);
        }
    }

    /**
     * @param int $dokType
     * @return void
     */
    public function renderTca($dokType)
    {
        if (isset($this->tcaFields[$dokType])) {
            $tabConfig = ',--div--;' . $this->tcaFields[$dokType]['title'] . ', ';
            $this->tcaFields[$dokType]['pageProperties'] = array_unique($this->tcaFields[$dokType]['pageProperties']);

            // add showItems to pages
            $pagesShowItems = $tabConfig . implode(',', $this->tcaFields[$dokType]['pageProperties']);
            if (isset($GLOBALS['TCA']['pages']['types']['1']['showitem'])) {
                $GLOBALS['TCA']['pages']['types'][$dokType]['showitem'] = $GLOBALS['TCA']['pages']['types']['1']['showitem'] . $pagesShowItems;
            } elseif (is_array($GLOBALS['TCA']['pages']['types'])) {
                // use first entry in types array
                $pagesTypeDefinition = reset($GLOBALS['TCA']['pages']['types']);
                $GLOBALS['TCA']['pages']['types'][$dokType]['showitem'] = $pagesTypeDefinition['showitem'] . $pagesShowItems;
            }

            // add showItems to pages_language_overlay
            $pagesLanguageOverlayFields = [];
            foreach ($this->tcaFields[$dokType]['pageProperties'] as $pageProperty) {
                if (is_array($GLOBALS['TCA']['pages_language_overlay']['columns'][$pageProperty])) {
                    $pagesLanguageOverlayFields[] = $pageProperty;
                }
            }
            $pagesLanguageOverlayShowItems = $tabConfig . implode(',', $pagesLanguageOverlayFields);
            if (isset($GLOBALS['TCA']['pages_language_overlay']['types']['1']['showitem'])) {
                $GLOBALS['TCA']['pages_language_overlay']['types'][$dokType]['showitem'] = $GLOBALS['TCA']['pages_language_overlay']['types']['1']['showitem'] . $pagesLanguageOverlayShowItems;
            } elseif (is_array($GLOBALS['TCA']['pages_language_overlay']['types'])) {
                // use first entry in types array
                $pagesTypeDefinition = reset($GLOBALS['TCA']['pages_language_overlay']['types']);
                $GLOBALS['TCA']['pages_language_overlay']['types'][$dokType]['showitem'] = $pagesTypeDefinition['showitem'] . $pagesLanguageOverlayShowItems;
            }

            $this->renderGlobalLocalizationFields($dokType);
        }
    }

    /**
     * @param int $dokType
     * @param array $pageProperties
     * @return void
     */
    public function addFieldsToLocalizationIfRequired($dokType, array $pageProperties)
    {
        if (isset($this->localizationFields[$dokType]) === false) {
            $this->localizationFields[$dokType] = [];
        }
        foreach ($pageProperties as $pageProperty => &$pagePropertyConfiguration) {
            if (!isset($pagePropertyConfiguration['excludeFromLanguageOverlay']) || $pagePropertyConfiguration['excludeFromLanguageOverlay'] !== true) {
                $this->localizationFields[$dokType][] = $pageProperty;
            }
        }
    }

    /**
     * @param int $dokType
     * @param array $fields
     * @return void
     *
     * @deprecated This method will be removed in the next major version!
     */
    public function addFieldsToLocalization($dokType, array $fields)
    {
        if (isset($this->localizationFields[$dokType]) === false) {
            $this->localizationFields[$dokType] = [];
        }
        $this->localizationFields[$dokType] = array_merge($fields, $this->localizationFields[$dokType]);
    }

    /**
     * @param int $dokType
     * @return void
     */
    public function renderGlobalLocalizationFields($dokType)
    {
        if (isset($this->localizationFields[$dokType])) {
            // Make fields ready for localization
            $pageOverlayFields = explode(',', $GLOBALS['TYPO3_CONF_VARS']['FE']['pageOverlayFields']);
            $pageOverlayFields = array_merge($pageOverlayFields, $this->localizationFields[$dokType]);
            $pageOverlayFields = array_unique($pageOverlayFields);
            $GLOBALS['TYPO3_CONF_VARS']['FE']['pageOverlayFields'] = implode(',', $pageOverlayFields);
        }
    }

}
