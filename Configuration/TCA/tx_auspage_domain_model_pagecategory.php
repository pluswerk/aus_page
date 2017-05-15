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
        'iconfile' => 'EXT:aus_page/Resources/Pubic/Icons/PageCategory.svg'
    ),
    'interface' => array(
        'showRecordFieldList' => 'hidden,title,dok_type',
    ),
    'types' => array(
        '1' => array('showitem' => 'title,dok_type,--div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access,hidden'),
    ),
    'palettes' => array(
        '1' => array('showitem' => ''),
    ),
    'columns' => array(
        'sys_language_uid' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'sys_language',
                'foreign_table_where' => 'ORDER BY sys_language.title',
                'items' => [
                    ['LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages', -1],
                    ['LLL:EXT:lang/locallang_general.xlf:LGL.default_value', 0]
                ],
                'default' => 0,
                'showIconTable' => true, // Legacy support for TYPO3 version <= 7.6
                'fieldWizard' => [
                    'selectIcons' => [
                        'disabled' => false,
                    ],
                ],
            ],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => 'tx_auspage_domain_model_pagecategory',
                'foreign_table_where' => 'AND tx_auspage_domain_model_pagecategory.pid=###CURRENT_PID### AND tx_auspage_domain_model_pagecategory.sys_language_uid IN (-1,0)',
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'hidden' => [
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
            ],
        ],

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