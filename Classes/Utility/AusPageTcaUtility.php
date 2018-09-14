<?php

namespace AUS\AusPage\Utility;

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Class AusPageTcaUtility
 *
 * @author Felix König <f.koenig@andersundsehr.com>
 * @copyright 2017 anders und sehr GmbH
 * @license GPL, version 2
 * @package AUS\AusPage\Utility
 */
class AusPageTcaUtility
{
    /**
     * @param array $config
     * @return array
     */
    public static function input(array $config)
    {
        $tca = [
            'label' => 'Headline',
            'exclude' => 0,
            'config' => [
                'type' => 'input',
                'cols' => 40,
                'eval' => 'trim',
            ],
        ];
        ArrayUtility::mergeRecursiveWithOverrule($tca, $config);
        return $tca;
    }

    /**
     * @param array $config
     * @param int $lower
     * @param int $upper
     * @param int $step
     * @return array
     */
    public static function slider(array $config, int $lower, int $upper, int $step)
    {
        $tca = [
            'label' => 'Slider',
            'config' => [
                'type' => 'input',
                'size' => 5,
                'eval' => 'trim,int',
                'range' => [
                    'lower' => $lower,
                    'upper' => $upper,
                ],
                'default' => 0,
                'slider' => [
                    'step' => $step,
                    'width' => 400,
                ],
            ],
        ];
        ArrayUtility::mergeRecursiveWithOverrule($tca, $config);
        return $tca;
    }

    /**
     * @param array $config
     * @return array
     */
    public static function text(array $config)
    {
        $tca = [
            'label' => 'Text',
            'exclude' => 0,
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 8,
                'eval' => 'trim',
            ],
        ];
        ArrayUtility::mergeRecursiveWithOverrule($tca, $config);
        return $tca;
    }

    /**
     * @param $config
     * @return array
     */
    public static function rte($config)
    {
        $tca = [
            'label' => 'RTE',
            'exclude' => 0,
            'config' => [
                'type' => 'text',
                'enableRichtext' => true,
            ],
        ];
        ArrayUtility::mergeRecursiveWithOverrule($tca, $config);
        return $tca;
    }

    /**
     * @param array $config
     * @return array
     */
    public static function date(array $config)
    {
        $tca = [
            'label' => 'Date',
            'exclude' => 0,
            'config' => [
                'type' => 'input',
                'size' => 5,
                'eval' => 'date',
            ],
        ];
        ArrayUtility::mergeRecursiveWithOverrule($tca, $config);
        return $tca;
    }

    /**
     * @param array $config
     * @return array
     */
    public static function colorPicker(array $config)
    {
        $tca = [
            'exclude' => 0,
            'label' => 'Colorpicker',
            'config' => [
                'type' => 'input',
                'size' => 10,
                'eval' => 'trim',
                'wizards' => [
                    'colorChoice' => [
                        'type' => 'colorbox',
                        'label' => 'Colorpicker',
                        'module' => [
                            'name' => 'wizard_colorpicker',
                        ],
                        'dim' => '20x20',
                        'tableStyle' => 'border: solid 1px black; margin-left: 20px;',
                        'JSopenParams' => 'height=600,width=380,status=0,menubar=0,scrollbars=1',
                    ],
                ],
            ],
        ];
        ArrayUtility::mergeRecursiveWithOverrule($tca, $config);
        return $tca;
    }

    /**
     * @param array $config
     * @param string $fieldName
     * @return array
     */
    public static function image(array $config, string $fieldName)
    {
        $tca = [
            'label' => 'Header Image',
            'exclude' => 0,
            'excludeFromLanguageOverlay' => true,
            'config' => ExtensionManagementUtility::getFileFieldTCAConfig(
                $fieldName,
                [
                    'maxitems' => 1,
                    'overrideChildTca' => [
                        'types' => [
                            File::FILETYPE_IMAGE => [
                                'showitem' => '--palette--;LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,--palette--;;filePalette',
                            ],
                        ],
                    ],
                ],
                $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
            ),
        ];
        ArrayUtility::mergeRecursiveWithOverrule($tca, $config);
        return $tca;
    }

    /**
     * @param array $config
     * @param array $fields
     * @return array
     */
    public static function select(array $config, array $fields)
    {
        $tca = [
            'label' => 'Select',
            'exclude' => 1,
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => $fields,
            ],
        ];
        ArrayUtility::mergeRecursiveWithOverrule($tca, $config);
        return $tca;
    }
}
