<?php
namespace AUS\AusPage\Service;

/***
 * This file is part of an "anders und sehr GmbH" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2017 Markus Hölzle <m.hoelzle@andersundsehr.com>, anders und sehr GmbH
 ***/

use AUS\AusPage\Domain\Repository\AbstractPageRepository;
use AUS\AusPage\Domain\Repository\DefaultPageRepository;
use AUS\AusPage\Page\PageTypeService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ClassNamingUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * Class GetPageViewHelper
 *
 * @author Markus Hölzle <m.hoelzle@andersundsehr.com>
 * @package AUS\AusPage\ViewHelpers
 */
class RepositoryService implements SingletonInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * RepositoryService constructor.
     */
    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
    }

    /**
     * @param int $dokType
     * @return AbstractPageRepository
     */
    public function getPageRepositoryForDokType($dokType)
    {
        /** @var PageTypeService $pageTypeService */
        $pageTypeService = $this->objectManager->get(PageTypeService::class);
        $modelClassName = $pageTypeService->getClassByPageType($dokType);
        if ($modelClassName !== '') {
            $repositoryClassName = ClassNamingUtility::translateModelNameToRepositoryName($modelClassName);
            /** @var AbstractPageRepository $repository */
            $repository = $this->objectManager->get($repositoryClassName);
        } else {
            /** @var DefaultPageRepository $repository */
            $repository = $this->objectManager->get(DefaultPageRepository::class);
            $repository->setDokType($dokType);
        }
        return $repository;
    }
}
