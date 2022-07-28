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
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Module;
use Contao\ModuleNewsReader;
use Contao\Template;

class NewsListener
{
    private ImageCollector $imageCollector;

    public function __construct(ImageCollector $imageCollector)
    {
        $this->imageCollector = $imageCollector;
    }

    /**
     * @Hook("parseArticles")
     */
    public function onParseArticles(Template $template, array $data, Module $module): void
    {
        $prepend = $module instanceof ModuleNewsReader;
        $result = $this->imageCollector->addFromUuid($data['socialImage'], $prepend);

        if (!$result && $data['addImage']) {
            $this->imageCollector->addFromUuid($data['singleSRC'], $prepend);
        }
    }
}
