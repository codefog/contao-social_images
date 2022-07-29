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

use Codefog\SocialImagesBundle\Routing\ResponseContext\SocialImagesBag;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use Contao\FilesModel;

class ImageCollector
{
    private ContaoFramework $framework;
    private ResponseContextAccessor $responseContextAccessor;
    private string $projectDir;

    public function __construct(ContaoFramework $framework, ResponseContextAccessor $responseContextAccessor, string $projectDir)
    {
        $this->framework = $framework;
        $this->responseContextAccessor = $responseContextAccessor;
        $this->projectDir = $projectDir;
    }

    /**
     * Get all images.
     */
    public function getImages(): array
    {
        $bag = $this->getResponseContextBag();

        if ($bag === null) {
            return [];
        }

        return array_unique($bag->all());
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

        $bag = $this->getResponseContextBag();

        if ($bag === null) {
            return false;
        }

        $bag->add($path, $prepend);

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

    /**
     * Get the response context bag.
     */
    private function getResponseContextBag(): ?SocialImagesBag
    {
        $responseContext = $this->responseContextAccessor->getResponseContext();

        if ($responseContext === null) {
            return null;
        }

        if (!$responseContext->has(SocialImagesBag::class)) {
            $responseContext->addLazy(SocialImagesBag::class);
        }

        return $responseContext->get(SocialImagesBag::class);
    }
}
