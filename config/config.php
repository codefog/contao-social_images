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
 * Extension version
 */
@define('SOCIAL_IMAGES_VERSION', '3.0');
@define('SOCIAL_IMAGES_BUILD', '0');


/**
 * Content elements that are supported by social images
 */
$GLOBALS['SOCIAL_IMAGES_CE'] = array('text', 'image', 'gallery', 'player', 'youtube');


/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['getAllEvents'][] = array('SocialImages', 'collectEventImages');
$GLOBALS['TL_HOOKS']['getContentElement'][] = array('SocialImages', 'collectContentElementImages');
$GLOBALS['TL_HOOKS']['generatePage'][] = array('SocialImages', 'addSocialImages');
$GLOBALS['TL_HOOKS']['parseArticles'][] = array('SocialImages', 'collectNewsImages');
