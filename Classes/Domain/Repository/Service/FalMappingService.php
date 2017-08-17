<?php

namespace AUS\AusPage\Domain\Repository\Service;

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
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class FalMappingService
 *
 * @author Markus Hölzle <m.hoelzle@andersundsehr.com>
 * @package AUS\AusPage\Domain\Repository\Service
 */
class FalMappingService implements SingletonInterface
{
    /**
     * @var ObjectManager
     */
    protected $objectManager = null;

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @var null
     */
    protected $frameworkConfiguration = null;


    /**
     * initializeObject
     */
    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->configurationManager = $this->objectManager->get(ConfigurationManagerInterface::class);
    }

    /**
     * @param DomainObjectInterface $object
     * @return void
     */
    public function remapFalFields(DomainObjectInterface $object)
    {
        if ($object instanceof AbstractPage) {
            $table = 'pages';
            if ((int)$GLOBALS['TSFE']->sys_language_uid !== 0) {
                $table = 'pages_language_overlay';
            }

            foreach ($GLOBALS['TCA'][$table]['columns'] as $columnName => &$columnDefinition) {
                // filter all file reference fields
                if ($columnDefinition['config']['type'] === 'inline' && !empty($columnDefinition['config']['foreign_table_field'])) {
                    $this->setFalValueToObject($object, $table, $object->_getProperty('_localizedUid'), $columnName, $columnDefinition);
                }
            }

            // excludeFromLanguageOverlay: load file reference from "pages" as fallback
            if ($table === 'pages_language_overlay') {
                foreach ($GLOBALS['TCA']['pages']['columns'] as $columnName => &$columnDefinition) {
                    if ($columnDefinition['config']['type'] === 'inline' &&
                        !empty($columnDefinition['config']['foreign_table_field']) &&
                        isset($columnDefinition['excludeFromLanguageOverlay']) &&
                        $columnDefinition['excludeFromLanguageOverlay'] === true
                    ) {
                        $this->setFalValueToObject($object, 'pages', $object->_getProperty('uid'), $columnName, $columnDefinition);
                    }
                }
            }
        }
    }

    /**
     * @param AbstractPage $object
     * @param string $table
     * @param int $referenceParentUid
     * @param string $columnName
     * @param array $columnDefinition
     * @return void
     */
    protected function setFalValueToObject(AbstractPage $object, $table, $referenceParentUid, $columnName, $columnDefinition)
    {
        $className = get_class($object);
        $frameworkConfiguration = $this->getFrameworkConfiguration();
        $columnMapping = $frameworkConfiguration['persistence']['classes'][$className]['mapping']['columns'];
        if (isset($columnMapping[$columnName]['mapOnProperty'])) {
            $propertyName = $columnMapping[$columnName]['mapOnProperty'];
        } else {
            $propertyName = GeneralUtility::underscoredToLowerCamelCase($columnName);

            // If a property should be set by a mapping AND by a native value, we use the mapping because its more explicit defined.
            // This case does just appear by some crazy configuration stuff.
            // A better way would be to check if the property is really defined in de AusPage configuration, but this is not loaded at this point.
            if (is_array($columnMapping) && $this->isPropertyOverriddenByMapping($propertyName, $columnMapping)) {
                return;
            }
        }
        if ($object->_hasProperty($propertyName)) {
            if ($columnDefinition['config']['foreign_table'] === 'sys_file_reference') {
                $value = $this->getFalValue($table, $columnName, $columnDefinition, $referenceParentUid);
                $object->_setProperty($propertyName, $value);
            }
        }
    }

    /**
     * @param string $table
     * @param string $columnName
     * @param array $columnDefinition
     * @param int $relationUid
     * @return FileReference[]|FileReference|null
     */
    protected function getFalValue($table, $columnName, $columnDefinition, $relationUid)
    {
        /** @var FileRepository $fileRepository */
        $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
        $fileReferences = $fileRepository->findByRelation($table, $columnName, $relationUid);
        $value = [];
        foreach ($fileReferences as $fileReference) {
            /** @var FileReference $extbaseFileReference */
            $extbaseFileReference = $this->objectManager->get(FileReference::class);
            $extbaseFileReference->setOriginalResource($fileReference);
            $extbaseFileReference->_memorizeCleanState();
            $value[] = $extbaseFileReference;
        }
        if ((int)$columnDefinition['config']['maxitems'] === 1) {
            if (isset($value[0])) {
                $value = $value[0];
            } else {
                $value = null;
            }
        }
        return $value;
    }

    /**
     * @param string $propertyName
     * @param string[][] $columnMapping
     * @return bool
     */
    protected function isPropertyOverriddenByMapping($propertyName, $columnMapping) {
        foreach ($columnMapping as $column) {
            if ($column['mapOnProperty'] === $propertyName) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return array
     */
    protected function getFrameworkConfiguration()
    {
        if ($this->frameworkConfiguration === null) {
            $this->frameworkConfiguration = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        }
        return $this->frameworkConfiguration;
    }
}
