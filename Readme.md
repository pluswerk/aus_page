# aus_page

## Installation

Put this in your `aus_project/ext_localconf.php`:
```php
<?php
\AUS\AusPage\Configuration\PageConfiguration::load($_EXTKEY, 'ext_localconf.php');
```


Put this in your `aus_project/ext_tables.php`:
```php
<?php
\AUS\AusPage\Configuration\PageConfiguration::load($_EXTKEY, 'ext_tables.php');
```

## Page Type Configuration

Put your configuration in your extension in `aus_project/Configuration/AusPage/Configuration.php`:

Bug: Font Awesome can not be displayed in the page edit. (TYPO3 CMS 7.6.9)

```php
<?php
\AUS\AusPage\Configuration\PageConfiguration::addPageType([
    'dokType' => 125, // (required, unique)
    'identifier' => 'news', // (required)
    'modelClassName' => \AUS\MyExtension\Domain\Model\MyModel::class, // create TypoScript mapping (optional)
    'title' => 'LLL:EXT:aus_project/Resources/Private/Language/locallang_db.xlf:doktype.news',
    'icon' => 'EXT:aus_project/ext_icon.svg', // SVG, PNG, Font Awesome ('file')
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

## Plugin Template Configuration

Add a plugin of type "Navigation (flat)" to your page and select a "Template".
The templates will be defined via TypoScript:

```
plugin.tx_auspage.settings.templates.myOwnTemplate {
  title = Blog
  view {
    templateRootPaths.100 = EXT:aus_page/Resources/Private/Templates/
    partialRootPaths.100 = EXT:aus_page/Resources/Private/Partials/
    layoutRootPaths.100 = EXT:aus_page/Resources/Private/Layouts/
  }
  settings {
    pageFilter {
      limit = 2
    }
  }
}
```
