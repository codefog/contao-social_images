<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;

// Palettes
if (is_array($GLOBALS['TL_DCA']['tl_calendar_events']['palettes'] ?? null)) {
    foreach ($GLOBALS['TL_DCA']['tl_calendar_events']['palettes'] as $k => $v) {
        if (is_array($v)) {
			continue;
		}

		PaletteManipulator::create()
			->addLegend('socialimages_legend', 'expert_legend', PaletteManipulator::POSITION_BEFORE, true)
			->addField('socialImage', 'socialimages_legend', PaletteManipulator::POSITION_APPEND)
			->applyToPalette($k, 'tl_calendar_events')
		;
	}
}

// Fields
if (is_array($GLOBALS['TL_DCA']['tl_calendar_events']['fields'] ?? null)) {
    $GLOBALS['TL_DCA']['tl_calendar_events']['fields']['socialImage'] = [
        'exclude' => true,
        'inputType' => 'fileTree',
        'eval' => ['files' => true, 'filesOnly' => true, 'fieldType' => 'radio', 'extensions' => \Contao\Config::get('validImageTypes'), 'tl_class' => 'clr'],
        'sql' => ['type' => 'binary', 'length' => 16, 'notnull' => false],
	];
}
