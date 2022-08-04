<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;

// Palettes
foreach ($GLOBALS['TL_DCA']['tl_page']['palettes'] as $k => $v) {
    if (is_array($v)) {
        continue;
    }

    PaletteManipulator::create()
        ->addLegend('socialimages_legend', 'layout_legend', PaletteManipulator::POSITION_AFTER, true)
        ->addField('socialImage', 'socialimages_legend', PaletteManipulator::POSITION_APPEND)
        ->applyToPalette($k, 'tl_page')
    ;
}


// Fields
$GLOBALS['TL_DCA']['tl_page']['fields']['socialImage'] = [
    'exclude' => true,
    'inputType' => 'fileTree',
    'eval' => ['files' => true, 'filesOnly' => true, 'fieldType' => 'radio', 'extensions' => \Contao\Config::get('validImageTypes'), 'tl_class' => 'clr'],
    'sql' => ['type' => 'binary', 'length' => 16, 'notnull' => false],
];
