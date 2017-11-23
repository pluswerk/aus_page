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
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ClassNamingUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageRepository;

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

    /**
     * @param array $pageRecord
     * @return AbstractPageRepository
     */
    public function getPageRepositoryForPageRecord($pageRecord)
    {
        $dokType = (int)$pageRecord['doktype'];

        // overlay dokType if this is a mount point
        if ($dokType === PageRepository::DOKTYPE_MOUNTPOINT && !empty($pageRecord['mount_pid']) && (int)$pageRecord['mount_pid_ol'] === 0) {
            $mountPointRecord = $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
                '*',
                'pages',
                'uid = ' . (int)$pageRecord['mount_pid'] . ' AND doktype' . $this->getTypoScriptFrontendController()->sys_page->enableFields('pages')
            );
            if ($mountPointRecord) {
                $dokType = (int)$mountPointRecord['doktype'];
            }
        }

        return $this->getPageRepositoryForDokType($dokType);
    }

    /**
     * @return DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }
}
