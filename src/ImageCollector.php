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

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FilesModel;

class ImageCollector
{
    private array $images = [];

    private ContaoFramework $framework;
    private string $projectDir;

    public function __construct(ContaoFramework $framework, string $projectDir)
    {
        $this->framework = $framework;
        $this->projectDir = $projectDir;
    }

    /**
     * Get all images.
     */
    public function getImages(): array
    {
        return array_unique($this->images);
    }

    /**
     * Add image from the UUID and return true on success, false otherwise (e.g. if file model does not exist).
     */
    public function addFromUuid(string $uuid = null, bool $prepend = false): bool
    {
        if (!$uuid) {
            return false;
        }

        $this->framework->initialize(true);

        if (($model = FilesModel::findByPk($uuid)) === null) {
            return false;
        }

        return $this->addFromPath($model->path, $prepend);
    }

    /**
     * Add image from path and return true on success, false otherwise (e.g. if file does not exist).
     */
    public function addFromPath(string $path = null, bool $prepend = false): bool
    {
        if (!$this->validateFromPath($path)) {
            return false;
        }

        if ($prepend) {
            array_unshift($this->images, $path);
        } else {
            $this->images[] = $path;
        }

        return true;
    }

    /**
     * Validate the image from UUID.
     */
    public function validateFromUuid(string $uuid = null): bool
    {
        if (!$uuid) {
            return false;
        }

        $this->framework->initialize(true);

        if (($model = FilesModel::findByPk($uuid)) === null) {
            return false;
        }

        return $this->validateFromPath($model->path);
    }

    /**
     * Validate the image from path.
     */
    public function validateFromPath(string $path = null): bool
    {
        return $path && is_file($this->projectDir.'/'.$path);
    }
}
