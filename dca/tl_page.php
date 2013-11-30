<?php

/**
 * social_images extension for Contao Open Source CMS
 *
 * Copyright (C) 2013 Codefog
 *
 * @package social_images
 * @author  Codefog <http://codefog.pl>
 * @author  Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 * @license LGPL
 */


/**
 * Extend tl_page palettes
 */
foreach ($GLOBALS['TL_DCA']['tl_page']['palettes'] as $k=>$v)
{
	$GLOBALS['TL_DCA']['tl_page']['palettes'][$k] = str_replace('includeLayout;', 'includeLayout;{socialimages_legend:hide},socialImage;', $v);
}


/**
 * Add the field to tl_page
 */
$GLOBALS['TL_DCA']['tl_page']['fields']['socialImage'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_page']['socialImage'],
	'exclude'                 => true,
	'inputType'               => 'fileTree',
	'eval'                    => array('files'=>true, 'filesOnly'=>true, 'fieldType'=>'radio', 'extensions'=>$GLOBALS['TL_CONFIG']['validImageTypes'], 'tl_class'=>'clr'),
	'sql'                     => "binary(16) NULL"
);
