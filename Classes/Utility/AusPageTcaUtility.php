<?php

namespace AUS\AusPage\Utility;

use TYPO3\CMS\Core\Utility\ArrayUtility;

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
    public static function input($config)
    {
        $tca = [
            'label' => 'Headline',
            'exclude' => 0,
            'config' => [
                'type' => 'input',
                'cols' => 40,
                'eval' => 'trim'
            ],
        ];
        ArrayUtility::mergeRecursiveWithOverrule($tca, $config);
        return $tca;
    }

    public static function slider(int $lower, int $upper, int $step)
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
                    'width' => 200,
                ],
            ],
        ];
        return $tca;
    }

    public static function text($config)
    {
        $tca = [
            'label' => 'Text',
            'exclude' => 0,
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 8,
                'eval' => 'trim'
            ],
        ];
        ArrayUtility::mergeRecursiveWithOverrule($tca, $config);
        return $tca;
    }

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

    public static function date($config)
    {
        $tca = [
            'label' => 'Date',
            'exclude' => 0,
            'config' => [
                'type' => 'input',
                'size' => 5,
                'eval' => 'date',
            ]
        ];
        ArrayUtility::mergeRecursiveWithOverrule($tca, $config);
        return $tca;
    }

    public static function colorPicker($config)
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
                    ]
                ]
            ]
        ];
        ArrayUtility::mergeRecursiveWithOverrule($tca, $config);
        return $tca;
    }

    public static function headerImage($config)
    {
        $tca = [
            'label' => 'Header Image',
            'exclude' => 0,
            'excludeFromLanguageOverlay' => true,
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'header_image',
                [
                    'maxitems' => 1,
                    'overrideChildTca' => [
                        'types' => array(
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                                'showitem' => '--palette--;LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,--palette--;;filePalette',
                            ],
                        ),
                    ],
                ],
                $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
            )
        ];
        ArrayUtility::mergeRecursiveWithOverrule($tca, $config);
        return $tca;
    }

    public static function teaserImage($config)
    {
        $tca = [
            'exclude' => 0,
            'label' => 'Teaser Image',
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'teaser_image',
                [
                    'maxitems' => 1,
                    'overrideChildTca' => [
                        'types' => array(
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                                'showitem' => '--palette--;LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,--palette--;;filePalette',
                            ],
                        ),
                    ],
                ],
                $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
            ),
        ];
        ArrayUtility::mergeRecursiveWithOverrule($tca, $config);
        return $tca;
    }
}
