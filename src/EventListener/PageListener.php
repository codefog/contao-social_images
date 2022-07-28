<?php

declare(strict_types=1);

/*
 * This file is part of SocialImagesBundle.
 *
 * (c) Codefog
 *
 * @license MIT
 */

namespace Codefog\SocialImagesBundle\EventListener;

use Codefog\SocialImagesBundle\ImageCollector;
use Codefog\SocialImagesBundle\ImageGenerator;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\LayoutModel;
use Contao\PageModel;

class PageListener
{
    private ImageCollector $imageCollector;
    private ImageGenerator $imageGenerator;

    public function __construct(ImageCollector $imageCollector, ImageGenerator $imageGenerator)
    {
        $this->imageCollector = $imageCollector;
        $this->imageGenerator = $imageGenerator;
    }

    /**
     * @Hook("generatePage")
     */
    public function onGeneratePage(PageModel $page, LayoutModel $layout): void
    {
        if (!$layout->socialImages) {
            return;
        }

        $images = $this->imageGenerator->generateImages($this->imageCollector->getImages(), [
            'limit' => $layout->socialImages_limit,
            'resize' => $layout->socialImages_resize,
            'size' => $layout->socialImages_size,
        ]);

        if (0 === \count($images)) {
            return;
        }

        $GLOBALS['TL_HEAD'][] = implode("\n", $this->imageGenerator->generateTags($page, $images));
    }

    /**
     * @Hook("getPageLayout")
     */
    public function onGetPageLayout(PageModel $page): void
    {
        $this->imageCollector->addFromUuid($page->socialImage, true);
    }

    /**
     * @Hook(value="loadPageDetails")
     */
    public function onLoadPageDetails(array $parentPages, PageModel $currentPage): void
    {
        // The current page already has the value
        if ($this->imageCollector->validateFromUuid($currentPage->socialImage)) {
            return;
        }

        // Inherit the social image from parent pages
        foreach ($parentPages as $parentPage) {
            if ($this->imageCollector->validateFromUuid($parentPage->socialImage)) {
                $currentPage->socialImage = $parentPage->socialImage;
                break;
            }
        }
    }
}
