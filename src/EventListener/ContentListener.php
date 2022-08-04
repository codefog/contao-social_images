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
use Contao\ContentModel;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Template;

class ContentListener
{
    private ImageCollector $imageCollector;

    public function __construct(ImageCollector $imageCollector)
    {
        $this->imageCollector = $imageCollector;
    }

    /**
     * @Hook("getContentElement")
     */
    public function onGetContentElement(ContentModel $model, string $buffer): string
    {
        switch ($model->type) {
            case 'text':
                if ($model->addImage) {
                    $this->imageCollector->addFromUuid($model->singleSRC);
                }
                break;

            case 'image':
                $this->imageCollector->addFromUuid($model->singleSRC);
                break;

            case 'player':
                $this->imageCollector->addFromUuid($model->posterSRC);
                break;

            case 'youtube':
            case 'vimeo':
                if ($model->splashImage) {
                    $this->imageCollector->addFromUuid($model->singleSRC);
                }
                break;
        }

        return $buffer;
    }

    /**
     * @Hook("parseTemplate")
     */
    public function onParseTemplate(Template $template): void
    {
        if (!str_starts_with($template->getName(), 'gallery_')) {
            return;
        }

        foreach ($template->body as $rows) {
            foreach ($rows as $row) {
                $this->imageCollector->addFromPath($row->singleSRC ?? null);
            }
        }
    }
}
