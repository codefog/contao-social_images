<?php

/**
 * social_images extension for Contao Open Source CMS
 *
 * Copyright (C) 2013 Codefog Ltd
 *
 * @package social_images
 * @author  Codefog Ltd <http://codefog.pl>
 * @author  Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 * @license LGPL
 */

namespace SocialImages;


/**
 * Override the default image class
 */
class Image extends \Contao\Image
{

	/**
	 * Resize an image and store the resized version in the assets/images folder
	 * @param string
	 * @param integer
	 * @param integer
	 * @param string
	 * @param string
	 * @param boolean
	 * @return mixed
	 */
	public static function get($image, $width, $height, $mode='', $target=null, $force=false)
	{
		$strPath = parent::get($image, $width, $height, $mode, $target, $force);

		if (strlen($strPath))
		{
			$GLOBALS['SOCIAL_IMAGES'][] = $image;
		}

		return $strPath;
	}
}
