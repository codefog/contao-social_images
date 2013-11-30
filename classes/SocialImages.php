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
 * Provide methods to handle the social images
 */
class SocialImages extends \Frontend
{

	/**
	 * Add the social images to the page
	 * @param object
	 * @param object
	 * @param object
	 */
	public function addSocialImages(\PageModel $objPage, \LayoutModel $objLayout, \PageRegular $objPageRegular)
	{
		if (!$objLayout->socialImages)
		{
			return;
		}

		// Add the current page image
		if ($objPage->socialImage && ($objImage = \FilesModel::findByUuid($objPage->socialImage)) !== null && is_file(TL_ROOT . '/' . $objImage->path))
		{
			if (is_array($GLOBALS['SOCIAL_IMAGES']))
			{
				array_unshift($GLOBALS['SOCIAL_IMAGES'], $objImage->path);
			}
			else
			{
				$GLOBALS['SOCIAL_IMAGES'] = array($objImage->path);
			}
		}
		// Walk the trail
		else
		{
			$objTrail = \PageModel::findParentsById($objPage->id);

			if ($objTrail !== null)
			{
				while ($objTrail->next())
				{
					// Add the image
					if ($objTrail->socialImage && ($objImage = \FilesModel::findByUuid($objPage->socialImage)) !== null && is_file(TL_ROOT . '/' . $objImage->path))
					{
						if (is_array($GLOBALS['SOCIAL_IMAGES']))
						{
							array_unshift($GLOBALS['SOCIAL_IMAGES'], $objImage->path);
						}
						else
						{
							$GLOBALS['SOCIAL_IMAGES'] = array($objImage->path);
						}

						break;
					}
				}
			}
		}

		if (!is_array($GLOBALS['SOCIAL_IMAGES']) || empty($GLOBALS['SOCIAL_IMAGES']))
		{
			return;
		}

		$arrImages = array_unique($GLOBALS['SOCIAL_IMAGES']);
		$strHost = (\Environment::get('ssl') ? 'https://' : 'http://') . \Environment::get('host');

		foreach ($arrImages as $strImage)
		{
			list($width, $height) = getimagesize(TL_ROOT . '/' . $strImage);

			// Skip small images
			if ($width < 200 || $height < 200)
			{
				continue;
			}

			if ($objPage->outputFormat == 'xhtml')
			{
				$GLOBALS['TL_HEAD'][] = '<meta property="og:image" content="' . $strHost . '/' . $strImage . '" />';
			}
			else
			{
				$GLOBALS['TL_HEAD'][] = '<meta property="og:image" content="' . $strHost . '/' . $strImage . '">';
			}
		}
	}
}
