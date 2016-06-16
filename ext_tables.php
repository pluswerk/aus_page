<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\AUS\AusPage\Configuration\PageConfiguration::addPageType([
    'dokType' => 125, // (required) check if already exists
    'identifier' => 'news', // (required)
    'modelClass' => '\AUS\Model\News', // create TypoScript (if class exists)
    'title' => 'LLL:EXT:extension/bla.xlf:label', //falback = identifier
    'icon' => 'EXT:dfdsaf/sdaff/icon.svg', //falback aus icon
    'additionalProperties' => [
        'meinSpezialFeld1' => [
            'label' => 'LLL:EXT:extension/bla.xlf:label',
            'config' => [
                'type' => 'input',
            ],
        ],
        'meinSpezialFeld2' => [
            'label' => 'LLL:EXT:extension/bla.xlf:label',
            'config' => [
                'type' => 'text',
                'size' => 10,
            ],
        ],
        'meinSpezialFeld3' => [
            'label' => 'LLL:EXT:extension/bla.xlf:label',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'size' => 10,
            ],
        ],
    ],
]);
