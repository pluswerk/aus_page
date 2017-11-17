<?php

namespace AUS\AusPage\Controller;

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

use AUS\AusPage\Domain\Model\PageFilter;
use AUS\AusPage\Domain\Repository\DefaultPageRepository;
use AUS\AusPage\Service\RepositoryService;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Controller\MvcPropertyMappingConfiguration;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class PageController
 *
 * @author Markus Hölzle <m.hoelzle@andersundsehr.com>
 * @package AUS\AusPage\Controller
 */
class PageController extends ActionController
{

    /**
     * @var \AUS\AusPage\Domain\Repository\PageCategoryRepository
     * @inject
     */
    protected $categoryRepository = null;


    /**
     * @return void
     */
    public function detailAction()
    {
        $this->settings = $this->mergeSettingsFromTypoScriptTemplate($this->settings);
        if (empty($this->settings['template']) === false) {
            $this->mergeViewPaths($this->view, $this->settings['templates'][$this->settings['template']]['view']);
        }
        /** @var RepositoryService $repositoryService */
        $repositoryService = $this->objectManager->get(RepositoryService::class);
        $repository = $repositoryService->getPageRepositoryForDokType((int)$this->getTypoScriptFrontendController()->page['doktype']);
        $this->view->assignMultiple([
            'settings' => $this->settings,
            'page' => $repository->findByUid((int)$this->getTypoScriptFrontendController()->id),
        ]);
    }

    /**
     * @return void
     */
    protected function initializeOneLevelNavigationAction()
    {
        /** @var MvcPropertyMappingConfiguration $propertyMappingConfiguration */
        $propertyMappingConfiguration = $this->arguments['filter']->getPropertyMappingConfiguration();
        $propertyMappingConfiguration->allowProperties('pageCategoryUid', 'fields', 'offset');
        $propertyMappingConfiguration->setTypeConverterOption(PersistentObjectConverter::class, PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, true);
    }

    /**
     * @param PageFilter $filter
     * @return void
     */
    public function oneLevelNavigationAction(PageFilter $filter = null)
    {
        if ($filter === null) {
            $filter = $this->objectManager->get(PageFilter::class);
        }
        $this->settings = $this->mergeSettingsFromTypoScriptTemplate($this->settings);
        $this->settings['dokType'] = (int)$this->settings['dokType'];
        $this->settings['startPage'] = (int)$this->settings['startPage'];
        $this->settings['pageCategory'] = (int)$this->settings['pageCategory'];
        if ($this->settings['dokType'] === 0 && $this->settings['startPage'] === 0 && $this->settings['pageCategory'] === 0) {
            return;
        }

        // PageCategory
        if ($this->settings['pageCategory'] !== 0) {
            $filter->setPageCategoryUid($this->settings['pageCategory']);
        }
        // SelectedPages
        if ($this->settings['selectedPages']) {
            $filter->setSelectedPages($this->settings['selectedPages']);
        }

        // DokType
        if ($this->settings['dokType'] > 0) {
            /** @var RepositoryService $repositoryService */
            $repositoryService = $this->objectManager->get(RepositoryService::class);
            $repository = $repositoryService->getPageRepositoryForDokType($this->settings['dokType']);
        } else {
            /** @var DefaultPageRepository $repository */
            $repository = $this->objectManager->get(DefaultPageRepository::class);
        }

        if (empty($this->settings['template']) === false) {
            $this->mergeViewPaths($this->view, $this->settings['templates'][$this->settings['template']]['view']);
        }
        if (empty($this->settings['pageFilter']) === false) {
            $this->mergePageFilterSettingsFromSettings($filter, $this->settings);
        }

        $pages = $repository->findByFilter($filter, $this->settings['startPage']);

        /*
         * extra $mappedPages as mount point pages
         * the mount point pages are to be searched and mapped only for pages with the defined doktype and langauge
         */
        $mappedPages = array();
        if ($ausPagePluginId = $this->settings['languageAusPageIdMapping'][$this->settings['dokType']][$GLOBALS['TSFE']->sys_language_uid]) {
            foreach ($pages as $page) {
                if ($mappedPage = $repository->getMappedPageWithMountPid($repository->getAllPagesInOrder($ausPagePluginId), $page->getUid())) {
                    $mappedPages[$page->getUid()]['mappedPage'] = $mappedPage;
                }
            }
        }

        $this->view->assignMultiple([
            'settings' => $this->settings,
            'filter' => $filter,
            'pageCategory' => ($filter->getPageCategoryUid() !== 0 ? $this->categoryRepository->findByUid($filter->getPageCategoryUid()) : null),
            'pages' => $pages,
            'mappedPages' => $mappedPages,
        ]);
    }

    /**
     * @return void
     */
    public function oneLevelCategoryNavigationAction()
    {
        $this->settings = $this->mergeSettingsFromTypoScriptTemplate($this->settings);
        $this->settings['dokType'] = (int)$this->settings['dokType'];
        $this->settings['startPage'] = (int)$this->settings['startPage'];
        if ($this->settings['dokType'] === 0 && $this->settings['startPage'] === 0) {
            return;
        }

        if (empty($this->settings['template']) === false) {
            $this->mergeViewPaths($this->view, $this->settings['templates'][$this->settings['template']]['view']);
        }
        $activePageCategoryUid = null;
        $oneLevelNavigation = GeneralUtility::_GP('tx_auspage_onelevelnavigation');
        if (isset($oneLevelNavigation['filter']['pageCategoryUid'])) {
            $activePageCategoryUid = (int)$oneLevelNavigation['filter']['pageCategoryUid'];
        }

        $this->view->assignMultiple([
            'settings' => $this->settings,
            'activePageCategoryUid' => $activePageCategoryUid,
            'pageCategories' => $this->categoryRepository->findByDokType($this->settings['dokType'], $this->settings['startPage']),
        ]);
    }


    /**
     * @param array $settings
     * @return array
     */
    protected function mergeSettingsFromTypoScriptTemplate(array $settings)
    {
        if (empty($settings['template']) === false &&
            empty($settings['templates'][$settings['template']]) === false &&
            empty($settings['templates'][$settings['template']]['settings']) === false &&
            is_array($settings['templates'][$settings['template']]['settings'])
        ) {
            $this->unsetEmptyValuesFromArray($settings);
            ArrayUtility::mergeRecursiveWithOverrule($settings, $settings['templates'][$settings['template']]['settings']);
        }
        return $settings;
    }

    /**
     * @param array $array
     * @return void
     */
    protected function unsetEmptyValuesFromArray(array &$array)
    {
        foreach ($array as $key => $value) {
            if (empty($value)) {
                unset($array[$key]);
            }
        }
    }

    /**
     * @param PageFilter $filter
     * @param array $settings
     * @return void
     */
    protected function mergePageFilterSettingsFromSettings(PageFilter $filter, array $settings)
    {
        foreach ($settings['pageFilter'] as $key => $value) {
            $filter->_setProperty($key, $value);
        }
    }

    /**
     * @param ViewInterface $view
     * @param array $viewSettings
     * @return void
     */
    protected function mergeViewPaths(ViewInterface $view, array $viewSettings)
    {
        if ($view instanceof TemplateView) {
            $rootPaths = $view->getTemplateRootPaths();
            if (is_array($viewSettings['templateRootPaths'])) {
                $view->setTemplateRootPaths(array_reverse(array_merge(
                    ($rootPaths !== null ? $rootPaths : []),
                    $this->resolvePathArray($viewSettings['templateRootPaths'])
                ), true));
            }
            if (is_array($viewSettings['partialRootPaths'])) {
                $view->setPartialRootPaths($this->resolvePathArray($viewSettings['partialRootPaths']));
            }
            if (is_array($viewSettings['layoutRootPaths'])) {
                $view->setLayoutRootPaths($this->resolvePathArray($viewSettings['layoutRootPaths']));
            }
        }
    }

    /**
     * @param array $paths
     * @return array
     */
    protected function resolvePathArray(array $paths)
    {
        foreach ($paths as &$path) {
            $path = GeneralUtility::getFileAbsFileName($path);
        }
        return $paths;
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }
}
