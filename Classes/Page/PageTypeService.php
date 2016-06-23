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

use AUS\AusPage\Configuration\ConfigurationCache;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconProvider\FontawesomeIconProvider;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class PageTypeService
 *
 * @author Markus Hölzle <m.hoelzle@andersundsehr.com>
 * @package AUS\AusPage\Page
 */
class PageTypeService implements SingletonInterface
{

    const ICON_DEFAULT_PATH = 'EXT:aus_page/ext_icon.svg';

    /**
     * @var int[]
     */
    protected $pageTypeClasses = [];

    /**
     * @var ConfigurationCache
     */
    protected $configurationCache = null;


    /**
     * PageTypeService constructor.
     */
    public function __construct()
    {
        $this->configurationCache = GeneralUtility::makeInstance(ConfigurationCache::class);
        $configuration = $this->getExtensionCache();
        $this->pageTypeClasses = $configuration['pageTypeClasses'];
    }

    /**
     * @param int $dokType
     * @param string $identifier
     * @param string|null $title
     * @param string|null $iconPath
     * @return void
     */
    public function registerPageType(int $dokType, string $identifier, string $title = null, string $iconPath = null)
    {
        if ($title === null || $title === '') {
            $title = $identifier;
        }
        if ($iconPath === null || $iconPath === '') {
            $iconPath = static::ICON_DEFAULT_PATH;
        }
        $this->registerIcon($dokType, $identifier, $iconPath);

        // Add the new dokType to the list of page types
        $GLOBALS['PAGES_TYPES'][$dokType] = [
            'type' => 'web',
            'allowedTables' => '*'
        ];
        // Set pageType as "content dokType"
        $GLOBALS['TYPO3_CONF_VARS']['FE']['content_doktypes'] .= ',' . $dokType;
        // Add the new dokType to the page type selector
        $GLOBALS['TCA']['pages']['columns']['doktype']['config']['items'][] = [$title, $dokType, $iconPath];
        // Add the new dokType to the page type selector
        $GLOBALS['TCA']['pages_language_overlay']['columns']['doktype']['config']['items'][] = [$title, $dokType, $iconPath];
    }

    /**
     * @param int $dokType
     * @return void
     */
    public function addPageToBackendDragArea(int $dokType)
    {
        // Add the new dokType to the list of types available from the new page menu at the top of the page tree
        ExtensionManagementUtility::addUserTSConfig(
            'options.pageTree.doktypesToShowInNewPageDragArea := addToList(' . $dokType . ')'
        );
    }

    /**
     * @param int $dokType
     * @param string $modelClassName
     * @return void
     */
    public function addPageTypeClassMapping(int $dokType, string $modelClassName)
    {
        $this->pageTypeClasses[$modelClassName] = $dokType;
        // store for later usage in extension configuration
        $configuration = $this->getExtensionCache();
        $configuration['pageTypeClasses'] = $this->pageTypeClasses;
        $this->setExtensionCache($configuration);
    }

    /**
     * @param string $modelClassName
     * @return int
     */
    public function getPageTypeByClass(string $modelClassName): int
    {
        return isset($this->pageTypeClasses[$modelClassName]) ? $this->pageTypeClasses[$modelClassName] : 1;
    }

    /**
     * @param int $dokType
     * @return string
     */
    public function getClassByPageType(int $dokType): string
    {
        $className = array_search($dokType, $this->pageTypeClasses, true);
        return $className === false ? '' : $className;
    }

    /**
     * @param int $dokType
     * @param string $identifier
     * @param string $iconPath
     * @return void
     */
    protected function registerIcon(int $dokType, string $identifier, string $iconPath)
    {
        $iconClass = 'apps-pagetree-page-' . $identifier;

        /* @var IconRegistry $iconRegistry */
        $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
        if (substr($iconPath, -4) === '.svg') {
            $iconRegistry->registerIcon($iconClass, SvgIconProvider::class, ['source' => $iconPath]);
        } elseif (strpos($iconPath, '/') === false && strpos($iconPath, '.') === false) {
            $iconRegistry->registerIcon($iconClass, FontawesomeIconProvider::class, ['name' => $iconPath]);
        } else {
            $iconRegistry->registerIcon($iconClass, BitmapIconProvider::class, ['source' => $iconPath]);
        }

        $GLOBALS['TCA']['pages']['ctrl']['typeicon_classes'][$dokType] = $iconClass;
        $GLOBALS['TCA']['pages_language_overlay']['ctrl']['typeicon_classes'][$dokType] = $iconClass;
    }

    /**
     * @return array
     */
    protected function getExtensionCache(): array
    {
        $configuration = $this->configurationCache->getCachedConfiguration();
        return is_array($configuration) ? $configuration : [];
    }

    /**
     * @param array $configuration
     */
    protected function setExtensionCache(array $configuration)
    {
        $this->configurationCache->setCachedConfiguration($configuration);
    }

}
