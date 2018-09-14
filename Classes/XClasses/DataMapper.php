<?php

namespace AUS\AusPage\XClasses;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2017 Markus Hölzle <m.hoelzle@andersundsehr.com>, anders und sehr GmbH
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

use AUS\AusPage\Domain\Model\AbstractPage;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * Class DataMapper
 *
 * @author Markus Hölzle <m.hoelzle@andersundsehr.com>
 * @package AUS\AusPage\XClasses
 */
class DataMapper extends \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper
{
    /**
     * @param DomainObjectInterface $object
     * @param array $row
     * @return void
     */
    protected function thawProperties(DomainObjectInterface $object, array $row)
    {
        if ($object instanceof AbstractPage) {
            if (!empty($row['_PAGES_OVERLAY_UID'])) {
                // Make some arrangements to use language meta fields later
                $dataMap = $this->getDataMap(get_class($object));
                $dataMap->setLanguageIdColumnName('_PAGES_OVERLAY_LANGUAGE');
                $row['_LOCALIZED_UID'] = $row['_PAGES_OVERLAY_UID'];
            } elseif ((int)$GLOBALS['TSFE']->sys_language_uid !== 0) {
                // Try to find the language meta fields.
                // This is just used, if a page is a relation of an other object and will be resolved automatically.
                $currentOverlayRecord = $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
                    'uid',
                    'pages_language_overlay',
                    'pid=' . (int)$row['uid'] . ' AND sys_language_uid=' . (int)$GLOBALS['TSFE']->sys_language_uid .
                    $this->getPageRepository()->enableFields('pages_language_overlay')
                );
                if ($currentOverlayRecord) {
                    $dataMap = $this->getDataMap(get_class($object));
                    $dataMap->setLanguageIdColumnName('_PAGES_OVERLAY_LANGUAGE');
                    $row['_LOCALIZED_UID'] = $currentOverlayRecord['uid'];
                    $row['_PAGES_OVERLAY_LANGUAGE'] = (int)$GLOBALS['TSFE']->sys_language_uid;
                }
            }
        }
        parent::thawProperties($object, $row);
    }

    /**
     * @return DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * @return PageRepository
     */
    protected function getPageRepository()
    {
        return $GLOBALS['TSFE']->sys_page;
    }
}
