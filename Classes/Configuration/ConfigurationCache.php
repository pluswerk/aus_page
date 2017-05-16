<?php

namespace AUS\AusPage\Configuration;

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

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ConfigurationCache
 *
 * @author Markus Hölzle <m.hoelzle@andersundsehr.com>
 * @package AUS\AusPage\Configuration
 */
class ConfigurationCache implements SingletonInterface
{

    /**
     * ConfigurationCache constructor.
     */
    public function __construct()
    {
        $cacheIdentifier = $this->getCacheIdentifier();
        /** @var $codeCache \TYPO3\CMS\Core\Cache\Frontend\PhpFrontend */
        $codeCache = GeneralUtility::makeInstance(CacheManager::class)->getCache('cache_core');
        if ($codeCache->has($cacheIdentifier)) {
            $codeCache->requireOnce($cacheIdentifier);
        } else {
            $GLOBALS['aus_page_cache'] = [];
        }
    }

    /**
     * @return array
     */
    public function getCachedConfiguration()
    {
        return is_array($GLOBALS['aus_page_cache']) ? $GLOBALS['aus_page_cache'] : [];
    }

    /**
     * @param array $configuration
     * @return void
     */
    public function setCachedConfiguration(array $configuration)
    {
        $GLOBALS['aus_page_cache'] = $configuration;
        $cacheFileContent = '$GLOBALS[\'aus_page_cache\'] = ';
        $cacheFileContent .= var_export($GLOBALS['aus_page_cache'], true) . ';';
        GeneralUtility::makeInstance(CacheManager::class)->getCache('cache_core')->set($this->getCacheIdentifier(), $cacheFileContent);
    }

    /**
     * @return string
     */
    protected function getCacheIdentifier()
    {
        return 'aus_page_' . sha1(PATH_site . PageConfiguration::EXTENSION_KEY);
    }

}
