<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;

// Palettes
PaletteManipulator::create()
    ->addLegend('socialImages_legend', 'image_legend', PaletteManipulator::POSITION_AFTER, true)
    ->addField(['socialImages', 'socialImages_limit', 'socialImages_size', 'socialImages_resize'], 'socialImages_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_layout')
;

// Fields
$GLOBALS['TL_DCA']['tl_layout']['fields']['socialImages'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'clr'],
    'sql' => "char(1) COLLATE ascii_bin NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_layout']['fields']['socialImages_limit'] = [
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50'],
    'sql' => ['type' => 'smallint', 'unsigned' => true, 'default' => 10],
];

$GLOBALS['TL_DCA']['tl_layout']['fields']['socialImages_size'] = [
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['multiple' => true, 'size' => 2, 'tl_class' => 'w50'],
    'sql' => ['type' => 'string', 'length' => 64, 'default' => serialize(['200', '200'])],
];

$GLOBALS['TL_DCA']['tl_layout']['fields']['socialImages_resize'] = [
    'exclude' => true,
    'inputType' => 'imageSize',
    'options_callback' => [\Codefog\SocialImagesBundle\EventListener\LayoutListener::class, 'onSocialImagesResizeOptionsCallback'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval' => ['rgxp' => 'natural', 'includeBlankOption' => true, 'nospace' => true, 'helpwizard' => true, 'tl_class' => 'w50'],
    'sql' => ['type' => 'string', 'length' => 64, 'default' => ''],
];
