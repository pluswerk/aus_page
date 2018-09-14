[![Packagist Release](https://img.shields.io/packagist/v/pluswerk/aus-page.svg?style=flat-square)](https://packagist.org/packages/pluswerk/aus-page)
[![Travis](https://img.shields.io/travis/pluswerk/aus_page.svg?style=flat-square)](https://travis-ci.org/pluswerk/aus_page)
[![GitHub License](https://img.shields.io/github/license/pluswerk/aus_page.svg?style=flat-square)](https://github.com/pluswerk/aus_page/blob/master/LICENSE.txt)
[![Code Climate](https://img.shields.io/codeclimate/github/pluswerk/aus_page.svg?style=flat-square)](https://codeclimate.com/github/pluswerk/aus_page)
[![Build Status](https://travis-ci.org/pluswerk/aus_page.svg?branch=master)](https://travis-ci.org/pluswerk/aus_page)

# +Pluswerk TYPO3 extension: aus_page

## Installation

#### ext_localconf.php
Put this in your `my_extension/ext_localconf.php`:
```php
<?php
\AUS\AusPage\Configuration\PageConfiguration::load($_EXTKEY, 'ext_localconf.php');
```

#### TCA Overrides
Put this in your `my_extension/Configuration/TCA/Overrides/AusPage.php`:
```php
<?php
defined('TYPO3_MODE') || die('Access denied.');
call_user_func(function () {
    \AUS\AusPage\Configuration\PageConfiguration::load('my_extension_key', 'TCA/Overrides');
});
```

#### ext_tables.php
Put this in your `my_extension/ext_tables.php`:
```php
<?php
\AUS\AusPage\Configuration\PageConfiguration::load($_EXTKEY, 'ext_tables.php');
```

## Page Type Configuration

Put your configuration in your extension in `Configuration/AusPage/Configuration.php`:

**Update:** Some general fields can be used now without long TCA entries:
        input, slider (params: lower end, upper end, steps), text, rte, date, colorPicker, headerImage, teaserImage
        <br> Difference between headerImage and teaserImage are the field names.

**Note:** Your new Page Type will get all the fields from the Default page. But only until your Extension is loaded.
       <br> for example: So If you wont to get realurl page fields in your new Page Type you must add `realurl` to the depends key in your `my_extension/ext_emconf.php`

```php
<?php
\AUS\AusPage\Configuration\PageConfiguration::addPageType([
    'dokType' => 125, // (required, unique, int, > 10, < 200)
    'identifier' => 'news', // (required)
    'modelClassName' => \AUS\MyExtension\Domain\Model\MyModel::class, // create TypoScript mapping (is needed if you add Properties that will be used in FE ++Repository(with doktype) is needed too)
    'title' => 'LLL:EXT:my_extension/Resources/Private/Language/locallang_db.xlf:doktype.news',
    'icon' => 'EXT:my_extension/ext_icon.svg', // SVG, PNG, Font Awesome ('file')
    'additionalTabs' => [ // Map fields to tabs (optional, if not set, a default tab for this dokType will be created)
        'LLL:EXT:my_extension/Resources/Private/Language/locallang_db.xlf:doktype.news.tab.foo' => ['my_special_field1', 'my_special_field2'],
        'LLL:EXT:my_extension/Resources/Private/Language/locallang_db.xlf:doktype.news.tab.bar' => ['my_special_field3', 'property_from_other_dok_type'],
    ],
    'additionalProperties' => [ // Add new database fields (optional)
        'my_text' => \AUS\AusPage\Utility\AusPageTcaUtility::text([
                            'label' => 'LLL:EXT:my_extension/Resources/Private/Language/locallang_db.xlf:pages.text',
                        ]),
        'my_input' => \AUS\AusPage\Utility\AusPageTcaUtility::input([
                    'label' => 'LLL:EXT:my_extension/Resources/Private/Language/locallang_db.xlf:pages.input',
                ]),
        'my_colorpicker' => \AUS\AusPage\Utility\AusPageTcaUtility::colorPicker([
                    'label' => 'LLL:EXT:my_extension/Resources/Private/Language/locallang_db.xlf:pages.colorpicker',
                ]),
        'my_slider' => \AUS\AusPage\Utility\AusPageTcaUtility::slider([
                    'label' => 'LLL:EXT:my_extension/Resources/Private/Language/locallang_db.xlf:pages.slider',
                ], -20, 20, 1),
        'my_image' => \AUS\AusPage\Utility\AusPageTcaUtility::image([
                            'label' => 'LLL:EXT:my_extension/Resources/Private/Language/locallang_db.xlf:pages.image',
                        ], 'my_image'),
        'my_select' => \AUS\AusPage\Utility\AusPageTcaUtility::select(
            [
                'label' => 'LLL:EXT:my_extension/Resources/Private/Language/locallang_db.xlf:pages.image',
            ],
            [
                ['Item 1', 0],
                ['Item 2', 1],
            ]),
            // Default TCA does also work:
            'my_special_field1' => [
                        'label' => 'LLL:EXT:my_extension/Resources/Private/Language/locallang_db.xlf:news.my_special_field1',
                        'config' => [
                            'type' => 'input',
                        ],
                    ],
                    'my_special_field2' => [
                        'label' => 'LLL:EXT:my_extension/Resources/Private/Language/locallang_db.xlf:news.my_special_field2',
                        'config' => [
                            'type' => 'text',
                            'size' => 10,
                            'eval' => 'int',
                        ],
                    ],
                    'my_special_field3' => [
                        'label' => 'Bla!',
                        'excludeFromLanguageOverlay' => true, // special field from aus_page
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

### Localization

All `additionalProperties` are added to the table `pages_language_overlay` as well.
If you want to exclude your property from the language overlay just set `'excludeFromLanguageOverlay' => true`.

**Note** This extension is not yet compatible with TYPO3 9.


## Plugin Template Configuration

Add a plugin of type "Navigation (flat)" to your page and select a "Template".
The templates will be defined via TypoScript:

```typo3_typoscript
plugin.tx_auspage.settings.templates.myOwnTemplate {
  title = Blog
  view {
    templateRootPaths.100 = EXT:my_extension/Resources/Private/Templates/
    partialRootPaths.100 = EXT:my_extension/Resources/Private/Partials/
    layoutRootPaths.100 = EXT:my_extension/Resources/Private/Layouts/
  }
  settings {
    # Show only sub pages of this page
    startPage = 42
    # Limit result to pages with this type
    dokType = 125

    pageFilter {
      # All properties from model \AUS\AusPage\Domain\Model\PageFilter are allowed here
      # Hint: This fields can be overwritten via GET / POST parameters

      # Limit result count
      limit = 2
      # Set first result position
      #offset = 3
      # The number of page levels to descend. If you want to descend infinitely, just set this to 100 or so. Should be at least "1" since zero will just make the function return (no decent...)
      #pageTreeDepth = 99
      # Is an integer that determines at which page level in the tree to start collecting uid's. Zero means 'start right away', 1 = 'next level and out'
      #pageTreeBegin = 0
      # Sort Recursive (default: 0)
      sortRecursive = 0

      # Limit result to a single year
      #fields.your_specified_field.year = 2016
      # Limit result to a minimum date
      #fields.your_specified_field.dateMinimal = -30days
      #fields.your_specified_field.dateMinimal = 2017-11-28
      # Limit result to a maximum date
      #fields.your_specified_field.dateMaximal = +30days
      #fields.your_specified_field.dateMaximal = 2018-01-27
      # Limit result to pages with this category
      #fields.page_categories = 3
      # Limit result to pages with this your_specified_field (mm relation possible)
      #fields.your_specified_field = 3

      # !!!DEPRECATED Limit result to pages with this category
      #pageCategoryUid = 3 # replace this with the above
    }

    # Additional settings are available in Fluid
    #showAllPageUid = 42
  }
}
```

### Using Filters

```xml
<form method="GET" action="">
  <f:form.select name="tx_auspage_onelevelnavigation[filter][fields][company]" options="{companies}"
                 prependOptionValue=""
                 prependOptionLabel="{f:translate(key: 'all_companies', extensionName: 'my_extension')}"
                 value="{currentFilterParams.fields.company}"
                 class="input input__select js-news__input"/>
  <f:form.select name="tx_auspage_onelevelnavigation[filter][fields][page_categories]" options="{categories}"
                 optionValueField="uid" optionLabelField="title"
                 prependOptionValue=""
                 prependOptionLabel="{f:translate(key: 'all_categories', extensionName: 'my_extension')}"
                 value="{currentFilterParams.fields.page_categories}"
                 class="input input__select js-news__input"/>
  <f:form.select name="tx_auspage_onelevelnavigation[filter][fields][date][year]" options="{years}"
                 prependOptionValue=""
                 prependOptionLabel="{f:translate(key: 'all_years', extensionName: 'my_extension')}"
                 value="{currentFilterParams.fields.date.year}"
                 class="input input__select js-news__input"/>

  <f:form.textfield name="tx_auspage_onelevelnavigation[filter][fields][date][from]"
                    value="{currentFilterParams.fields.date.from}"
                    class="input input__input"/>
  <f:form.textfield name="tx_auspage_onelevelnavigation[filter][fields][date][to]"
                    value="{currentFilterParams.fields.date.to}"
                    class="input input__input"/>
</form>
```


### Using the additional properties

To use all your page properties in a Fluid template in an aus_page plugin you have to create an extbase Model class
which extends `\AUS\AusPage\Domain\Model\AbstractPage` and add all your properties and the required getter functions.

Remember to set the right `modelClassName` in your `Configuration.php`!

Example:
```php
<?php
namespace AUS\MyExtension\Domain\Model;

use AUS\AusPage\Domain\Model\AbstractPage;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;

class Product extends AbstractPage
{
    /**
     * @var \TYPO3\CMS\Extbase\Domain\Model\FileReference
     */
    protected $headerImage = null;

    /**
     * @var string
     */
    protected $description = '';

    public function getHeaderImage(): FileReference
    {
        return $this->headerImage;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
```


## Using pages as extbase Model

If you want to use pages in your extension as an extbase Model object you have to create a Model class
which extends `\AUS\AusPage\Domain\Model\AbstractPage` (see above) and a repository class
which extends `\AUS\AusPage\Domain\Repository\AbstractPageRepository`.

The repository must have set the variable `$dokType`.

Example:
```php
<?php
namespace AUS\MyExtension\Domain\Repository;

use AUS\AusPage\Domain\Repository\AbstractPageRepository;

class ProductRepository extends AbstractPageRepository
{
    /**
     * @var int
     */
    protected $dokType = 125;
}
```
