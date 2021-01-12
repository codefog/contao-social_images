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
 * Extend tl_news palettes
 */
foreach ($GLOBALS['TL_DCA']['tl_news']['palettes'] as $k=>$v)
{
    if (!\is_string($v))
    {
        continue;
    }

	$GLOBALS['TL_DCA']['tl_news']['palettes'][$k] = str_replace('{expert_legend', '{socialimages_legend:hide},socialImage;{expert_legend', $v);
}


/**
 * Add the field to tl_news
 */
$GLOBALS['TL_DCA']['tl_news']['fields']['socialImage'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_news']['socialImage'],
	'exclude'                 => true,
	'inputType'               => 'fileTree',
	'eval'                    => array('files'=>true, 'filesOnly'=>true, 'fieldType'=>'radio', 'extensions'=>$GLOBALS['TL_CONFIG']['validImageTypes'], 'tl_class'=>'clr'),
	'sql'                     => "binary(16) NULL"
);
