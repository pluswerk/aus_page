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

use AUS\AusPage\Configuration\PageConfiguration;
use AUS\AusPage\Domain\Model\DefaultPage;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Class PageConfigurationService
 *
 * @author Markus Hölzle <m.hoelzle@andersundsehr.com>
 * @package AUS\AusPage\Page
 */
class PageConfigurationService implements SingletonInterface
{

    /**
     * @var string
     */
    protected $currentExtensionKey = '';

    /**
     * @var string[]
     */
    protected $loadedExtension = [];

    /**
     * @var array[][][]
     */
    protected $loadedConfigurations = [];

    /**
     * PageConfigurationService constructor.
     */
    public function __construct()
    {
        $this->addTypoScriptMapping(DefaultPage::class);
    }

    /**
     * @return PageConfigurationService
     */
    public static function getInstance()
    {
        return GeneralUtility::makeInstance(PageConfigurationService::class);
    }

    /**
     * @param string $extensionKey
     * @param string $fileName
     * @return void
     * @throws \Exception
     */
    public function load($extensionKey, $fileName)
    {
        $this->currentExtensionKey = $extensionKey;
        if (isset($this->loadedExtension[$extensionKey]) === false) {
            $absoluteFilePath = GeneralUtility::getFileAbsFileName('EXT:' . $extensionKey . '/Configuration/AusPage/Configuration.php');
            if (file_exists($absoluteFilePath)) {
                require_once($absoluteFilePath);
            } else {
                throw new \Exception('Missing configuration file "' . $absoluteFilePath . '"');
            }
            $this->loadedExtension[$extensionKey] = $extensionKey;
        }

        if ($fileName === 'ext_localconf.php') {
            $this->loadExtLocalConf($extensionKey);
        } elseif ($fileName === 'TCA/Overrides') {
            // use 'TCA/Overrides' since typo3 8
            if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_branch) >= VersionNumberUtility::convertVersionNumberToInteger('8.0')) {
                $this->loadTcaOverrides($extensionKey);
            }
        } elseif ($fileName === 'ext_tables.php') {
            $this->loadExtTables($extensionKey);

            // legacy support for TYPO3 7
            if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_branch) < VersionNumberUtility::convertVersionNumberToInteger('8.0')) {
                $this->loadTcaOverrides($extensionKey);
            }
        } else {
            throw new \Exception('Unknown file "' . $fileName . '" could not be loaded');
        }
    }

    /**
     * This call ist just allowed from the configuration file
     *
     * @param array $configuration
     * @return void
     */
    public function addPageType(array $configuration)
    {
        if (isset($this->loadedConfigurations[$this->currentExtensionKey]) === false) {
            $this->loadedConfigurations[$this->currentExtensionKey] = [];
        }
        if (isset($this->loadedConfigurations[$this->currentExtensionKey]['addPageType']) === false) {
            $this->loadedConfigurations[$this->currentExtensionKey]['addPageType'] = [];
        }
        $this->loadedConfigurations[$this->currentExtensionKey]['addPageType'][] = $configuration;
    }

    /**
     * @param string $extensionKey
     * @return void
     */
    protected function loadExtLocalConf($extensionKey)
    {
        if (isset($this->loadedConfigurations[$extensionKey]['addPageType'])) {
            foreach ($this->loadedConfigurations[$extensionKey]['addPageType'] as $configuration) {
                $this->validateRequiredConfiguration($configuration);

                /** @var PageTypeService $pageTypeService */
                $pageTypeService = GeneralUtility::makeInstance(PageTypeService::class);
                $pageTypeService->addPageToBackendDragArea($configuration['dokType']);

                if (empty($configuration['modelClassName']) === false && class_exists($configuration['modelClassName'])) {
                    $pageTypeService->addPageTypeClassMapping($configuration['dokType'], $configuration['modelClassName']);
                    $this->addTypoScriptMapping($configuration['modelClassName']);
                }

                /** @var PagePropertyService $pagePropertyService */
                $pagePropertyService = GeneralUtility::makeInstance(PagePropertyService::class);
                if (empty($configuration['additionalProperties']) === false) {
                    $pagePropertyService->addFieldsToLocalizationIfRequired($configuration['dokType'], $configuration['additionalProperties']);
                }
                $pagePropertyService->renderGlobalLocalizationFields($configuration['dokType']);
            }
        }
    }

    /**
     * @param string $extensionKey
     * @return void
     */
    protected function loadExtTables($extensionKey)
    {
        if (isset($this->loadedConfigurations[$extensionKey]['addPageType'])) {
            /** @var PageTypeService $pageTypeService */
            $pageTypeService = GeneralUtility::makeInstance(PageTypeService::class);

            foreach ($this->loadedConfigurations[$extensionKey]['addPageType'] as $configuration) {
                $pageTypeService->registerIcon($configuration['identifier'], $configuration['icon']);
            }
        }
    }

    /**
     * @param string $extensionKey
     * @return void
     */
    protected function loadTcaOverrides($extensionKey)
    {
        if (isset($this->loadedConfigurations[$extensionKey]['addPageType'])) {
            /** @var PageTypeService $pageTypeService */
            $pageTypeService = GeneralUtility::makeInstance(PageTypeService::class);
            /** @var PagePropertyService $pagePropertyService */
            $pagePropertyService = GeneralUtility::makeInstance(PagePropertyService::class);

            foreach ($this->loadedConfigurations[$extensionKey]['addPageType'] as $configuration) {
                $this->validateRequiredConfiguration($configuration);
                $pageTypeService->registerPageType($configuration['dokType'], $configuration['identifier'], $configuration['title'], $configuration['icon']);
                $pagePropertyService->setDokTypeTitle($configuration['dokType'], $configuration['title']);
                if (empty($configuration['modelClassName']) === false && class_exists($configuration['modelClassName'])) {
                    $pageTypeService->addPageTypeClassMapping($configuration['dokType'], $configuration['modelClassName']);
                }
                if (empty($configuration['additionalTabs']) === false) {
                    $pagePropertyService->addBackendTabs($configuration['dokType'], $configuration['additionalTabs']);
                }
                if (empty($configuration['additionalProperties']) === false) {
                    $pagePropertyService->addPageProperties($configuration['dokType'], $configuration['additionalProperties']);
                }
                if (empty($configuration['showAsAdditionalProperty']) === false) {
                    $pagePropertyService->moveOrAddPagePropertiesToDokType($configuration['dokType'], explode(',', $configuration['showAsAdditionalProperty']));
                }
            }
            foreach ($this->loadedConfigurations[$extensionKey]['addPageType'] as $configuration) {
                $pagePropertyService->renderTca($configuration['dokType']);
            }
        }
    }

    /**
     * @param array $configuration
     * @return void
     * @throws \Exception
     */
    protected function validateRequiredConfiguration(array &$configuration)
    {
        // "dokType"
        if (empty($configuration['dokType'])) {
            throw new \Exception('No dokType was given!');
        }
        if (is_int($configuration['dokType']) === false) {
            throw new \Exception('DokType have to be an integer!');
        }
        if ($configuration['dokType'] < 11 || $configuration['dokType'] > 199) {
            throw new \Exception('DokType "' . $configuration['identifier']. '" (' . $configuration['dokType']. ') have to be between 10 and 200!');
        }

        if (isset($GLOBALS['PAGES_TYPES'][$configuration['dokType']])) {
            // Bugfix for extension installing in backend
            $getEM = GeneralUtility::_GET('tx_extensionmanager_tools_extensionmanagerextensionmanager');
            if ($getEM['action'] !== 'toggleExtensionInstallationState') {
                throw new \Exception('DokType "' . $configuration['identifier']. '" (' . $configuration['dokType']. ') does already exists!');
            }
        }

        // identifier
        if (empty($configuration['identifier']) || is_string($configuration['identifier']) === false) {
            throw new \Exception('No Identifier was given!');
        }
    }

    /**
     * @param string $className
     * @return void
     */
    protected function addTypoScriptMapping($className = null)
    {
        $typoScript = PHP_EOL . 'config.tx_extbase.persistence.classes.' . ltrim($className, '\\') . '.mapping.tableName = pages' . PHP_EOL;
        ExtensionManagementUtility::addTypoScript(PageConfiguration::EXTENSION_KEY, 'setup', $typoScript);
    }
}
