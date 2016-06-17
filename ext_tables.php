<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

/** @var string $_EXTKEY */
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'AUS.' . $_EXTKEY,
    'OneLevelNavigation',
    'Simple one-level navigation'
);
$pluginSignature = 'auspage_onelevelnavigation';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'select_key,pages,recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature,
    'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForm/OneLevelNavigationSettings.xml');
