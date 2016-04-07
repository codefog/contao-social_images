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

namespace SocialImages;

/**
 * Provide methods to handle the social images
 */
class SocialImages extends \Controller
{

    /**
     * Initializes the global array
     */
    public function __construct()
    {
        if (!isset($GLOBALS['SOCIAL_IMAGES']) || !is_array($GLOBALS['SOCIAL_IMAGES']))
        {
            $GLOBALS['SOCIAL_IMAGES'] = array();
        }

        parent::__construct();
    }


    /**
     * Add the social images to the page
     *
     * @param \PageModel   $objPage
     * @param \LayoutModel $objLayout
     */
    public function addSocialImages(\PageModel $objPage, \LayoutModel $objLayout)
    {
        if (empty($GLOBALS['SOCIAL_IMAGES']))
        {
            return;
        }

        $arrImages = array_unique($GLOBALS['SOCIAL_IMAGES']);

        // Limit the images
        if ($objLayout->socialImages_limit > 0)
        {
            $arrImages = array_slice($arrImages, 0, $objLayout->socialImages_limit);
        }

        $arrDimensions = deserialize($objLayout->socialImages_size, true);
        $strHost = (\Environment::get('ssl') ? 'https://' : 'http://') . \Environment::get('host') . TL_PATH;

        foreach ($arrImages as $strImage)
        {
            // Check the dimensions limit
            if ($arrDimensions[0] > 0 && $arrDimensions[1] > 0)
            {
                list($width, $height) = getimagesize(TL_ROOT . '/' . $strImage);

                if ($width < $arrDimensions[0] || $height < $arrDimensions[1])
                {
                    continue;
                }
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


    /**
     * Collect the images from page
     * @param object
     * @param object
     */
    public function collectPageImages($objPage, $objLayout)
    {
        if (!$objLayout->socialImages)
        {
            return;
        }

        // Add the current page image
        if ($objPage->socialImage && ($objImage = \FilesModel::findByUuid($objPage->socialImage)) !== null && is_file(TL_ROOT . '/' . $objImage->path))
        {
            array_unshift($GLOBALS['SOCIAL_IMAGES'], $objImage->path);
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
                    if ($objTrail->socialImage && ($objImage = \FilesModel::findByUuid($objTrail->socialImage)) !== null && is_file(TL_ROOT . '/' . $objImage->path))
                    {
                        array_unshift($GLOBALS['SOCIAL_IMAGES'], $objImage->path);

                        break;
                    }
                }
            }
        }
    }


    /**
     * Collect the images from content element
     * @param object
     * @param string
     */
    public function collectContentElementImages($objModel, $strBuffer)
    {
        if (!in_array($objModel->type, $GLOBALS['SOCIAL_IMAGES_CE']))
        {
            return $strBuffer;
        }

        switch ($objModel->type)
        {
            case 'text':
                if ($objModel->addImage)
                {
                    $objFile = \FilesModel::findByPk($objModel->singleSRC);

                    if ($objFile !== null && is_file(TL_ROOT . '/' . $objFile->path))
                    {
                        $GLOBALS['SOCIAL_IMAGES'][] = $objFile->path;
                    }
                }
                break;

            case 'image':
                $objFile = \FilesModel::findByPk($objModel->singleSRC);

                if ($objFile !== null && is_file(TL_ROOT . '/' . $objFile->path))
                {
                    $GLOBALS['SOCIAL_IMAGES'][] = $objFile->path;
                }
                break;

            case 'gallery':
                $objFiles = \FilesModel::findMultipleByUuids(deserialize($objModel->multiSRC));

                if ($objFiles !== null)
                {
                    $images = array();
                    $auxDate = array();

                    // Get all images
                    while ($objFiles->next())
                    {
                        // Continue if the files has been processed or does not exist
                        if (isset($images[$objFiles->path]) || !file_exists(TL_ROOT . '/' . $objFiles->path))
                        {
                            continue;
                        }

                        // Single files
                        if ($objFiles->type == 'file')
                        {
                            $objFile = new \File($objFiles->path, true);

                            if (!$objFile->isGdImage)
                            {
                                continue;
                            }

                            $images[$objFiles->path] = array('path'=>$objFiles->path, 'uuid'=>$objFiles->uuid);
                            $auxDate[] = $objFile->mtime;
                        }

                        // Folders
                        else
                        {
                            $objSubfiles = \FilesModel::findByPid($objFiles->uuid);

                            if ($objSubfiles === null)
                            {
                                continue;
                            }

                            while ($objSubfiles->next())
                            {
                                // Skip subfolders
                                if ($objSubfiles->type == 'folder')
                                {
                                    continue;
                                }

                                $objFile = new \File($objSubfiles->path, true);

                                if (!$objFile->isGdImage)
                                {
                                    continue;
                                }

                                $images[$objSubfiles->path] = array('path'=>$objSubfiles->path, 'uuid'=>$objSubfiles->uuid);
                                $auxDate[] = $objFile->mtime;
                            }
                        }
                    }

                    // Sort array
                    switch ($objModel->sortBy)
                    {
                        default:
                        case 'name_asc':
                            uksort($images, 'basename_natcasecmp');
                            break;

                        case 'name_desc':
                            uksort($images, 'basename_natcasercmp');
                            break;

                        case 'date_asc':
                            array_multisort($images, SORT_NUMERIC, $auxDate, SORT_ASC);
                            break;

                        case 'date_desc':
                            array_multisort($images, SORT_NUMERIC, $auxDate, SORT_DESC);
                            break;

                        case 'meta': // Backwards compatibility
                        case 'custom':
                            if ($objModel->orderSRC != '')
                            {
                                $tmp = deserialize($objModel->orderSRC);

                                if (!empty($tmp) && is_array($tmp))
                                {
                                    // Remove all values
                                    $arrOrder = array_map(function(){}, array_flip($tmp));

                                    // Move the matching elements to their position in $arrOrder
                                    foreach ($images as $k=>$v)
                                    {
                                        if (array_key_exists($v['uuid'], $arrOrder))
                                        {
                                            $arrOrder[$v['uuid']] = $v;
                                            unset($images[$k]);
                                        }
                                    }

                                    // Append the left-over images at the end
                                    if (!empty($images))
                                    {
                                        $arrOrder = array_merge($arrOrder, array_values($images));
                                    }

                                    // Remove empty (unreplaced) entries
                                    $images = array_values(array_filter($arrOrder));
                                    unset($arrOrder);
                                }
                            }
                            break;

                        case 'random':
                            shuffle($images);
                            break;
                    }

                    $images = array_values($images);

                    // Limit the total number of items (see #2652)
                    if ($objModel->numberOfItems > 0)
                    {
                        $images = array_slice($images, 0, $objModel->numberOfItems);
                    }

                    $offset = 0;
                    $total = count($images);
                    $limit = $total;

                    // Pagination
                    if ($objModel->perPage > 0)
                    {
                        // Get the current page
                        $id = 'page_g' . $objModel->id;
                        $page = \Input::get($id) ?: 1;

                        // Do not index or cache the page if the page number is outside the range
                        if ($page < 1 || $page > max(ceil($total/$objModel->perPage), 1))
                        {
                            global $objPage;
                            $objPage->noSearch = 1;
                            $objPage->cache = 0;

                            // Send a 404 header
                            header('HTTP/1.1 404 Not Found');
                            return $strBuffer;
                        }

                        // Set limit and offset
                        $offset = ($page - 1) * $objModel->perPage;
                        $limit = min($this->perPage + $offset, $total);
                    }

                    // Limit the images
                    if ($offset > 0)
                    {
                        $images = array_slice($images, $offset, $limit);
                    }

                    foreach ($images as $image)
                    {
                        $GLOBALS['SOCIAL_IMAGES'][] = $image['path'];
                    }
                }
                break;

            case 'player':
            case 'youtube':
                $objFile = \FilesModel::findByPk($objModel->posterSRC);

                if ($objFile !== null && is_file(TL_ROOT . '/' . $objFile->path))
                {
                    $GLOBALS['SOCIAL_IMAGES'][] = $objFile->path;
                }
                break;
        }

        return $strBuffer;
    }


    /**
     * Collect the images from news
     * @param object
     * @param array
     */
    public function collectNewsImages($objTemplate, $arrData, $objModule)
    {
        if (!$arrData['addImage'])
        {
            return;
        }

        $objFile = \FilesModel::findByPk($arrData['singleSRC']);

        if ($objFile !== null && is_file(TL_ROOT . '/' . $objFile->path))
        {
            if ($objModule->type == 'newsreader')
            {
                array_unshift($GLOBALS['SOCIAL_IMAGES'], $objFile->path);
            }
            else
            {
                $GLOBALS['SOCIAL_IMAGES'][] = $objFile->path;
            }
        }
    }


    /**
     * Collect the images from events
     * @param array
     * @return array
     */
    public function collectEventImages($arrEvents, $arrCalendars, $intStart, $intEnd, $objModule)
    {
        foreach ($arrEvents as $v)
        {
            foreach ($v as $vv)
            {
                foreach ($vv as $arrData)
                {
                    if (!$arrData['addImage'])
                    {
                        return $arrEvents;
                    }

                    $objFile = \FilesModel::findByPk($arrData['singleSRC']);

                    if ($objFile !== null && is_file(TL_ROOT . '/' . $objFile->path))
                    {
                        if ($objModule->type == 'eventreader')
                        {
                            array_unshift($GLOBALS['SOCIAL_IMAGES'], $objFile->path);
                        }
                        else
                        {
                            $GLOBALS['SOCIAL_IMAGES'][] = $objFile->path;
                        }
                    }

                }
            }
        }

        return $arrEvents;
    }
}
