<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

/** @var string $_EXTKEY */
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

// Add page categories
\AUS\AusPage\Configuration\PageConfiguration::addPageType([
    'dokType' => 100,
    'identifier' => 'pagecategory',
    'title' => 'LLL:EXT:aus_page/Resources/Private/Language/locallang_db.xlf:page_category',
    'icon' => 'EXT:aus_page/Resources/Pubic/Icons/PageCategory.svg',
    'additionalProperties' => [
        'category_dok_type' => [
            'label' => 'LLL:EXT:aus_page/Resources/Private/Language/locallang_db.xlf:page_category.category_dok_type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'itemsProcFunc' => \AUS\AusPage\Backend\FormHelper::class . '->addDokTypeOptions',
                'minitems' => 1,
                'maxitems' => 1,
            ],
        ],
    ],
]);

/** @var \AUS\AusPage\Page\PagePropertyService $pagePropertyService */
$pagePropertyService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\AUS\AusPage\Page\PagePropertyService::class);
$pagePropertyService->addFieldToDatabase('page_categories');
$pagePropertyService->addTcaColumns([
    'page_categories' => [
        'label' => 'LLL:EXT:aus_page/Resources/Private/Language/locallang_db.xlf:pages.page_categories',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'foreign_table' => 'pages',
            'foreign_table_where' => ' AND pages.category_dok_type = ###REC_FIELD_doktype###',
            'MM' => 'tx_auspage_page_pagecategory_mm',
            'enableMultiSelectFilterTextfield' => true,
            'size' => 7,
            'minitems' => 0,
            'maxitems' => 9999,
        ],
    ],
], [
    'page_categories' => [
        'config' => [
            'MM' => 'tx_auspage_pagelanguageoverlay_pagecategory_mm',
        ],
    ],
]);
