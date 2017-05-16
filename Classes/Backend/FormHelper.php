<?php

namespace AUS\AusPage\Backend;

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

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Service\TypoScriptService;

/**
 * Class FormHelper
 *
 * @author Markus Hölzle <m.hoelzle@andersundsehr.com>
 * @package AUS\AusPage\Backend
 */
class FormHelper implements SingletonInterface
{

    /**
     * @param array $config
     * @return array
     */
    public function addDokTypeOptions($config)
    {
        $config['items'] = array_merge($config['items'], $GLOBALS['TCA']['pages']['columns']['doktype']['config']['items']);
        return $config;
    }

    /**
     * @param array $config
     * @return array
     */
    public function addTemplateOptions($config)
    {
        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager */
        $configurationManager = $objectManager->get(ConfigurationManagerInterface::class);
        $fullTypoScript = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        /** @var \TYPO3\CMS\Extbase\Service\TypoScriptService $typoScriptService */
        $typoScriptService = $objectManager->get(TypoScriptService::class);
        $templates = [];
        if (is_array($fullTypoScript['plugin.']['tx_auspage.']['settings.']['templates.'])) {
            $templates = $fullTypoScript['plugin.']['tx_auspage.']['settings.']['templates.'];
        }

        $templates = $typoScriptService->convertTypoScriptArrayToPlainArray($templates);
        foreach ($templates as $key => $template) {
            $config['items'][] = [$template['title'], $key];
        }
        return $config;
    }

}
