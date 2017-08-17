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

use AUS\AusPage\Page\PageConfigurationService;

/**
 * Class PageConfiguration
 *
 * @author Markus Hölzle <m.hoelzle@andersundsehr.com>
 * @package AUS\AusPage\Configuration
 */
class PageConfiguration
{

    const EXTENSION_KEY = 'aus_page';

    /**
     * @param string $extensionKey
     * @param string $fileName
     * @return void
     */
    public static function load($extensionKey, $fileName)
    {
        PageConfigurationService::getInstance()->load($extensionKey, $fileName);
    }

    /**
     * @param array $configuration
     * @return void
     */
    public static function addPageType(array $configuration)
    {
        PageConfigurationService::getInstance()->addPageType($configuration);
    }

    /**
     * @param int $dokType
     * @param array $configuration
     * @return void
     * @throws \Exception
     * @todo: Implement & define params
     */
    public static function addFieldToPage($dokType, array $configuration = [])
    {
        throw new \Exception('not implemented');
    }
}
