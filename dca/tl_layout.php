<?php

/**
 * social_images extension for Contao Open Source CMS
 *
 * Copyright (C) 2013 Codefog
 *
 * @package social_images
 * @link    http://codefog.pl
 * @author  Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 * @license LGPL
 */


/**
 * Extend a tl_layout palette
 */
$GLOBALS['TL_DCA']['tl_layout']['palettes']['default'] = str_replace('cssClass,', 'cssClass,socialImages,', $GLOBALS['TL_DCA']['tl_layout']['palettes']['default']);


/**
 * Add the field to tl_layout
 */
$GLOBALS['TL_DCA']['tl_layout']['fields']['socialImages'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['socialImages'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'                    => array('tl_class'=>'w50'),
	'sql'                     => "char(1) NOT NULL default ''"
);
