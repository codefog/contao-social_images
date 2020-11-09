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
     * Add the social images to the page
     *
     * @param \PageModel   $objPage
     * @param \LayoutModel $objLayout
     */
    public function addSocialImages(\PageModel $objPage, \LayoutModel $objLayout)
    {
        if (!is_array($GLOBALS['SOCIAL_IMAGES']) || count($GLOBALS['SOCIAL_IMAGES']) < 1)
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

        foreach ($arrImages as $strImage)
        {
            list($width, $height) = (new \Contao\File($strImage))->imageSize;

            // Check the dimensions limit
            if ($arrDimensions[0] > 0 && $arrDimensions[1] > 0)
            {
                if ($width < $arrDimensions[0] || $height < $arrDimensions[1])
                {
                    continue;
                }
            }

            $resize = deserialize($objLayout->socialImages_resize, true);

            // Resize the image
            if ($resize[0] || $resize[1] || $resize[2]) {
                $strImage = urldecode(\Image::get($strImage, $resize[0], $resize[1], $resize[2]));
                list($width, $height) = (new \Contao\File($strImage))->imageSize;
            }

            $tagEnd = ($objPage->outputFormat === 'xhtml') ? ' />' : '>';
            $tags = ['<meta property="og:image" content="' . $this->generateImageUrl($strImage) . '"' . $tagEnd];

            // Add the dimension tags
            if ($width > 0 && $height > 0) {
                $tags[] = '<meta property="og:image:width" content="' . $width . '"' . $tagEnd;
                $tags[] = '<meta property="og:image:height" content="' . $height . '"' . $tagEnd;
            }

            $GLOBALS['TL_HEAD'][] = implode("\n", $tags);
        }
    }

    /**
     * Generate the image URL
     *
     * @param string $image
     *
     * @return string
     */
    private function generateImageUrl($image)
    {
        // Support the custom assets URL
        if (TL_ASSETS_URL && strpos($image, 'assets/') === 0) {
            $url = ltrim(TL_ASSETS_URL, '/');
        } elseif (TL_FILES_URL && strpos($image, \Config::get('uploadPath').'/') === 0) {
            // Support the custom files URL
            $url = ltrim(TL_FILES_URL, '/');
        } else {
            $url = \Environment::get('host') . TL_PATH;
        }

        // Add the protocol if missing
        if (!preg_match('@https?://@', $url)) {
            $url = (\Environment::get('ssl') ? 'https://' : 'http://') . $url;
        }

        return rtrim($url, '/') . '/' . \System::urlEncode($image);
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

        // Initialize the array
        if (!is_array($GLOBALS['SOCIAL_IMAGES'])) {
            $GLOBALS['SOCIAL_IMAGES'] = array();
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
        if (!is_array($GLOBALS['SOCIAL_IMAGES']) || !in_array($objModel->type, $GLOBALS['SOCIAL_IMAGES_CE']))
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
        if (!is_array($GLOBALS['SOCIAL_IMAGES']) || !$arrData['addImage'])
        {
            return;
        }

        $objFile = \FilesModel::findByPk($arrData['singleSRC']);

        if ($objFile !== null && is_file(TL_ROOT . '/' . $objFile->path))
        {
            if ($objModule->type === 'newsreader')
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
        // do not add images of events from the event menu
        if ($objModule->type === 'eventmenu') {
            return $arrEvents;
        }

        if (!is_array($GLOBALS['SOCIAL_IMAGES'])) {
            return $arrEvents;
        }

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
                        if ($objModule->type === 'eventreader')
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


    /**
     * Collect the image from the currently displayed event
     * @param \ContentModel
     * @param string
     * @param \ContentElement
     * @return string
     */
    public function collectEventReaderImage($objContentModel, $strBuffer, $objElement)
    {
        if (!is_array($GLOBALS['SOCIAL_IMAGES']) || !$objElement instanceof \ContentModule)
        {
            return $strBuffer;
        }

        $strItem = \Input::get(\Config::get('useAutoItem') ? 'auto_item' : 'item');

        if (empty($strItem))
        {
            return $strBuffer;
        }

        $objModuleModel = \ModuleModel::findByPk($objContentModel->module);

        if ('eventreader' !== $objModuleModel->type && 'eventlist' === $objModuleModel->type && empty($objModuleModel->cal_readerModule))
        {
            return $strBuffer;
        }

        $objEvent = \CalendarEventsModel::findByIdOrAlias($strItem);

        if (null === $objEvent)
        {
            return $strBuffer;
        }

        $objFile = \FilesModel::findById($objEvent->singleSRC);

        if ($objFile !== null && is_file(TL_ROOT . '/' . $objFile->path))
        {
            array_unshift($GLOBALS['SOCIAL_IMAGES'], $objFile->path);
        }

        return $strBuffer;
    }
}
