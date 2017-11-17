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

class MappedPageViewHelper extends AbstractViewHelper
{

    /**
     * Get field form mount page if found overwriting the default
     *
     * @param AbstractPage $page
     * @param array $mappedPages
     * @param string $field
     * @param mixed $default
     *
     * @return mixed
     */
    public function render($page, $mappedPages, $field, $default)
    {
        if ($mappedPages[$page->getUid()]['mappedPage']) {
            return $mappedPages[$page->getUid()]['mappedPage'][$field];
        } else {
            return $default;
        }
    }
}
