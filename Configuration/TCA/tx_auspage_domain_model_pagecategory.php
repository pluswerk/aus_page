<?php
return array(
    'ctrl' => array(
        'title'	=> 'LLL:EXT:aus_page/Resources/Private/Language/locallang_db.xlf:tx_auspage_domain_model_pagecategory',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'dividers2tabs' => TRUE,
        'sortby' => 'sorting',
        'delete' => 'deleted',
        'enablecolumns' => array(
            'disabled' => 'hidden',
        ),
        'searchFields' => 'title,',
        'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('aus_page') . 'Resources/Pubic/Icons/PageCategory.svg'
    ),
    'interface' => array(
        'showRecordFieldList' => 'hidden,title,dok_type',
    ),
    'types' => array(
        '1' => array('showitem' => 'title,dok_type,--div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access,hidden;;1'),
    ),
    'palettes' => array(
        '1' => array('showitem' => ''),
    ),
    'columns' => array(
        'sys_language_uid' => \AUS\AusUtility\Configuration\TcaUtility::getDefaultFieldConfig('sys_language_uid'),
        'l10n_parent' => \AUS\AusUtility\Configuration\TcaUtility::getDefaultFieldConfig('l10n_parent', 'tx_auspage_domain_model_pagecategory'),
        'l10n_diffsource' => \AUS\AusUtility\Configuration\TcaUtility::getDefaultFieldConfig('l10n_diffsource'),
        'hidden' => \AUS\AusUtility\Configuration\TcaUtility::getDefaultFieldConfig('hidden'),

        'title' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:aus_page/Resources/Private/Language/locallang_db.xlf:tx_auspage_domain_model_pagecategory.title',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ),
        ),
        'dok_type' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:aus_page/Resources/Private/Language/locallang_db.xlf:tx_auspage_domain_model_pagecategory.dok_type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'itemsProcFunc' => \AUS\AusPage\Backend\FormHelper::class . '->addDokTypeOptions',
                'minitems' => 1,
                'maxitems' => 1,
            ],
        ),

    ),
);