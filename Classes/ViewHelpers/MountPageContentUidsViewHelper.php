<?php

namespace AUS\AusPage\ViewHelpers;

/***
 * This file is part of an "anders und sehr GmbH" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2017 Sinian Zhang <s.zhang@andersundsehr.com>, anders und sehr GmbH
 ***/

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use AUS\AusPage\Domain\Model\AbstractPage;

class MountPageContentUidsViewHelper extends AbstractViewHelper
{

    /**
     * Get all content uids from the mount page
     *
     * @param AbstractPage $page
     * @param int $doktype
     *
     * @return boolean
     */
    public function render($page, $doktype)
    {
        $contentUids = array();
        $whereClause = 'uid = ' . $page->getUid() . ' AND doktype = ' . $doktype . $GLOBALS['TSFE']->sys_page->enableFields('pages');
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'pages', $whereClause, '', '', 1);
        if ($res) {
            $pid = $res[0]['uid'];
            $whereClause = 'pid = ' . $pid . $GLOBALS['TSFE']->sys_page->enableFields('tt_content');
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tt_content', $whereClause, '', 'sorting');
            if ($res) {
                foreach ($res as $row) {
                    $contentUids[] = $row['uid'];
                }
            }
        }
        return $contentUids;
    }
}
