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

use AUS\AusPage\Page\PageTypeService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ClassNamingUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Abstract repository for objects which are "pages"
 *
 * @author Markus Hölzle <m.hoelzle@andersundsehr.com>
 * @package AUS\AusPage\Domain\Repository
 */
abstract class AbstractPageRepository implements SingletonInterface
{

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
     * initializeObject
     */
    public function __construct()
    {
        $this->databaseConnection = $GLOBALS['TYPO3_DB'];
        $this->pageRepository = $GLOBALS['TSFE']->sys_page;
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->dataMapper = $this->objectManager->get(DataMapper::class);

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
    protected function getContentObject(): ContentObjectRenderer
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
    public function findAll(int $rootLinePid = 0): array
    {
        return $this->findByWhereClause('', $rootLinePid);
    }

    /**
     * findByUid
     * @param int $pageUid
     * @return \AUS\AusPage\Domain\Model\AbstractPage
     */
    public function findByUid(int $pageUid)
    {
        return $this->findByWhereClause('uid = ' . (int)$pageUid)[0];
    }

    /**
     * @param string $whereClause
     * @param int $rootLinePid
     * @return \AUS\AusPage\Domain\Model\AbstractPage[]
     */
    public function findByWhereClause(string $whereClause, int $rootLinePid = 0): array
    {
        $allPageUidArray = [];
        if ($whereClause !== '') {
            $whereClause = $whereClause . ' AND ';
        }
        if ($rootLinePid !== 0) {
            $pidList = $this->getContentObject()->getTreeList($rootLinePid, 99, 0, '1=1');
            if ($pidList === '') {
                return []; // we have no child pages
            }
            $whereClause .= ' pages.uid IN(' . $pidList . ') AND ';
        }
        $whereClause = $whereClause . 'pages.doktype = ' . $this->dokType;

        // resolve mm relation to page categories
        if (strpos($whereClause, 'page_categories.') !== false) {
            $resource = $this->databaseConnection->exec_SELECT_queryArray([
                'SELECT' => 'pages.uid',
                'FROM' => 'pages,tx_auspage_page_pagecategory_mm,pages AS page_categories',
                'WHERE' => 'pages.uid=tx_auspage_page_pagecategory_mm.uid_local AND page_categories.uid=tx_auspage_page_pagecategory_mm.uid_foreign AND ' . $whereClause,
                'GROUPBY' => '',
                'ORDERBY' => 'pages.sorting ASC',
                'LIMIT' => '',
            ]);
            if ($resource) {
                while ($record = $this->databaseConnection->sql_fetch_assoc($resource)) {
                    $allPageUidArray[] = $record['uid'];
                }
                $this->databaseConnection->sql_free_result($resource);
            }
        } else {
            $allPageUidArray = array_keys($this->databaseConnection->exec_SELECTgetRows('pages.uid', 'pages', $whereClause, '', 'pages.sorting ASC', '', 'uid'));
        }
        $pages = [];
        foreach($allPageUidArray as $pageUid) {
            $pageRecord = $this->pageRepository->getPage($pageUid);
            if ($pageRecord) {
                $pages[] = $pageRecord;
            }
        }
        return $this->mapResultToModel($pages);
    }

    /**
     * @param $rows
     * @return \AUS\AusPage\Domain\Model\AbstractPage[]
     */
    protected function mapResultToModel(&$rows): array
    {
        return $this->dataMapper->map($this->modelClassName, $rows);
    }

}
