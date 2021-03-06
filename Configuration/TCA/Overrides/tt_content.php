<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(function ($_EXTKEY) {
    // Plugin for selected page navigation
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'AUS.' . $_EXTKEY,
        'OneLevelSelectedNavigation',
        'LLL:EXT:aus_page/Resources/Private/Language/locallang_db.xlf:plugin.OneLevelSelectedNavigation'
    );
    $pluginSignature = 'auspage_onelevelselectednavigation';
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'select_key,pages,recursive';
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
        $pluginSignature,
        'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForm/OneLevelSelectedNavigationSettings.xml'
    );

    // Plugin for page navigation
    foreach (['OneLevelNavigation', 'OneLevelNavigationNonCached'] as $pluginName) {
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'AUS.' . $_EXTKEY,
            $pluginName,
            'LLL:EXT:aus_page/Resources/Private/Language/locallang_db.xlf:plugin.' . $pluginName
        );
        $pluginSignature = 'auspage_' . strtolower($pluginName);
        $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'select_key,pages,recursive';
        $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
            $pluginSignature,
            'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForm/OneLevelNavigationSettings.xml'
        );
    }


    // Plugin for category navigation
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'AUS.' . $_EXTKEY,
        'OneLevelCategoryNavigation',
        'LLL:EXT:aus_page/Resources/Private/Language/locallang_db.xlf:plugin.OneLevelCategoryNavigation'
    );
    $pluginSignature = 'auspage_onelevelcategorynavigation';
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'select_key,pages,recursive';
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
        $pluginSignature,
        'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForm/OneLevelCategoryNavigationSettings.xml'
    );
}, 'aus_page');
