<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(function () {
// Add field page_categories
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', [
        'page_categories' => [
            'label' => 'LLL:EXT:aus_page/Resources/Private/Language/locallang_db.xlf:pages.page_categories',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_auspage_domain_model_pagecategory',
                'foreign_table_where' => ' AND tx_auspage_domain_model_pagecategory.dok_type = ###REC_FIELD_doktype### AND tx_auspage_domain_model_pagecategory.sys_language_uid = 0',
                'MM' => 'tx_auspage_page_pagecategory_mm',
                'enableMultiSelectFilterTextfield' => true,
                'size' => 7,
                'minitems' => 0,
                'maxitems' => 9999,
            ],
        ],
    ]);
});
