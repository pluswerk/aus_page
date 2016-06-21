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
use AUS\AusPage\Domain\Repository\AbstractPageRepository;
use AUS\AusPage\Domain\Repository\DefaultPageRepository;
use AUS\AusPage\Domain\Repository\PageCategoryRepository;
use AUS\AusPage\Page\PageTypeService;
use TYPO3\CMS\Core\Utility\ClassNamingUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * Class NavigationController
 *
 * @author Markus Hölzle <m.hoelzle@andersundsehr.com>
 * @package AUS\AusPage\Controller
 */
class NavigationController extends ActionController
{

    /**
     * @return void
     */
    protected function initializeOneLevelNavigationAction(){
        $propertyMappingConfiguration = $this->arguments['filter']->getPropertyMappingConfiguration();
        $propertyMappingConfiguration->allowProperties('pageCategoryUid', 'fields');
        $propertyMappingConfiguration->setTypeConverterOption(PersistentObjectConverter::class, PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, TRUE);
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

        // DokType
        if ($this->settings['dokType'] > 0) {
            /** @var PageTypeService $pageTypeService */
            $pageTypeService = $this->objectManager->get(PageTypeService::class);
            $modelClassName = $pageTypeService->getClassByPageType($this->settings['dokType']);
            if ($modelClassName !== '') {
                $repositoryClassName = ClassNamingUtility::translateModelNameToRepositoryName($modelClassName);
                /** @var AbstractPageRepository $repository */
                $repository = $this->objectManager->get($repositoryClassName);
            } else {
                /** @var DefaultPageRepository $repository */
                $repository = $this->objectManager->get(DefaultPageRepository::class);
                $repository->setDokType($this->settings['dokType']);
            }
        } else {
            /** @var DefaultPageRepository $repository */
            $repository = $this->objectManager->get(DefaultPageRepository::class);
        }

        if (empty($this->settings['template']) === false) {
            /** @var TemplateView $view */
            $view = $this->view;
            $view->setTemplateRootPaths(array_merge($this->settings['templates'][$this->settings['template']]['templateRootPaths'], $view->getTemplateRootPaths()));
            $view->setPartialRootPaths($this->settings['templates'][$this->settings['template']]['partialRootPaths']);
            $view->setLayoutRootPaths($this->settings['templates'][$this->settings['template']]['layoutRootPaths']);
        }

        $this->view->assign('pages', $repository->findByFilter($filter, $this->settings['startPage']));
    }

    /**
     * @return void
     */
    public function oneLevelCategoryNavigationAction()
    {
        $this->settings['dokType'] = (int)$this->settings['dokType'];
        $this->settings['startPage'] = (int)$this->settings['startPage'];
        if ($this->settings['dokType'] === 0 && $this->settings['startPage'] === 0) {
            return;
        }

        /** @var PageCategoryRepository $categoryRepository */
        $categoryRepository = $this->objectManager->get(PageCategoryRepository::class);

        if (empty($this->settings['template']) === false) {
            /** @var TemplateView $view */
            $view = $this->view;
            $rootPaths = $view->getTemplateRootPaths();
            $view->setTemplateRootPaths(array_merge($this->settings['templates'][$this->settings['template']]['templateRootPaths'], ($rootPaths !== null ? $rootPaths : [])));
            $view->setPartialRootPaths($this->settings['templates'][$this->settings['template']]['partialRootPaths']);
            $view->setLayoutRootPaths($this->settings['templates'][$this->settings['template']]['layoutRootPaths']);
        }

        $this->view->assign('pageCategories', $categoryRepository->findByDokType($this->settings['dokType'], $this->settings['startPage']));
    }

    /**
     * @return PageRepository
     */
    protected function getTYPO3PageRepository(): PageRepository
    {
        return $GLOBALS['TSFE']->sys_page;
    }

}
