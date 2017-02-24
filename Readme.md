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

Put your configuration in your extension in `Configuration/AusPage/Configuration.php`:

Bug: Font Awesome can not be displayed in the page edit. (TYPO3 CMS 7.6.9)

```php
<?php
\AUS\AusPage\Configuration\PageConfiguration::addPageType([
    'dokType' => 125, // (required, unique, int, > 10, < 200)
    'identifier' => 'news', // (required)
    'modelClassName' => \AUS\MyExtension\Domain\Model\MyModel::class, // create TypoScript mapping (is needed if you add Properties that will be used in FE ++Repository(with doktype) is needed too)
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
      # Limit result to pages with this category
      #pageCategoryUid = 3
      # Limit result to a single year
      #fields.myPagesDateField.year = 2016
    }

    # Additional settings are available in Fluid
    #showAllPageUid = 42
  }
}
```

### Using the additional properties

To use all your page properties in a Fluid template in an aus_page plugin you have to create an Extbase Model class
which extends `\AUS\AusPage\Domain\Model\AbstractPage` and add all your properties and the required getter functions.

Remember to set the right `modelClassName` in your `Configuration.php`!

Example:
```php
<?php
namespace AUS\AusProject\Domain\Model;

/***
 * This file is part of an "anders und sehr" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2017 Your Name <y.name@andersundsehr.com>, anders und sehr GmbH
 ***/

use AUS\AusPage\Domain\Model\AbstractPage;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;

/**
 * Class Product
 *
 * @package AUS\AusProject\Domain\Model
 */
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

    /**
     * @return FileReference
     */
    public function getHeaderImage(): FileReference
    {
        return $this->headerImage;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }
}
```


## Using pages as Extbase Model

If you want to use pages in your extension as an Extbase Model object you have to create a Model class
which extends `\AUS\AusPage\Domain\Model\AbstractPage` (see above) and a repository class
which extends `\AUS\AusPage\Domain\Repository\AbstractPageRepository`.

The repository must have set the variable `$dokType`.

Example:
```php
<?php
namespace AUS\AusProject\Domain\Repository;

/***
 * This file is part of an "anders und sehr" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2017 Your Name <y.name@andersundsehr.com>, anders und sehr GmbH
 ***/

use AUS\AusPage\Domain\Repository\AbstractPageRepository;

/**
 * Class ProductRepository
 *
 * @package AUS\AusProject\Domain\Repository
 */
class ProductRepository extends AbstractPageRepository
{
    /**
     * @var int
     */
    protected $dokType = 125;
}
```
