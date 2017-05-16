<?php

namespace AUS\AusPage\ViewHelpers;

/***
 * This file is part of an "anders und sehr GmbH" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2017 Markus Hölzle <m.hoelzle@andersundsehr.com>, anders und sehr GmbH
 ***/

use AUS\AusPage\Service\RepositoryService;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class GetPageViewHelper
 *
 * @author Markus Hölzle <m.hoelzle@andersundsehr.com>
 * @package AUS\AusPage\ViewHelpers
 */
class GetPageViewHelper extends AbstractViewHelper
{
    /**
     * @var \AUS\AusPage\Domain\Model\AbstractPage[]
     */
    protected static $firstLevelCache = [];

    /**
     * @return \AUS\AusPage\Domain\Model\AbstractPage
     */
    public function render()
    {
        $tsfe = $this->getTypoScriptFrontendController();
        $pageUid = (int)$tsfe->id;
        if (!isset(static::$firstLevelCache[$pageUid])) {
            /** @var RepositoryService $repositoryService */
            $repositoryService = $this->objectManager->get(RepositoryService::class);
            $repository = $repositoryService->getPageRepositoryForDokType((int)$tsfe->page['doktype']);
            static::$firstLevelCache[$pageUid] = $repository->findByUid($pageUid);
        }
        return static::$firstLevelCache[$pageUid];
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }
}
