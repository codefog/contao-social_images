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

use Contao\CoreBundle\Image\ImageSizes;

class LayoutListener
{
    private ImageSizes $imageSizes;

    public function __construct(ImageSizes $imageSizes)
    {
        $this->imageSizes = $imageSizes;
    }

    /**
     * On social images resize options callback.
     */
    public function onSocialImagesResizeOptionsCallback(): array
    {
        return $this->imageSizes->getAllOptions();
    }
}
