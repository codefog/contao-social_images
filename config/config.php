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
 * Extension version
 */
@define('SOCIAL_IMAGES_VERSION', '1.0');
@define('SOCIAL_IMAGES_BUILD', '0');


/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['generatePage'][] = array('SocialImages', 'addSocialImages');
