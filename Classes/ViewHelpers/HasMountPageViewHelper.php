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

class HasMountPageViewHelper extends AbstractViewHelper
{

    /**
     * Check if the current page hast mount page of the given doktype
     *
     * @param AbstractPage $page
     * @param int $doktype
     *
     * @return boolean
     */
    public function render($page, $doktype)
    {
        $whereClause = 'uid = ' . $GLOBALS['TSFE']->id . ' AND mount_pid = ' . $page->getUid() . ' AND mount_pid_ol = 0';
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'pages', $whereClause, '', '', 1);
        if ($res) {
            $pid = $res[0]['mount_pid'];
            $whereClause = 'uid = ' . $pid . ' AND doktype = '. $doktype . $GLOBALS['TSFE']->sys_page->enableFields('pages');
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'pages', $whereClause, '', '', 1);
            if ($res) {
                return true;
            }
        }
        return false;
    }
}
