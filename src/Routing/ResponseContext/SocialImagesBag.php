<?php

declare(strict_types=1);

/*
 * This file is part of SocialImagesBundle.
 *
 * (c) Codefog
 *
 * @license MIT
 */

namespace Codefog\SocialImagesBundle\Routing\ResponseContext;

class SocialImagesBag
{
    private array $images = [];

    /**
     * Get all images.
     */
    public function all(): array
    {
        return $this->images;
    }

    /**
     * Add an image.
     */
    public function add(string $path, bool $prepend): void
    {
        if ($prepend) {
            array_unshift($this->images, $path);
        } else {
            $this->images[] = $path;
        }
    }
}
