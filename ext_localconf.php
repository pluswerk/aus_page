<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

/** @var string $_EXTKEY */
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'AUS.' . $_EXTKEY,
    'OneLevelNavigation',
    [
        'Navigation' => 'oneLevelNavigation',
    ],
    []
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'AUS.' . $_EXTKEY,
    'OneLevelCategoryNavigation',
    [
        'Navigation' => 'oneLevelCategoryNavigation',
    ],
    []
);


/** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
$signalSlotDispatcher->connect(
    \TYPO3\CMS\Install\Service\SqlExpectedSchemaService::class,
    'tablesDefinitionIsBeingBuilt',
    \AUS\AusPage\Database\DatabaseSchemaService::class,
    'addAusPageRequiredDatabaseSchemaForSqlExpectedSchemaService'
);
$signalSlotDispatcher->connect(
    \TYPO3\CMS\Extensionmanager\Utility\InstallUtility::class,
    'tablesDefinitionIsBeingBuilt',
    \AUS\AusPage\Database\DatabaseSchemaService::class,
    'addAusPageRequiredDatabaseSchemaForInstallUtility'
);
