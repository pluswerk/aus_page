Example configuration
---------------------

Configuration in your `aus_project/ext_localconf.php` (not `ext_tables.php`!):
```php
<?php
\AUS\AusPage\Configuration\PageConfiguration::addPageType([
    'dokType' => 125, // (required, unique)
    'identifier' => 'news', // (required)
    'modelClassName' => \AUS\MyExtension\Domain\Model\MyModel::class, // create TypoScript mapping (optional)
    'title' => 'LLL:EXT:aus_project/Resources/Private/Language/locallang_db.xlf:doktype.news',
    'icon' => 'EXT:aus_project/ext_icon.svg', // SVG, PNG, fontawsome
    'additionalProperties' => [ // Add new database fields (optional)
        'my_special_field1' => [
            'label' => 'LLL:EXT:aus_project/Resources/Private/Language/locallang_db.xlf:news.my_special_field1',
            'config' => [
                'type' => 'input',
            ],
        ],
        'my_special_field2' => [
            'label' => 'LLL:EXT:aus_project/Resources/Private/Language/locallang_db.xlf:news.my_special_field2',
            'config' => [
                'type' => 'text',
                'size' => 10,
                'eval' => 'int',
            ],
        ],
        'my_special_field3' => [
            'label' => 'Bla!',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'size' => 10,
            ],
        ],
    ],
    'showAsAdditionalProperty' => 'property_from_other_dok_type,something_else', // show existing database fields for this dokType
]);
```
