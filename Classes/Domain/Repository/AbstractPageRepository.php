<?php

namespace AUS\AusPage\Domain\Repository;

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

use AUS\AusPage\Domain\Model\MMConfig;
use AUS\AusPage\Domain\Model\PageFilter;
use AUS\AusPage\Page\PageTypeService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ClassNamingUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Page\PageRepository;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * Abstract repository for objects which are "pages"
 *
 * @author Markus Hölzle <m.hoelzle@andersundsehr.com>
 * @package AUS\AusPage\Domain\Repository
 */
abstract class AbstractPageRepository implements SingletonInterface
{
    /**
     * @var string
     */
    protected $defaultSorting = 'pages.sorting ASC';

    /**
     * @var int
     */
    protected $dokType = 1;

    /**
     * @var string
     */
    protected $modelClassName = '';

    /**
     * @var \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected $databaseConnection = null;

    /**
     * @var \TYPO3\CMS\Frontend\Page\PageRepository
     */
    protected $pageRepository = null;

    /**
     * @var \TYPO3\CMS\Frontend\Page\PageRepository
     */
    protected $pageRepositoryShowHidden = null;

    /**
     * @var ObjectManager
     */
    protected $objectManager = null;

    /**
     * @var ContentObjectRenderer
     */
    protected $contentObject = null;

    /**
     * @var DataMapper
     */
    protected $dataMapper;

    /**
     * @var bool
     */
    protected $enableMountPoints = false;

    /**
     * initializeObject
     */
    public function __construct()
    {
        $this->databaseConnection = $GLOBALS['TYPO3_DB'];
        $this->pageRepository = $GLOBALS['TSFE']->sys_page;
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->dataMapper = $this->objectManager->get(DataMapper::class);
        $this->pageRepositoryShowHidden = $this->objectManager->get(PageRepository::class);
        $this->pageRepositoryShowHidden->init(false);

        $this->modelClassName = ClassNamingUtility::translateRepositoryNameToModelName(get_class($this));
        if ($this->dokType === 1) {
            /** @var PageTypeService $pageTypeService */
            $pageTypeService = $this->objectManager->get(PageTypeService::class);
            $this->dokType = $pageTypeService->getPageTypeByClass($this->modelClassName);
        }
    }

    /**
     * @return ContentObjectRenderer
     */
    protected function getContentObject()
    {
        if ($this->contentObject === null) {
            $this->contentObject = $this->objectManager->get(ContentObjectRenderer::class);
        }
        return $this->contentObject;
    }

    /**
     * @param int $rootLinePid
     * @return \AUS\AusPage\Domain\Model\AbstractPage[]
     */
    public function findAll($rootLinePid = 0)
    {
        return $this->findByWhereClause('', $rootLinePid);
    }

    /**
     * findByUid
     * @param int $pageUid
     * @param int $rootLinePid
     * @return \AUS\AusPage\Domain\Model\AbstractPage
     */
    public function findByUid($pageUid, $rootLinePid = 0)
    {
        return $this->findByWhereClause('uid = ' . $this->getRealPageUid((int)$pageUid), $rootLinePid)[0];
    }

    /**
     * @param PageFilter $pageFilter
     * @param int $rootLinePid
     * @return array
     */
    public function findByFilter(PageFilter $pageFilter, $rootLinePid = 0)
    {
        $conditions = [];
        $mmConfigs = [];
        foreach ($pageFilter->getFields() as $fieldName => $fieldValue) {
            if (isset($GLOBALS['TCA']['pages']['columns'][$fieldName]['config'])) {
                if (is_array($fieldValue)) {
                    //interpret as Date:
                    if (isset($fieldValue['year']) && empty($fieldValue['year']) == false) {
                        //year must equal $fieldValue['year']
                        $date = new \DateTime();
                        $date->setDate((int)$fieldValue['year'], 1, 1);
                        $date->setTime(0, 0, 0);
                        $conditions[] = 'pages.' . $fieldName . ' > ' . $date->getTimestamp();
                        $date->setDate((int)$fieldValue['year'] + 1, 1, 1);
                        $conditions[] = 'pages.' . $fieldName . ' < ' . $date->getTimestamp();
                    }

                    if (isset($fieldValue['dateMinimal']) && !empty($fieldValue['dateMinimal'])) {
                        $date = new \DateTime($fieldValue['dateMinimal']);
                        $conditions[] = 'pages.' . $fieldName . ' > ' . $date->getTimestamp();
                    }
                    if (isset($fieldValue['dateMaximal']) && !empty($fieldValue['dateMaximal'])) {
                        $date = new \DateTime($fieldValue['dateMaximal']);
                        $conditions[] = 'pages.' . $fieldName . ' < ' . $date->getTimestamp();
                    }
                    /** @deprecated will be removed in version 2.0.0 use dateMinimal instead */
                    if (isset($fieldValue['from']) && !empty($fieldValue['from'])) {
                        $date = \DateTime::createFromFormat('Y-m-d', $fieldValue['from']);
                        $conditions[] = 'pages.' . $fieldName . ' > ' . $date->getTimestamp();
                    }
                    /** @deprecated will be removed in version 2.0.0 use dateMaximal instead  */
                    if (isset($fieldValue['to']) && !empty($fieldValue['to'])) {
                        $date = \DateTime::createFromFormat('Y-m-d', $fieldValue['to']);
                        $conditions[] = 'pages.' . $fieldName . ' < ' . $date->getTimestamp();
                    }
                } else {
                    $TCAConfig = $GLOBALS['TCA']['pages']['columns'][$fieldName]['config'];
                    if (isset($TCAConfig['MM']) && isset($TCAConfig['foreign_table'])) {
                        if ((string)$fieldValue === (string)(int)$fieldValue) {
                            $mmConfig = $this->objectManager->get(MMConfig::class);
                            $mmConfig->setMMTable($TCAConfig['MM']);
                            $mmConfig->setForeignTable($TCAConfig['foreign_table']);
                            $mmConfig->setCompareValue($fieldValue);
                            $mmConfigs[] = $mmConfig;
                        } else {
                            continue;
                        }
                    } else {
                        $conditions[] = 'pages.' . $fieldName . ' = ' . $this->databaseConnection->quoteStr($fieldValue, 'pages');
                    }
                }
            }
        }

        if ($pageFilter->getSelectedPages() !== []) {
            $conditions[] = 'pages.uid IN(' . implode(',', $this->databaseConnection->cleanIntArray($pageFilter->getSelectedPages())) . ')';
        }

        $pages = $this->findByWhereClause(
            implode(' AND ', $conditions),
            $rootLinePid,
            $pageFilter->getLimit(),
            $pageFilter->getOffset(),
            $pageFilter->isSortRecursive(),
            $mmConfigs,
            $pageFilter->getPageTreeDepth(),
            $pageFilter->getPageTreeBegin()
        );
        if ($pageFilter->getSelectedPages() !== []) {
            $pages = $this->sortBySelectedPages($pageFilter, $pages);
        }

        return $pages;
    }

    /**
     * @param PageFilter $pageFilter
     * @param \AUS\AusPage\Domain\Model\AbstractPage[] $pages
     * @return \AUS\AusPage\Domain\Model\AbstractPage[]
     */
    protected function sortBySelectedPages(PageFilter $pageFilter, array $pages): array
    {
        $uidList = $pageFilter->getSelectedPages();
        $pagesResult = [];
        foreach ($pages as $page) {
            if (($key = array_search($page->getUid(), $uidList)) !== false) {
                $pagesResult[$key] = $page;
            }
        }
        ksort($pagesResult);
        return array_values(array_filter($pagesResult));
    }

    /**
     * @param string $whereClause
     * @param int $rootLinePid
     * @param int $limit
     * @param int $offset
     * @param bool $sortRecursive
     * @param MMConfig[] $mmConfigs
     * @param int $pageTreeDepth
     * @param int $pageTreeBegin
     * @return \AUS\AusPage\Domain\Model\AbstractPage[]
     */
    public function findByWhereClause(
        $whereClause,
        $rootLinePid = 0,
        $limit = 0,
        $offset = 0,
        $sortRecursive = false,
        array $mmConfigs = [],
        $pageTreeDepth = 99,
        $pageTreeBegin = 0
    ) {
        /** @var int[]|null $allPageUidArray */
        $allPageUidArray = null;
        $addedPidList = [];
        $addedPidListMapping = [];
        if ($whereClause !== '') {
            $whereClause = $whereClause . ' AND ';
        }
        if ($rootLinePid !== 0) {
            $pidList = $this->getContentObject()->getTreeList($rootLinePid, $pageTreeDepth, $pageTreeBegin, '1=1');

            // add mount point pages
            if ($this->enableMountPoints) {
                $addedMountPointPages = $this->addMountPointPages($pidList);
                $pidList = $addedMountPointPages['pidList'];
                $addedPidList = $addedMountPointPages['addedPidList'];
                $addedPidListMapping = $addedMountPointPages['addedPidListMapping'];
            }
            if ($pidList === '') {
                return []; // we have no child pages
            }
            $whereClause .= ' pages.uid IN(' . $pidList . ') AND ';
        }
        $whereClause .= 'pages.doktype = ' . $this->dokType;
        $whereClause .= $this->pageRepository->enableFields('pages');

        $limitString = '';
        if ($limit !== 0) {
            $limitString = (string)$limit;
            if ($offset !== 0) {
                $limitString = $offset . ', ' . $limitString;
            }
        }

        // resolve mm relation to page categories
        if (!empty($mmConfigs)) {
            foreach ($mmConfigs as $mmConfig) {
                $allPageUidArray = $this->executeMMConfig($mmConfig, $whereClause, $limitString, $allPageUidArray);
            }
        } else {
            $allPageUidArray = array_keys(
                $this->databaseConnection->exec_SELECTgetRows(
                    'pages.uid',
                    'pages',
                    $whereClause,
                    '',
                    $this->defaultSorting,
                    $limitString,
                    'uid'
                )
            );
        }

        if ($sortRecursive && $rootLinePid) {
            //In $flattenedTree are the uids in order, but there are also some uids that we don't want
            $flattenedTree = $this->getAllPagesInOrder($rootLinePid);
            //In $allPageUidArray are all uids that we want, but they are not in the Order that we want.
            //With array_intersect we get only the uids what we want and in they are in order.
            $allPageUidArrayOriginal = $allPageUidArray;
            $allPageUidArray = array_intersect($flattenedTree, $allPageUidArray);

            if ($this->enableMountPoints) {
                // added mount point pages are to be again
                $toRunResort = false;
                if (count($addedPidList) !== 0 && count($addedPidListMapping) !== 0) {
                    foreach ($allPageUidArrayOriginal as $pid) {
                        if (in_array($pid, $addedPidList)) {
                            $allPageUidArray[] = $pid;
                            $toRunResort = true;
                        }
                    }
                    $allPageUidArray = array_unique($allPageUidArray);
                }
                // resort mount points
                if ($toRunResort) {
                    usort($allPageUidArray, function ($pageUidA, $pageUidB) use ($flattenedTree, $addedPidListMapping) {
                        $keyA = array_search(
                            ($addedPidListMapping[$pageUidA] ? $addedPidListMapping[$pageUidA] : $pageUidA),
                            $flattenedTree
                        );
                        $keyB = array_search(
                            ($addedPidListMapping[$pageUidB] ? $addedPidListMapping[$pageUidB] : $pageUidB),
                            $flattenedTree
                        );
                        return $keyA - $keyB;
                    });
                }
            }
        }

        // signalSlotDispatcher
        $signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        $pages = [];
        foreach ($allPageUidArray as $pageUid) {
            $pageRecord = $this->pageRepository->getPage($pageUid);

            /* default: pageRecord of default language */
            $signalSlotDispatcher->dispatch(
                __CLASS__,
                'findByWhereClauseMountedPageRecord',
                array($addedPidListMapping[$pageUid], $this->pageRepository, &$pageRecord)
            );

            if ($pageRecord) {
                $pages[] = $pageRecord;
            }
        }

        return $this->mapResultToModel($pages);
    }

    /**
     * @param $pidList
     * @return array
     */
    protected function addMountPointPages($pidList)
    {
        $pidListArray = GeneralUtility::trimExplode(',', $pidList, true);
        $addedPidListMapping = [];
        foreach ($pidListArray as $pid) {
            // mount_pid_ol = 1: pid exists already in pidList
            $whereClause = 'uid = ' . $pid . ' AND mount_pid AND mount_pid_ol = 0' . $this->pageRepository->enableFields('pages');
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'pages', $whereClause, '', '', 1);
            if ($res) {
                $mountPid = $res[0]['mount_pid'];
                $whereClause = 'uid = ' . $mountPid . ' AND doktype = ' . $this->dokType . $this->pageRepository->enableFields('pages');
                $res = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'pages', $whereClause, '', '', 1);
                if ($res) {
                    $pidListArray[] = $mountPid;
                    $addedPidListMapping[$mountPid] = (int)$pid;
                }
            }
        }
        $pidListArray = array_unique($pidListArray);
        $addedPidListArray = array_diff($pidListArray, GeneralUtility::trimExplode(',', $pidList, true));
        return [
            'pidList' => implode(',', $pidListArray),
            'addedPidList' => $addedPidListArray,
            'addedPidListMapping' => $addedPidListMapping,
        ];
    }

    /**
     * @param MMConfig $mmConfig
     * @param $whereClause
     * @param $limitString
     * @param $allPageUidArray
     * @return array|null
     */
    protected function executeMMConfig(MMConfig $mmConfig, $whereClause, $limitString, $allPageUidArray)
    {
        $resource = $this->databaseConnection->exec_SELECT_mm_query(
            'pages.uid',
            'pages',
            $mmConfig->getMMTable(),
            $mmConfig->getForeignTable(),
            ' AND ' . $mmConfig->getWhereClause() . ' AND ' . $whereClause,
            '',
            $this->defaultSorting,
            $limitString
        );
        if ($resource) {
            $tempPageUidArray = [];
            while ($record = $this->databaseConnection->sql_fetch_assoc($resource)) {
                $tempPageUidArray[] = $record['uid'];
            }
            $this->databaseConnection->sql_free_result($resource);

            if ($allPageUidArray === null) {
                $allPageUidArray = $tempPageUidArray;
            } else {
                $allPageUidArray = array_intersect($allPageUidArray, $tempPageUidArray);
            }
        }
        return $allPageUidArray;
    }

    /**
     * @param int $pageUid
     * @param int[] $result
     * @return \int[]
     * @internal param array $tree
     */
    public function getAllPagesInOrder($pageUid = 1, array $result = [])
    {
        $result[] = (int)$pageUid;
        foreach ($this->pageRepositoryShowHidden->getMenu($pageUid) as $page) {
            $result = $this->getAllPagesInOrder($page['uid'], $result);
        }
        return $result;
    }

    /**
     * @param array $rows
     * @return \AUS\AusPage\Domain\Model\AbstractPage[]
     */
    protected function mapResultToModel(&$rows)
    {
        return $this->dataMapper->map($this->modelClassName, $rows);
    }

    /**
     * Get mount point page pid
     *
     * @param int $pageUid
     * @return int
     */
    protected function getRealPageUid($pageUid)
    {
        if ($this->enableMountPoints) {
            // If this is a mount point we have to look up for the real page uid
            $whereClause = 'uid = ' . $pageUid . ' AND doktype = ' . PageRepository::DOKTYPE_MOUNTPOINT . ' AND mount_pid AND mount_pid_ol = 0';
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'pages', $whereClause, '', '', 1);
            if ($res) {
                $pageUid = $res[0]['mount_pid'];
            }
        }
        return (int)$pageUid;
    }
}
