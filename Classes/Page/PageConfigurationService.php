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
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class PageConfigurationService
 *
 * @author Markus Hölzle <m.hoelzle@andersundsehr.com>
 * @package AUS\AusPage\Page
 */
class PageConfigurationService implements SingletonInterface
{

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected $objectManager = null;

    /**
     * PageConfigurationService constructor.
     */
    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
    }

    /**
     * @return PageConfigurationService
     */
    public static function getInstance(): PageConfigurationService
    {
        return GeneralUtility::makeInstance(PageConfigurationService::class);
    }

    /**
     * @param array $configuration
     * @return void
     */
    public function addPageType(array $configuration)
    {
        $this->validateRequiredConfiguration($configuration);

        /** @var PageTypeService $pageTypeService */
        $pageTypeService = $this->objectManager->get(PageTypeService::class);
        $pageTypeService->registerPageType($configuration['dokType'], $configuration['identifier'], $configuration['title'], $configuration['icon']);

        $this->addTypoScriptMapping($configuration['modelClassName']);

        /** @var PagePropertyService $pagePropertyService */
        $pagePropertyService = $this->objectManager->get(PagePropertyService::class);
        $pagePropertyService->addPageProperties($configuration['dokType'], $configuration['title'], $configuration['additionalProperties']);
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
        if ($configuration['dokType'] < 11 || $configuration['dokType'] > 199 ) {
            throw new \Exception('DokType "' . $configuration['identifier']. '" (' . $configuration['dokType']. ') have to be between 10 and 200!');
        }
        if (isset($GLOBALS['PAGES_TYPES'][$configuration['dokType']])) {
            throw new \Exception('DokType "' . $configuration['identifier']. '" (' . $configuration['dokType']. ') does already exists!');
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
    protected function addTypoScriptMapping(string $className = null)
    {
        if (empty($className) === false && class_exists($className)) {
            $typoScript = PHP_EOL . 'config.tx_extbase.persistence.classes.' . ltrim($className, '\\') . '.tableName = pages' . PHP_EOL;
            ExtensionManagementUtility::addTypoScript(PageConfiguration::EXTENSION_KEY, 'setup', $typoScript);
        }
    }

}
