<?php

declare(strict_types=1);

/*
 * This file is part of SocialImagesBundle.
 *
 * (c) Codefog
 *
 * @license MIT
 */

namespace Codefog\SocialImagesBundle;

use Contao\Config;
use Contao\CoreBundle\Asset\ContaoContext;
use Contao\CoreBundle\Image\ImageFactory;
use Contao\Environment;
use Contao\File;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Symfony\Component\Filesystem\Path;

class ImageGenerator
{
    private ContaoContext $assetsContext;
    private ContaoContext $filesContext;
    private ImageFactory $imageFactory;
    private string $projectDir;

    public function __construct(ContaoContext $assetsContext, ContaoContext $filesContext, ImageFactory $imageFactory, string $projectDir)
    {
        $this->assetsContext = $assetsContext;
        $this->filesContext = $filesContext;
        $this->imageFactory = $imageFactory;
        $this->projectDir = $projectDir;
    }

    /**
     * Generate the images.
     */
    public function generateImages(array $images, array $settings): array
    {
        if (0 === \count($images)) {
            return [];
        }

        $settings['size'] = StringUtil::deserialize($settings['size']);
        $settings['resize'] = StringUtil::deserialize($settings['resize']);

        // Limit the images
        if ($settings['limit'] > 0) {
            $images = \array_slice($images, 0, $settings['limit']);
        }

        $return = [];

        foreach ($images as $image) {
            $file = new File($image);

            if (!$file->isImage || !$this->validateImageMinimumDimensions($file, $settings)) {
                continue;
            }

            $return[] = $this->resizeImage($file, $settings);
        }

        return $return;
    }

    /**
     * Generate the tags.
     */
    public function generateTags(PageModel $page, array $images): array
    {
        $tags = [];

        foreach ($images as $image) {
            if (!($image instanceof File)) {
                $image = new File($image);
            }

            $url = $this->generateImageUrl($page, $image->path);

            // Add the first image as a thumbnail (e.g., for Google search results)
            if (0 === \count($tags)) {
                $tags[] = sprintf('<meta name="thumbnail" content="%s">', $url);
            }

            $tags[] = sprintf('<meta property="og:image" content="%s">', $url);

            // Add the dimension tags
            if ($image->width > 0 && $image->height > 0) {
                $tags[] = sprintf('<meta property="og:image:width" content="%s">', $image->width);
                $tags[] = sprintf('<meta property="og:image:height" content="%s">', $image->height);
            }
        }

        return $tags;
    }

    /**
     * Resize the image.
     */
    private function resizeImage(File $file, array $settings): File
    {
        if (!\is_array($settings['resize'] ?? null) || 0 === \count($settings['resize'])) {
            return $file;
        }

        $image = $this->imageFactory->create($this->projectDir.'/'.$file->path, $settings['resize']);

        return new File(Path::makeRelative($image->getPath(), $this->projectDir));
    }

    /**
     * Validate image minimum dimensions.
     */
    private function validateImageMinimumDimensions(File $file, array $settings): bool
    {
        if (!\is_array($settings['size'] ?? null)) {
            return true;
        }

        if (($settings['size'][0] ?? 0) > 0 && ($settings['size'][1] ?? 0) > 0) {
            [$width, $height] = $file->imageSize + [0, 0];

            if ($width < $settings['size'][0] || $height < $settings['size'][1]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Generate the image URL.
     */
    private function generateImageUrl(PageModel $page, string $image): string
    {
        // Support the custom assets URL
        if (str_starts_with('assets', $image) && ($assetsUrl = $this->assetsContext->getStaticUrl()) !== '') {
            $url = ltrim($assetsUrl, '/');
        } elseif (str_starts_with(Config::get('uploadPath').'/', $image) && ($imagesUrl = $this->filesContext->getStaticUrl()) !== '') {
            // Support the custom files URL
            $url = ltrim($imagesUrl, '/');
        } else {
            $url = $page->dns ?: Environment::get('base');
        }

        $url = rtrim($url, '/').'/'.System::urlEncode($image);

        // Add the protocol, if it's missing
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = ($page->rootUseSSL ? 'https://' : 'http://').ltrim($url, '/');
        }

        return $url;
    }
}
