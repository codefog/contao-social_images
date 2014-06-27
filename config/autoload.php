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
 * Register the namespace
 */
ClassLoader::addNamespace('SocialImages');


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	'SocialImages\SocialImages' => 'system/modules/social_images/classes/SocialImages.php',
	'SocialImages\Image'        => 'system/modules/social_images/classes/Image.php'
));
