<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

/** @var string $_EXTKEY */
// Plugin for page navigation
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'AUS.' . $_EXTKEY,
    'OneLevelNavigation',
    'LLL:EXT:aus_page/Resources/Private/Language/locallang_db.xlf:plugin.OneLevelNavigation'
);
$pluginSignature = 'auspage_onelevelnavigation';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'select_key,pages,recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature,
    'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForm/OneLevelNavigationSettings.xml');


// Plugin for category navigation
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'AUS.' . $_EXTKEY,
    'OneLevelCategoryNavigation',
    'LLL:EXT:aus_page/Resources/Private/Language/locallang_db.xlf:plugin.OneLevelCategoryNavigation'
);
$pluginSignature = 'auspage_onelevelcategorynavigation';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'select_key,pages,recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature,
    'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForm/OneLevelCategoryNavigationSettings.xml');


// Add field page_categories
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', [
    'page_categories' => [
        'label' => 'LLL:EXT:aus_page/Resources/Private/Language/locallang_db.xlf:pages.page_categories',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'foreign_table' => 'tx_auspage_domain_model_pagecategory',
            'foreign_table_where' => ' AND tx_auspage_domain_model_pagecategory.dok_type = ###REC_FIELD_doktype###',
            'MM' => 'tx_auspage_page_pagecategory_mm',
            'enableMultiSelectFilterTextfield' => true,
            'size' => 7,
            'minitems' => 0,
            'maxitems' => 9999,
        ],
    ],
]);
